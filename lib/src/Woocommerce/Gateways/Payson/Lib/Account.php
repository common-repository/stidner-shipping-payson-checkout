<?php

namespace Stidner\Woocommerce\Gateways\Payson\Lib;

/**
 * Class Account
 * @package Stidner\Woocommerce\Gateways\Payson\Lib
 */
class Account
{
    /** @var string $accountEmail */
    public $accountEmail;
    /** @var string $status */
    public $status;
    /** @var int $merchantId */
    public $merchantId;
    /** @var string $enabledForInvoice */
    public $enabledForInvoice;
    /** @var string $enabledForPaymentPlan */
    public $enabledForPaymentPlan;


    /**
     * Account constructor.
     *
     * @param $accountEmail
     * @param $status
     * @param $merchantId
     * @param $enabledForInvoice
     * @param $enabledForpaymentPlan
     */
    public function __construct($accountEmail, $status, $merchantId, $enabledForInvoice, $enabledForpaymentPlan)
    {
        $this->accountEmail = $accountEmail;
        $this->status = $status;
        $this->merchantId = $merchantId;
        $this->enabledForInvoice = $enabledForInvoice;
        $this->enabledForpaymentPlan = $enabledForpaymentPlan;
    }

    /**
     * @param $data
     *
     * @return Account
     */
    public static function create($data)
    {
        return new Account($data->accountEmail, $data->status, $data->merchantId, $data->enabledForInvoice,
            $data->enabledForpaymentPlan);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
