<?php

namespace Stidner\Woocommerce\Gateways\Payson\Lib;

/**
 * Class PayData
 * @package Stidner\Woocommerce\Gateways\Payson\Lib
 */
class PayData
{
    /** @var string $currency Currency of the order ("sek", "eur"). */
    public $currency = null;
    /** @var array $items An array with objects of the order items */
    public $items = array();

    /** @var float $totalPriceExcludingTax - Read only */
    public $totalPriceExcludingTax;
    /** @var float $totalPriceIncludingTax - Read only */
    public $totalPriceIncludingTax;
    /** @var float $totalTaxAmount - Read only */
    public $totalTaxAmount;
    /** @var float $totalCreditedAmount - Read only */
    public $totalCreditedAmount;

    /**
     * PayData constructor.
     *
     * @param $currencyCode
     */
    public function __construct($currencyCode)
    {
        $this->currency = $currencyCode;
        $this->items = array();
    }

    /**
     * @param $data
     *
     * @return PayData
     */
    public static function create($data)
    {
        $payData = new PayData($data->currency);
        $payData->totalPriceExcludingTax = $data->totalPriceExcludingTax;
        $payData->totalPriceIncludingTax = $data->totalPriceIncludingTax;
        $payData->totalTaxAmount = $data->totalTaxAmount;
        $payData->totalCreditedAmount = $data->totalCreditedAmount;

        foreach ($data->items ?: [] as $item) {
            $payData->items[] = OrderItem::create($item);
        }

        return $payData;
    }

    /**
     * @param OrderItem $item
     */
    public function AddOrderItem(OrderItem $item)
    {
        $this->items[] = $item;
    }

    /**
     * @param $items
     *
     * @throws PaysonApiException
     */
    public function setOrderItems($items)
    {
        if (!($items instanceof OrderItem)) {
            throw new PaysonApiException("Parameter must be an object of class Item");
        }

        $this->items = $items;
    }

    /**
     * @return mixed|string|void
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $items = array();
        foreach ($this->items as $item) {
            $items[] = $item->toArray();
        }

        return array('currency' => $this->currency, 'items' => $items);
    }
}