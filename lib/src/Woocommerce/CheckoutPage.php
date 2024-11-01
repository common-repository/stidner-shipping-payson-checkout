<?php

namespace Stidner\Woocommerce;

use Stidner\Api\Stidner\Objects\Order;
use Stidner\Exceptions\InvalidCredentialsException;
use Stidner\Init;
use Stidner\Woocommerce\Gateways\Payson\WcPaysonCheckoutSetupPaysonAPI;

/**
 * Class CheckoutPage
 * @package Stidner\Woocommerce
 */
class CheckoutPage
{

    /**
     * @return mixed
     * @throws \Exception
     */
    public static function shippingSection()
    {
        try {
            $options = get_option('woocommerce_' . \Stidner\Init::PREFIX . '_settings');

            if ((!isset($options['merchant_id']) or $options['merchant_id'] == '') or (!isset($options['api_key']) or $options['api_key'] == '')) {
                $email = '';

                if (is_user_logged_in()) {
                    $email = wp_get_current_user()->user_email;
                    $message = '<iframe width="370" height="400" src="https://dashboard.stidner.com/auth/register/external?email=' . $email . '" id="stidner_iframe"></iframe>';
                    //throw new InvalidCredentialsException($message);
                }
            }

            return OrderActions::createOrUpdateStidnerOrder(WC()->cart->get_cart());

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Used with ajax
     *
     *
     */
    public static function cartUpdated()
    {
        echo WC()->session->get('payson_order_iframe', '');
        die();
    }

    /**
     * @throws \Exception
     */
    public static function paymentSection()
    {

        try {

            WC()->session->set('chosen_payment_method', 'paysoncheckout');

            $payson_api = new WcPaysonCheckoutSetupPaysonAPI();

            $stidner_order = OrderActions::getStidnerOrderFromSession();

            if (!$stidner_order instanceof Order) {
                throw new \Exception(__('Internal Error', Init::TEXT_DOMAIN));
            }

            $wc_order = WcOrderActions::updateOrCreateWcOrder();

            $checkout = (WC()->session->get('payson_checkout_id'))
                ?$payson_api->updateCheckout( $stidner_order,$wc_order )
                :$payson_api->createCheckout($stidner_order, $wc_order);


            if (!isset($checkout->snippet)) {
                throw new \Exception("Payson error");
            }

            return $checkout;
        } catch (\Exception $e) {
            throw $e;
        }

    }

    /**
     * Used in woocommerce hook
     *
     *
     * @param $wc_order_id
     *
     * @throws \Exception
     */
    public static function successPage($wc_order_id)
    {

        try {

            $wc_order = wc_get_order($wc_order_id);

            //exit on Woocommerce error
            if (!$wc_order instanceof \WC_Order) {
                return;
            }

            //exit if other shipping method selected
            if ($wc_order->get_shipping_method() != Init::SERVICE_NAME) {
                return;
            }

            self::successShippingWidget($wc_order);


        } catch (\Exception $e) {
            return;
        }

    }

    /**
     * @param \WC_Order $wc_order
     *
     * @throws \Exception
     */
    public static function successShippingWidget(\WC_Order $wc_order)
    {

        //get order from meta instead of session on page reloading
        $stidner_order = (OrderActions::getStidnerOrderFromSession() instanceof Order)
            ? OrderActions::getStidnerOrderFromSession()
            : OrderActions::getStidnerOrderFromMeta($wc_order->get_id());


        //exit if something wrong with stidner order
        if (!$stidner_order instanceof Order) {
            return;
        }


        try {
            //don't update order on page reload
            if ($stidner_order->getOrderStatus() == Order::STATUS_COMPLETED) {
                CheckoutPage::printWidget($stidner_order);
                return;
            }

            //Update stidner order status and address
            $stidner_order = Init::api()->orderUpdate(
                OrderActions::completeOrder($stidner_order, $wc_order),
                $stidner_order->getUuid()
            );

            OrderActions::updateStidnerOrderInSession($stidner_order);

            OrderActions::updateStidnerOrderInMeta($wc_order->get_id(),
                $stidner_order); //update stidner order in wc order meta

            CheckoutPage::printWidget($stidner_order);


        } catch (\Exception $e) {
            throw $e;
        }


    }

    /**
     * @param Order $stidner_order
     *
     * @throws \Exception
     */
    public static function printWidget(Order $stidner_order)
    {
        if (!is_null($stidner_order->getWidgetEmbed())) {
            echo '<div>' . $stidner_order->getWidgetEmbed() . '</div>';
        } else {
            throw new \Exception(__("Stidner Error", Init::TEXT_DOMAIN));
        }
    }


}


