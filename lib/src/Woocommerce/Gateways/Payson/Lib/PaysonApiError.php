<?php

namespace Stidner\Woocommerce\Gateways\Payson\Lib;

/**
 * Class PaysonApiError
 * @package Stidner\Woocommerce\Gateways\Payson\Lib
 */
class PaysonApiError
{
    /**
     * @var null
     */
    public $message = null;
    /**
     * @var null
     */
    public $parameter = null;

    /**
     * PaysonApiError constructor.
     *
     * @param      $message
     * @param null $parameter
     */
    public function __construct($message, $parameter = null)
    {
        $this->message = $message;
        $this->parameter = $parameter;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "Message: " . $this->message . "\t Parameter: " . $this->parameter;
    }

}