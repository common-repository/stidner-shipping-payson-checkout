<?php

namespace Stidner\Woocommerce\Gateways\Payson;

use Stidner\Init;
use Stidner\Woocommerce\CheckoutPage;
use Stidner\Woocommerce\OrderActions;

class WcGatewayPaysonCheckout extends \WC_Payment_Gateway
{

    /** @var \WC_Logger Logger instance */
    public static $log = false;

    /**
     * Constructor for the gateway.
     */
    public function __construct()
    {

        $this->id = 'paysoncheckout';
        $this->method_title = __('Payson', Init::TEXT_DOMAIN);
        $this->icon = '';
        $this->has_fields = true;
        $this->method_description = __('Allows payments through Payson. <a target="_blank" href="https://account.payson.se/account/create/?type=b">Click here to signup</a>.',
            Init::TEXT_DOMAIN);

        // Load the form fields.
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();

        // Define user set variables.
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->merchant_id = $this->get_option('merchant_id', '4');
        $this->testmode = $this->get_option('testmode', 'yes');
        $this->api_key = $this->get_option('api_key', '2acab30d-fe50-426f-90d7-8c60a7eb31d4');
        $this->color_scheme = $this->get_option('color_scheme');
        $this->request_phone = true;
        $this->debug = $this->get_option('debug');
        add_filter('the_title', [$this, 'changeThankYouPageTitle'], 10, 2);


        // Supports.
        $this->supports = ['products'];

        // Actions.
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [
            $this,
            'process_admin_options'
        ]);
        add_action('woocommerce_thankyou_paysoncheckout', [$this, 'payson_thankyou']);
    }

    /**
     * Initialise Gateway Settings Form Fields.
     */
    public function init_form_fields()
    {
        $this->form_fields = [
            'merchant_id'  => [
                'title'       => __('Agent ID', Init::TEXT_DOMAIN),
                'type'        => 'text',
                'description' => __('Your Payson Agent ID. <a target="_blank" href="https://account.payson.se/account/create/?type=b">Click here to sign up</a>, if you aren\'t currently a Payson customer',
                    Init::TEXT_DOMAIN),
                'default'     => '4',
            ],
            'api_key'      => [
                'title'       => __('API Key', Init::TEXT_DOMAIN),
                'type'        => 'text',
                'description' => __('Your Payson API Key. <a target="_blank" href="https://account.payson.se/account/create/?type=b">Click here to sign up</a>, if you aren\'t currently a Payson customer',
                    Init::TEXT_DOMAIN),
                'default'     => '2acab30d-fe50-426f-90d7-8c60a7eb31d4',
            ],
            'testmode'     => [
                'title'   => __('Testmode', Init::TEXT_DOMAIN),
                'type'    => 'checkbox',
                'label'   => __('Enable PaysonCheckout testmode', Init::TEXT_DOMAIN),
                'default' => 'yes',
            ],
            'color_scheme' => [
                'title'       => __('Color Scheme', Init::TEXT_DOMAIN),
                'type'        => 'select',
                'options'     => [
                    'Gray'           => __('Gray', Init::TEXT_DOMAIN),
                    'Blue'           => __('Blue', Init::TEXT_DOMAIN),
                    'White'          => __('White', Init::TEXT_DOMAIN),
                    'GrayTextLogos'  => __('GrayTextLogos', Init::TEXT_DOMAIN),
                    'BlueTextLogos'  => __('BlueTextLogos', Init::TEXT_DOMAIN),
                    'WhiteTextLogos' => __('WhiteTextLogos', Init::TEXT_DOMAIN),
                    'GrayNoFooter'   => __('GrayNoFooter', Init::TEXT_DOMAIN),
                    'BlueNoFooter'   => __('BlueNoFooter', Init::TEXT_DOMAIN),
                    'WhiteNoFooter'  => __('WhiteNoFooter', Init::TEXT_DOMAIN),
                ],
                'description' => __('Different color schemes for how the embedded PaysonCheckout iframe should be displayed.',
                    Init::TEXT_DOMAIN),
                'default'     => 'WhiteNoFooter',
                'desc_tip'    => true
            ],
            'show_terms'     => [
                'title'   => __('Show terms checkbox', Init::TEXT_DOMAIN),
                'type'    => 'checkbox',
                'label'   => __('Show checkbox for WooCommerce terms and conditions on checkout page', Init::TEXT_DOMAIN),
                'default' => 'no',
            ],
            'debug'        => [
                'title'       => __('Debug Log', Init::TEXT_DOMAIN),
                'type'        => 'checkbox',
                'label'       => __('Enable logging', Init::TEXT_DOMAIN),
                'default'     => 'no',
                'description' => ''
            ],
        ];
    }

    /**
     * Logging method.
     *
     * @param string $message
     */
    public static function log($message)
    {
        $paysoncheckout_settings = get_option('woocommerce_paysoncheckout_settings');
        if ($paysoncheckout_settings['debug'] === 'yes') {
            if (empty(self::$log)) {
                self::$log = new \WC_Logger();
            }
            self::$log->add('paysoncheckout', $message);
        }
    }

    /**
     * Check if this gateway is enabled and available in the user's country
     */
    function is_available()
    {
        //TODO remove in production

        return true;
        if ('yes' === $this->enabled) {
            if (!is_admin()) {
                // Currency check.
                if (!in_array(get_woocommerce_currency(), array('EUR', 'SEK'))) {
                    return false;
                }

                // Required fields check.
                if (!$this->merchant_id || !$this->api_key) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Get gateway icon.
     *
     * @return string
     */
    public function get_icon()
    {
        $icon_src = 'https://www.payson.se/sites/all/files/images/external/payson.png';
        $icon_width = '85';
        $icon_html = '<img src="' . $icon_src . '" alt="PaysonCheckout 2.0" style="max-width:' . $icon_width . 'px"/>';

        return apply_filters('wc_payson_icon_html', $icon_html);
    }

    /**
     * Add PaysonCheckout iframe to thankyou page.
     *
     * @param $order_id
     */
    public function payson_thankyou($order_id)
    {
        if (isset($_GET['paysonorder'])) {
            $payson_api = new WcPaysonCheckoutSetupPaysonAPI();

            try {
                $checkout = $payson_api->getNotificationCheckout($_GET['paysonorder']);
                if ('canceled' === $checkout->status) {
                    WC()->session->__unset('payson_checkout_id');

                    wc_add_notice(__('Order was cancelled.', Init::TEXT_DOMAIN), 'error');
                    wp_safe_redirect(wc_get_cart_url());
                } else {

                    if ('readyToShip' === $checkout->status) {
                        //remove payment gateway session
                        WC()->session->set('ongoing_payson_order', null); // for payson only for now
                        WC()->session->set('payson_checkout_id', null);
                        $wc_order = wc_get_order($order_id);
                        $payson_response_handler = new WcPaysonCheckoutResponseHandler();
                        $payson_response_handler->readyToShipCb($wc_order, $checkout);
                        echo '<div class="paysoncheckout-container" style="width:100%;margin-bottom:10px; margin-left:auto;margin-right:auto;">';
                        echo $checkout->snippet;
                        echo '</div>';

                        //remove payment stidner session
                        CheckoutPage::successShippingWidget($wc_order);
                        OrderActions::clearSession();

                    }

                }
            } catch (\Exception $e) {
                echo 'Internal Payson error';
            }
        }
    }

    public function changeThankYouPageTitle($title, $id)
    {
        if (is_order_received_page()) {
            return '';
        }

        return $title;


    }


}
