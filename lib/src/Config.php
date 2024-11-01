<?php

namespace Stidner;

/**
 * Class Config
 * @package Stidner
 */
class Config
{

    /**
     * Main prefix for everything
     */
    const PREFIX = 'stidner';

    /**
     * Used as default value in Woocommerce settings
     */
    const SERVICE_NAME = 'Stidner';

    /**
     * Text domain for translation support
     */
    const TEXT_DOMAIN = 'stidner';

    /**
     * Order meta received from Stidner api, stored in order meta
     * Use in get_post_meta(), update_post_meta() WP built-in functions
     * */
    const ORDER_META = 'stidner_order';


    /**
     * Used on order creation in recipient address
     * */
    const DEFAULT_COUNTRY_CODE = 'SE';

    /**
     *  Default shipping reference for shipping object in payment gateway's order
     * */
    const SHIPPING_REFERENCE = '__stidner_shipping_fee';

    /**
     * Stored in woocommerce session
     * */
    const STIDNER_INCOMPLETE_ORDER = 'stidner_incomplete_order';

    /**
     *  Index for Woocommerce log file
     * */
    const LOG_SOURCE = 'stidner-shipping';

    /**
     *  Used in order table
     * */
    const WOOCOMMERCE_COLUMN_SLUG = 'stidner_shipping_status';

    /**
     *  Used in order table
     * */
    const WOOCOMMERCE_COLUMN_NAME = 'Stidner shipping information';

    /**
     * Used in endpoint
     * */
    const REST_NAMESPACE = 'stidner/v';
    /**
     * Used in endpoint
     * */
    const REST_VERSION = '1';
    /**
     * Used in endpoint
     * */
    const REST_ROUTE = '/order/(?P<id>\d+)/(?P<key>\w+)';

}