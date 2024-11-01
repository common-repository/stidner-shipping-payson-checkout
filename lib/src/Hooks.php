<?php

namespace Stidner;

use Stidner\Woocommerce\CheckoutActions;
use Stidner\Woocommerce\CheckoutPage;
use Stidner\Woocommerce\Gateways\Payson\WcPaysonCheckoutResponseHandler;
use Stidner\Woocommerce\OrderActions;
use Stidner\Woocommerce\OrderTable;

class Hooks
{
    public function __construct()
    {

        $this->init();
        $this->orderTable();
        $this->checkoutActions();
    }

    private function init()
    {

        add_action(
            'template_redirect', function () {
            global $wp;
            if (is_checkout() and empty($wp->query_vars['order-received'])) {
                wp_redirect(wc_get_cart_url());
                exit;
            }
        }
        );
        
       if(is_admin()){
           add_action(
               'post_updated', [OrderTable::class, 'onAddressUpdated'], 10, 3
           );
       }

        add_filter(
            'wc_get_template', [Init::class, 'changeCartTemplate'], 1, 4
        );

        remove_action(
            'woocommerce_view_order', 'woocommerce_order_details_table'
        );

        remove_action(
            'woocommerce_thankyou', 'woocommerce_order_details_table'
        );

        remove_action(
            'woocommerce_view_order', 'woocommerce_thankyou'
        );

        remove_action(
            'woocommerce_order_details_after_order_table', 'woocommerce_order_again_button'
        );

        remove_action(
            'woocommerce_cart_collaterals', 'woocommerce_cart_totals'
        );

        remove_action(
            'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display'
        );

        add_action(
            'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display'
        );

        add_filter(
            'woocommerce_get_checkout_url', [Init::class, 'setCheckoutUrl']
        );
        add_action(
            'wp_enqueue_scripts', [Init::class, 'includeAssets']
        );
        add_filter(
            'woocommerce_shipping_methods', [Init::class, 'addStidnerShippingMethod']
        );
        add_filter(
            'woocommerce_payment_gateways', [Init::class, 'addPaymentGateways']
        );
        add_action(
            'admin_init', [Init::class, 'activate']
        );

        add_action(
            'woocommerce_api_wc_gateway_paysoncheckout', [new WcPaysonCheckoutResponseHandler, 'notificationListener']
        );

        add_action(
            'wp_ajax_stidner_notification', [OrderActions::class, 'callbackHandler']
        );

        add_action(
            'wp_ajax_nopriv_stidner_notification', [OrderActions::class, 'callbackHandler']
        );

        add_action(
            'wp_ajax_stidner_cart_updated', [CheckoutPage::class, 'cartUpdated']
        );

        add_action(
            'wp_ajax_nopriv_stidner_cart_updated', [CheckoutPage::class, 'cartUpdated']
        );



    }

    private function orderTable()
    {

        add_filter(
            'manage_edit-shop_order_columns', [Woocommerce\OrderTable::class, 'columns'], 11
        );
        add_action(
            'admin_head', [Woocommerce\OrderTable::class, 'orderTableStyle']
        );
        add_action(
            'wp_ajax_create_stidner_package', [Woocommerce\OrderTable::class, 'createStidnerPackage']
        );
        add_action(
            'manage_shop_order_posts_custom_column', [Woocommerce\OrderTable::class, 'columnContent'], 10, 2
        );

        add_action(
            'wp_ajax_stidner_request_pickup', [OrderTable::class, 'requestPickup']
        );
    }

    private function checkoutActions()
    {

        add_filter(
            'woocommerce_shipping_packages', [Woocommerce\CheckoutActions::class, 'calculatePackageRates'], 100, 2
        );
        add_action(
            'woocommerce_calculate_totals', [Woocommerce\CheckoutActions::class, 'calculateTotals'], 100, 1
        );

        add_action(
            'wp_ajax_stidner_update', [Woocommerce\CheckoutActions::class, 'stidnerOrderUpdated']
        );

        add_action(
            'wp_ajax_nopriv_stidner_update', [Woocommerce\CheckoutActions::class, 'stidnerOrderUpdated']
        );

        add_action(
            'wp_ajax_stidner_address_updated', [Woocommerce\CheckoutActions::class, 'addressUpdated']
        );

        add_action(
            'wp_ajax_nopriv_stidner_address_updated', [Woocommerce\CheckoutActions::class, 'addressUpdated']
        );

        add_action(
            'wp_ajax_stidner_widget_address_updated', [
                Woocommerce\CheckoutActions::class,
                'stidnerWidgetAddressUpdated',
            ]
        );

        add_action(
            'wp_ajax_nopriv_stidner_widget_address_updated', [
                Woocommerce\CheckoutActions::class,
                'stidnerWidgetAddressUpdated',
            ]
        );

        //TODO test
        add_action(
            'wp_ajax_stidner_cart_updated', [CheckoutActions::class, 'stidnerOrderUpdated']
        );

        add_action(
            'wp_ajax_nopriv_stidner_cart_updated', [CheckoutActions::class, 'stidnerOrderUpdated']
        );

        add_filter(
            'the_title', function ($title) {
            global $wp;
            if (is_checkout() && !empty($wp->query_vars['order-received'])) {
                $title = '';
            }

            return $title;
        }
        );
    }
}