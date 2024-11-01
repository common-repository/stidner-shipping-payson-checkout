<?php

namespace Stidner\Woocommerce;

use Stidner\Api\Stidner\Objects\Address;
use Stidner\Api\Stidner\Objects\Item;
use Stidner\Api\Stidner\Objects\Order;
use Stidner\Exceptions\AdminException;
use Stidner\Exceptions\UserException;
use Stidner\Init;

/**
 * Class OrderActions
 *
 * @package Stidner\Api\Stidner\Actions
 */
class OrderActions
{
    public static $creation = false;

    /**
     * @param $wc_products
     *
     * @return Order
     * @throws \Exception
     */
    public static function createOrUpdateStidnerOrder($wc_products)
    {
        try {
            $items = self::wcProductsToStidnerItems($wc_products);

            if (empty($items)) {
                throw new \Exception('Cart is empty');
            }

            if (self::$creation) {
                return self::getStidnerOrderFromSession();
            }

            $stidner_order = OrderActions::updateStidnerOrderInSession(
                (OrderActions::getStidnerOrderFromSession() instanceof Order)
                    ? OrderActions::update($items)
                    : OrderActions::create($items)
            );

            $wc_order = (WC()->session->get('ongoing_payson_order'))
                ?wc_get_order(WC()->session->get('ongoing_payson_order'))
                :WcOrderActions::updateOrCreateWcOrder();

            OrderActions::updateStidnerOrderInMeta(
                $wc_order->get_id(),
                $stidner_order
            );

            OrderActions::updateStidnerOrderInSession($stidner_order);

            return $stidner_order;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Convert wc products to stidner items for api requests
     *
     * @param $cart_items
     *
     * @return array
     */
    public static function wcProductsToStidnerItems($cart_items)
    {
        $stidner_items = [];

        foreach ($cart_items as $i => $v) {

            $wc_item = wc_get_product($v['data']);

            if (! $wc_item instanceof \WC_Product) {
                continue;
            }

            if (! $wc_item->needs_shipping()) {
                continue;
            }

            $qty  = $v['quantity'];
            $item = new Item();
            $item
                ->setArticleNumber((string) $wc_item->get_id())
                ->setName($wc_item->get_name())
                ->setDescription(($wc_item->get_short_description() != '') ? $wc_item->get_short_description() : null)
                ->setUnitPrice((int) ($wc_item->get_price() * 100))
                ->setWeight((int) ((wc_get_weight($wc_item->get_weight(), 'g') > 0) ? wc_get_weight($wc_item->get_weight(), 'g') : 0))
                ->setHeight((int) ((wc_get_dimension($wc_item->get_height(), 'cm') > 0) ? wc_get_dimension($wc_item->get_height(), 'cm') : 0))
                ->setLength((int) ((wc_get_dimension($wc_item->get_length(), 'cm') > 0) ? wc_get_dimension($wc_item->get_length(), 'cm') : 0))
                ->setWidth((int) ((wc_get_dimension($wc_item->get_width(), 'cm') > 0) ? wc_get_dimension($wc_item->get_width(), 'cm') : 0))
                ->setInStock($wc_item->is_in_stock())
                ->setQuantity($qty);

            $stidner_items[] = $item->toArray();
        }

        return $stidner_items;
    }

    /**
     * @return Order
     *
     * get stidner order from metadata
     *
     */
    public static function getStidnerOrderFromSession()
    {

        $stidner_order = WC()->session->get(Init::ORDER_META);
        if (! $stidner_order or empty($stidner_order)) {
            return null;
        }

        return new Order($stidner_order);
    }

    /**
     * @param Order $stidner_order
     *
     * @return Order
     */
    public static function updateStidnerOrderInSession(Order $stidner_order)
    {

        WC()->session->set(Init::ORDER_META, $stidner_order);

        return $stidner_order;
    }

    /**
     * @param $items
     *
     * Used on cart updates
     *
     * @return mixed
     * @throws UserException
     * @throws \Exception
     *
     */
    public static function update($items)
    {

        $stidner_order = OrderActions::getStidnerOrderFromSession();

        if (! $stidner_order instanceof Order) {
            return self::create($items);
        }

        try {
            $new_stidner_order = new Order();
            $new_stidner_order->setItems($items)
                ->setDisallowAddressChange(false)
                ->setDisallowOptionChange(false)
                ->setCurrency(get_woocommerce_currency())
                ->setLocale(get_locale());

            return Init::api()->orderUpdate($new_stidner_order, $stidner_order->getUuid());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $items
     *
     * @return mixed
     * @throws \Exception
     */
    public static function create($items)
    {
        $recipient_address = new Address();
        $recipient_address->setCountryCode(Init::DEFAULT_COUNTRY_CODE)->setType(Address::TYPE_RECIPIENT)
                          ->setCustomerType(Address::CUSTOMER_PERSON);

        $order = new Order();
        $order->setIntegrationPlatform(Order::SOURCE_WOOCOMMERCE)
            ->setExternalReference(null)
            ->setPaymentReference(null)
            ->setPaymentSystem(null)
            ->setOrderStatus(Order::STATUS_CREATED)
            ->setCurrency(get_woocommerce_currency())
            ->setItems($items)
            ->setAddresses([$recipient_address])
            ->setNotificationUrl(OrderActions::createNotificationUrl(null))
            ->setLocale(get_locale());

        try {

            $stidner_order  = Init::api()->orderCreate($order);
            self::$creation = true;

            return $stidner_order;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Update wc_order meta
     *
     * @param $wc_order_id
     * @param $stidner_order
     */
    public static function updateStidnerOrderInMeta($wc_order_id, Order $stidner_order)
    {
        update_post_meta($wc_order_id, Init::ORDER_META, $stidner_order);
    }

    /**
     * @param Order $stidner_order
     * @param \WC_Order $wc_order
     *
     * @return Order
     */
    public static function completeOrder(Order $stidner_order, \WC_Order $wc_order)
    {
        return $stidner_order->setOrderStatus(Order::STATUS_COMPLETED) // update stidner order status
            ->setNotificationUrl(OrderActions::createNotificationUrl($wc_order->get_id())) // url used by stidner when order is shipped
            ->setAddresses(OrderActions::updateAddress($stidner_order, $wc_order)) // set recipient address
            ->setItems([]) // don't update items
            ->setPaymentSystem('payson')
            ->setPaymentReference(WC()->session->get('payson_checkout_id'))
            ->setExternalReference($wc_order->get_id()); // id of woocommerce order
    }

    /**
     * Used by stidner when shipping completed
     *
     * @param $wc_order_id
     *
     * @return string
     *
     */
    public static function createNotificationUrl($wc_order_id)
    {

        return admin_url(
            'admin-ajax.php?action=stidner_notification&order_id='.$wc_order_id.'&key='.md5(
                uniqid(
                    rand(),
                    true
                )
            )
        );
    }

    /**
     * @param Order $stidner_order
     * @param \WC_Order $wc_order
     *
     * @return \Stidner\Api\Stidner\Objects\Address[]
     */
    public static function updateAddress(Order $stidner_order, \WC_Order $wc_order)
    {
        $addresses = $stidner_order->getAddresses();

        //Add data to recipient address
        foreach ($addresses as $i => $address) {
            if (isset($address->type) and $address->type == Address::TYPE_RECIPIENT) {
                $address->setContactName($wc_order->get_formatted_shipping_full_name())->setCustomerType(
                        Address::CUSTOMER_PERSON
                    )->setType(Address::TYPE_RECIPIENT)->setName($wc_order->get_formatted_shipping_full_name())
                        ->setCountryCode($wc_order->get_shipping_country())->setContactPhone(
                        $wc_order->get_billing_phone()
                    )//for tests - +46771793336
                        ->setContactEmail($wc_order->get_billing_email())->setCity($wc_order->get_shipping_city())
                        ->setPostalCode($wc_order->get_shipping_postcode())->setAddressLine(
                        $wc_order->get_shipping_address_1()
                    )->setAddressLine2($wc_order->get_shipping_address_2());
            }
        }

        return $addresses;
    }

    /**
     * Remove all temporary data from cookies
     */
    public static function clearSession()
    {
        WC()->session->set(Init::ORDER_META, null);
    }

    /**
     * @return \WP_REST_Response
     * @throws \Exception
     * @internal param \WP_REST_Request $request
     *
     */
    public static function callbackHandler()
    {

        $wc_order_id       = isset($_GET['order_id']) ? $_GET['order_id'] : '';
        $stidner_order_key = isset($_GET['key']) ? $_GET['key'] : '';
        $stidner_order     = OrderActions::getStidnerOrderFromMeta($wc_order_id);

        if (! $stidner_order instanceof Order) {
            $data   = [
                'data'    => '',
                'error'   => true,
                'message' => 'Wrong order id.',
            ];
            $status = 400;
        } elseif (strpos($stidner_order->getNotificationUrl(), $stidner_order_key) === false) {
            $data   = [
                'data'    => '',
                'error'   => true,
                'message' => 'Wrong validation key.',
            ];
            $status = 400;
        } else {
            OrderActions::updateStidnerOrderInMeta(
                $wc_order_id,
                Init::api()->orderGet($stidner_order->getUuid())
            );

            /*Update Woocommerce order*/
            $wc_order = wc_get_order($wc_order_id);

            if ($stidner_order->getShippingStatus() == Order::SHIPPING_STATUS_SHIPPED) {
                $wc_order->update_status('completed', 'Stidner Notification');
            }

            $data   = [
                'data'    => [
                    'uuid' => $stidner_order->getUuid(),
                ],
                'error'   => false,
                'message' => 'Status updated.',
            ];
            $status = 200;
        }

        if ($status == 200) {
            wp_send_json_success($data);
        } else {
            wp_send_json_error($data);
        }

        die();
    }

    /**
     * @param $wc_order_id
     *
     * Get stidner order from metadata
     *
     * @return Order
     * @throws AdminException
     */
    public static function getStidnerOrderFromMeta($wc_order_id)
    {
        $stidner_order = get_post_meta($wc_order_id, Init::ORDER_META, true);

        if (! $stidner_order or empty($stidner_order)) {
            return null;
        }

        return new Order($stidner_order);
    }
}