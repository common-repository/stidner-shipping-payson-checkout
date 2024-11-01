<?php

namespace Stidner\Woocommerce\Gateways\Payson;

use Stidner\Api\Stidner\Objects\Order;
use Stidner\Init;
use Stidner\Woocommerce\Gateways\Payson\Lib\Checkout;
use Stidner\Woocommerce\OrderActions;

class WcPaysonCheckoutResponseHandler
{

    /**
     * Notification listener.
     */
    public function notificationListener()
    {

        if (!isset($_GET['checkout'])) {
            return;
        }

        try {
            $payson_api = new WcPaysonCheckoutSetupPaysonAPI();
            $checkout = $payson_api->getNotificationCheckout($_GET['checkout']);

            $wc_order = wc_get_order($_GET['wc_order']);

            if ($wc_order) {
                switch ($checkout->status) {
                    case 'readyToShip':
                        $stidner_order = (OrderActions::getStidnerOrderFromSession() instanceof Order)
                            ? OrderActions::getStidnerOrderFromSession()
                            : OrderActions::getStidnerOrderFromMeta($wc_order->get_id());
                        
                        if ($stidner_order instanceof Order and $stidner_order->getOrderStatus() != Order::STATUS_COMPLETED) {
                            Init::api()->orderUpdate(
                                OrderActions::completeOrder($stidner_order, $wc_order),
                                $stidner_order->getUuid()
                            );
                            OrderActions::updateStidnerOrderInMeta($wc_order->get_id(), $stidner_order);
                            OrderActions::updateStidnerOrderInSession($stidner_order);
                        }
                        $this->readyToShipCb($wc_order, $checkout);
                        break;
                    case 'paidToAccount':
                        // $this->paid_to_account_cb( $order, $checkout );
                        break;
                    case 'expired':
                        $this->expiredCb($wc_order);
                        break;
                    case 'denied':
                        $this->deniedCb($wc_order);
                        break;
                    case 'canceled':
                        $this->deniedCb($wc_order);
                        break;
                }
            }
        } catch (\Exception $e) {
            return;
        }
    }


    /**
     * Handle a completed payment.
     *
     * @param \WC_Order       $order    WooCommerce order.
     * @param object|Checkout $checkout PaysonCheckout resource.
     */
    public function readyToShipCb(\WC_Order $order, Checkout $checkout)
    {


        if (!$order instanceof \WC_Order) {
            exit;
        }

        if ($order->has_status(array('processing', 'completed'))) {

            header('HTTP/1.0 200 OK');

            return;

        }

        // Add order addresses.
        $this->addOrderAddresses($order, $checkout);

        // Add Payson order status.
        update_post_meta($order->get_id(), '_paysoncheckout_order_status', $checkout->status);

        // Add Payson Checkout Id.
        update_post_meta($order->get_id(), '_payson_checkout_id', $checkout->id);

        // Set status to pending
        $order->update_status('pending');
        //error_log('pending');

        // Change the order status to Processing/Completed in WooCommerce.
        $order->payment_complete($checkout->purchaseId);

        header('HTTP/1.0 200 OK');
    }

    /**
     * Adds order addresses to local order.
     *
     * @since  1.0.0
     * @access public
     *
     * @param \WC_Order $order
     * @param Checkout  $checkout
     */
    public function addOrderAddresses(\WC_Order $order, Checkout $checkout)
    {
        $order_id = $order->get_id();


        // Add customer billing address - retrieved from callback from Payson.
        update_post_meta($order_id, '_billing_first_name', $checkout->customer->firstName);
        update_post_meta($order_id, '_billing_last_name', $checkout->customer->lastName);
        update_post_meta($order_id, '_billing_address_1', $checkout->customer->street);
        update_post_meta($order_id, '_billing_postcode', $checkout->customer->postalCode);
        update_post_meta($order_id, '_billing_city', $checkout->customer->city);
        update_post_meta($order_id, '_billing_country', $checkout->customer->countryCode);
        update_post_meta($order_id, '_billing_email', $checkout->customer->email);
        update_post_meta($order_id, '_billing_phone', $checkout->customer->phone);

        // Add customer shipping address - retrieved from callback from Payson.
        update_post_meta($order_id, '_shipping_first_name', $checkout->customer->firstName);
        update_post_meta($order_id, '_shipping_last_name', $checkout->customer->lastName);
        update_post_meta($order_id, '_shipping_address_1', $checkout->customer->street);
        update_post_meta($order_id, '_shipping_postcode', $checkout->customer->postalCode);
        update_post_meta($order_id, '_shipping_city', $checkout->customer->city);
        update_post_meta($order_id, '_shipping_country', $checkout->customer->countryCode);

        // Store PaysonCheckout locale.
        update_post_meta($order_id, '_payson_locale', $checkout->gui->locale);
    }

    /**
     * Handle an expired PaysonCheckout resource.
     * Force deletes WooCommerce order, skipping Trash.
     *
     * @param \WC_Order $order WooCommerce order.
     */
    protected function expiredCb($order)
    {
        if ($order->has_status('payson-incomplete')) {
            wp_delete_post($order->get_id(), true);
        }
        header('HTTP/1.0 200 OK');
    }

    /**
     * Handle a denied PaysonCheckout payment.
     * Marks WooCommerce order as cancelled and adds order note.
     *
     * @param \WC_Order $order WooCommerce order.
     */
    protected function deniedCb(\WC_Order $order)
    {
        $order->update_status('cancelled', __('PaysonCheckout payment was denied.', 'woocommerce'));
        header('HTTP/1.0 200 OK');
    }
}

