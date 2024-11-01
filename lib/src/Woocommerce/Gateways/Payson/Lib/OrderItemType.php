<?php

namespace Stidner\Woocommerce\Gateways\Payson\Lib;

/**
 * Class OrderItemType
 * @package Stidner\Woocommerce\Gateways\Payson\Lib
 */
abstract class OrderItemType
{
    /**
     *
     */
    const PHYSICAL = 'physical';
    /**
     *
     */
    const DISCOUNT = 'discount';
    /**
     *
     */
    const SERVICE = 'service';
}