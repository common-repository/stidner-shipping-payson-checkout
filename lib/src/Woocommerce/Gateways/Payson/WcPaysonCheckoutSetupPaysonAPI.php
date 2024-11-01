<?php

namespace Stidner\Woocommerce\Gateways\Payson;

use Stidner\Api\Stidner\Objects\Address;
use Stidner\Api\Stidner\Objects\Order;
use Stidner\Init;
use Stidner\Woocommerce\Gateways\Payson\Lib\Checkout;
use Stidner\Woocommerce\Gateways\Payson\Lib\PayData;

/**
 * Class WcPaysonCheckoutSetupPaysonAPI
 *
 * @package Stidner\Woocommerce\Gateways\Payson
 */
class WcPaysonCheckoutSetupPaysonAPI
{
    /**
     * WC_PaysonCheckout_Setup_Payson_API constructor.
     */
    public function __construct()
    {
        $this->payment_method_id = 'paysoncheckout';
        $this->settings = get_option('woocommerce_' . $this->payment_method_id . '_settings');
    }

    /**
     * @param \Stidner\Api\Stidner\Objects\Order $stidner_order
     * @param \WC_Order                          $wc_order
     * @param                                    $addressOnly boolean  If only address changed, no need to refresh order
     *
     * @return mixed|\Stidner\Woocommerce\Gateways\Payson\Lib\Checkout
     * @throws \Exception
     */
    public function updateCheckout(Order $stidner_order, \WC_Order $wc_order, $addressOnly = false)
    {
        try {
            $block_status = [
                'readyToPay',
                'processingPayment',
                'readyToShip',
                'shipped',
                'paidToAccount',
                'canceled',
                'expired',
                'denied',
            ];

            $callPaysonApi = $this->setPaysonApi();

            $checkout_temp_obj = $callPaysonApi->GetCheckout(WC()->session->get('payson_checkout_id'));

            if (strtoupper($checkout_temp_obj->payData->currency) !== $wc_order->get_currency()) {
                update_post_meta($wc_order->get_id(), '_order_currency', $wc_order->get_currency());
                WC()->session->set('payson_checkout_id', null);
                WC()->session->set('payson_order_replaced', true);
            }

            if (!in_array($checkout_temp_obj->status, $block_status)) {
                $checkout_temp_obj->customer = $this->setCustomer($stidner_order);
                $checkout_temp_obj->id = WC()->session->get('payson_checkout_id');
            } else {
                if (!$addressOnly) {
                    WC()->session->set('payson_checkout_id', null);
                    WC()->session->set('payson_order_replaced', true);
                    $checkout_temp_obj = $this->createCheckout($stidner_order, $wc_order);
                }
            }

            $checkout_temp_obj->payData = $this->setPayData($stidner_order);

            $checkout = $callPaysonApi->UpdateCheckout($checkout_temp_obj);
            WC()->session->set('payson_order_iframe', $checkout->snippet);
            return $checkout;
        } catch (\Exception $e) {
            WC()->session->set('payson_checkout_id', null);
            WC()->session->set('payson_order_replaced', true);
            $checkout = $this->createCheckout($stidner_order, $wc_order);
            WC()->session->set('payson_order_iframe', $checkout->snippet);
            return $checkout;
        }
    }

