<?php

namespace Stidner\Woocommerce;

use Stidner\Init;

/**
 * Class Shipping
 * @package Stidner\Woocommerce
 */
class Shipping extends \WC_Shipping_Method
{

    /**
     * Shipping constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->init_settings();
        $this->id = Init::PREFIX;
        $this->method_title = Init::SERVICE_NAME;
        $this->title = '';
        $this->method_description = '';
        $this->enabled = 'yes';

        $this->init();
    }

    /**
     *
     */
    public function init()
    {
        $this->init_form_fields();
        $this->init_settings();

        add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
    }

    /**
     *
     */
    public function init_form_fields()
    {
        $this->form_fields = [
            'merchant_id' => [
                'title'       => __('Merchant ID', Init::TEXT_DOMAIN),
                'type'        => 'text',
                'description' => __('Your Merchant ID from <a target="_blank" href="https://dashboard.stidner.com/panel/api">dashboard</a>', Init::TEXT_DOMAIN),
                'default'     => ''
            ],
            'api_key'     => [
                'title'       => __('API Key', Init::TEXT_DOMAIN),
                'type'        => 'text',
                'description' => __('Your API Key from <a target="_blank" href="https://dashboard.stidner.com/panel/api">dashboard</a>', Init::TEXT_DOMAIN),
                'default'     => ''
            ],
            'step_1'     => [
                'title'       => __('Step 1 Title', Init::TEXT_DOMAIN),
                'type'        => 'text',
                'description' => '',
                'default'     => 'Steg 1. Se över er order'
            ],
            'step_2'     => [
                'title'       => __('Step 2 Title', Init::TEXT_DOMAIN),
                'type'        => 'text',
                'description' => '',
                'default'     => 'Steg 2. Välj frakt'
            ],
            'step_3' => [
                'title'       => __('Step 3 Title', Init::TEXT_DOMAIN),
                'type'        => 'text',
                'description' => '',
                'default'     => 'Steg 3. Betalning',
            ],
        ];
    }

    /**
     *
     */
    public function is_taxable()
    {
        return false;
    }

    /**
     * @param array $package
     */
    public function calculate_shipping($package = [])
    {

        $rate = [
            'id'    => $this->id,
            'label' => $this->title,
            'cost'  => 0,
            'taxes' => false,
        ];

        $this->add_rate($rate);
    }

}