    /**
     * @return Lib\PaysonApi
     * @throws \Exception
     */
    public function setPaysonApi()
    {
        try {
            $environment = ('yes' == $this->settings['testmode']) ? true : false;
            $merchant_id = $this->settings['merchant_id'];
            $api_key = $this->settings['api_key'];
            $callPaysonApi = new Lib\PaysonApi($merchant_id, $api_key, $environment);

            return $callPaysonApi;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @return Lib\Customer
     */
    private function setCustomer(Order $stidner_order)
    {

        $address = $stidner_order->getAddressByType(Address::TYPE_RECIPIENT);

        $name = $address->getName();

        $words = explode(' ', $name);

        array_splice($words, -1);

        $address_lines = (string)($address->getAddressLine() . ' ' . $address->getAddressLine2());

        $first_name = (string)implode(' ', $words);
        $last_name = (string)strrchr($name, ' ');
        $email = (string)$address->getContactEmail();
        $phone = (string)$address->getContactPhone();
        $id_number = '';
        $city = (string)$address->getCity();
        $country_code = (string)$address->getCountryCode();
        $street = ($address_lines != ' ') ? $address_lines : '';
        $postcode = (string)$address->getPostalCode();
        $current_user = wp_get_current_user();
        // Get customer info if logged in
        if ($current_user->user_email) {
            $email = $current_user->user_email;
        }
        if (WC()->customer->get_shipping_postcode()) {
            $postcode = WC()->customer->get_shipping_postcode();
        }

        $customer = new  Lib\Customer($first_name, $last_name, $email, $phone, $id_number, $city, $country_code,
            $postcode, $street);

        return $customer;
    }

    /**
     * @param \Stidner\Api\Stidner\Objects\Order $stidner_order
     * @param \WC_Order                          $wc_order
     *
     * @return \Stidner\Woocommerce\Gateways\Payson\Lib\Checkout
     * @throws \Exception
     */
    public function createCheckout(Order $stidner_order, \WC_Order $wc_order)
    {
        try {
            $callPaysonApi = $this->setPaysonApi();

            $checkout = $callPaysonApi->GetCheckout($callPaysonApi->CreateCheckout(new Lib\Checkout($this->setMerchant($wc_order),
                $this->setPayData($stidner_order), $this->setGui(), $this->setCustomer($stidner_order))));
            WC()->session->set('payson_checkout_id', $checkout->id);

            $this->updateNotificationUrl($checkout, $wc_order);

            return $checkout;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param \WC_Order $wc_order
     *
     * @return Lib\Merchant
     */

    private function setMerchant(\WC_Order $wc_order)
    {
        return new Lib\Merchant(Init::getCheckoutUrl(), $wc_order->get_checkout_order_received_url(),
            add_query_arg('wc_order', $wc_order->get_id(), get_home_url() . '/wc-api/WC_Gateway_PaysonCheckout/'),
            wc_get_page_permalink('terms'), Init::SERVICE_NAME, $wc_order->get_order_number());
    }

    /**
     * @param Order $order
     *
     * @return PayData
     */
    private function setPayData(Order $order)
    {
        $order_lines = new WcPaysonCheckoutProcessOrderLines();

        $payData = $order_lines->getOrderLines($order);

        return $payData;
    }

    /**
     * @return Lib\Gui
     */
    private function setGui()
    {
        $gui = new  Lib\Gui($this->getPaysonLanguage(), $this->settings['color_scheme'], 'none',
            $this->getRequestPhone(), $this->getShippingCountries());

        return $gui;
    }

    /**
     * @return string
     */
    private function getPaysonLanguage()
    {
        $iso_code = explode('_', get_locale());
        $shop_language = $iso_code[0];
        switch ($shop_language) {
            case 'sv' :
                $payson_language = 'sv';
                break;
            case 'fi' :
                $payson_language = 'fi';
                break;
            default:
                $payson_language = 'en';
        }

        return $payson_language;
    }

    /**
     * @return bool
     */
    private function getRequestPhone()
    {
        return true;
    }

    /**
     * @return array
     */
    private function getShippingCountries()
    {
        // Add shipping countries
        $wc_countries = new \WC_Countries();
        $countries = array_keys($wc_countries->get_shipping_countries());

        return $countries;
    }

    /**
     * @param \Stidner\Woocommerce\Gateways\Payson\Lib\Checkout $checkout
     * @param \WC_Order                                         $wc_order
     *
     * @return mixed
     * @throws \Exception
     * @throws \Stidner\Woocommerce\Gateways\Payson\Lib\PaysonApiException
     */
    private function updateNotificationUrl(Checkout $checkout, \WC_Order $wc_order)
    {
        $callPaysonApi = $this->setPaysonApi();

        $checkout->merchant->confirmationUri = add_query_arg(['paysonorder' => $checkout->id],
            $wc_order->get_checkout_order_received_url());

        return $callPaysonApi->UpdateCheckout($checkout);
    }

    /**
     * Gets PaysonCheckout resource.
     *
     *
     * @param Order     $stidner_order
     *
     * @param \WC_Order $wc_order
     *
     * @return Lib\Checkout
     * @throws \Exception
     */
    public function getCheckout(Order $stidner_order, \WC_Order $wc_order)
    {

        try {

            if (!$stidner_order instanceof Order) {
                throw new \Exception(__('Internal Error', Init::TEXT_DOMAIN));
            }

            $callPaysonApi = $this->setPaysonApi();

            $payson_checkout_id = WC()->session->get('payson_checkout_id');

            $checkout_resource = ($payson_checkout_id) ? $callPaysonApi->GetCheckout($payson_checkout_id) : $this->createCheckout($stidner_order,
                $wc_order);

            WC()->session->set('payson_checkout_id', $checkout_resource->id);

            return $checkout_resource;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param bool $order_id
     *
     * @return Lib\Checkout
     * @throws \Exception
     */
    public function getNotificationCheckout($order_id = false)
    {

        try {
            $callPaysonApi = $this->setPaysonApi();
            $checkout = $callPaysonApi->GetCheckout($order_id);

            return $checkout;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @return Lib\Account|\WP_Error
     */
    public function get_validate_account()
    {

        try {
            $callPaysonApi = $this->setPaysonApi();
            $account = $callPaysonApi->Validate();

            return $account;
        } catch (\Exception $ex) {
            return new \WP_Error('error',
                __('The entered Payson Merchant ID, API Key or test/live mode is not correct.', Init::TEXT_DOMAIN));
        }
    }
}