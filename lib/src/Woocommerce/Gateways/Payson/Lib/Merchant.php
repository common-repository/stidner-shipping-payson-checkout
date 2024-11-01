<?php

namespace Stidner\Woocommerce\Gateways\Payson\Lib;

/**
 * Class Merchant
 * @package Stidner\Woocommerce\Gateways\Payson\Lib
 */
class Merchant
{
    /** @var url $checkoutUri URI to the merchants checkout page. */
    public $checkoutUri = null;
    /** @var url $confirmationUri URI to the merchants confirmation page. */
    public $confirmationUri;
    /** @var url $notificationUri Notification URI which receives CPR-status updates. */
    public $notificationUri;
    /** @var url $verificationUri Validation URI which is called to verify an order before it can be paid. */
    public $validationUri = null;
    /** @var url $termsUri URI leading to the sellers terms. */
    public $termsUri;
    /** @var string $reference Merchants own reference of the checkout. */
    public $reference = null;
    /** @var string $partnerId Partners unique identifier */
    public $partnerId = null;
    /** @var string $integrationInfo Information about the integration. */
    public $integrationInfo = null;

    /**
     * Merchant constructor.
     *
     * @param        $checkoutUri
     * @param        $confirmationUri
     * @param        $notificationUri
     * @param        $termsUri
     * @param null   $partnerId
     * @param        $reference
     * @param string $integrationInfo
     */
    public function __construct(
        $checkoutUri,
        $confirmationUri,
        $notificationUri,
        $termsUri,
        $partnerId = 'Stidner',
        $reference,
        $integrationInfo = 'StidnerPaysonWoocommerce'
    ) {
        $this->checkoutUri = $checkoutUri;
        $this->confirmationUri = $confirmationUri;
        $this->notificationUri = $notificationUri;
        $this->termsUri = $termsUri;
        $this->partnerId = $partnerId;
        $this->reference = $reference;
        $this->integrationInfo = $integrationInfo;
    }

    /**
     * @param $data
     *
     * @return Merchant
     */
    public static function create($data)
    {
        $merchant = new Merchant($data->checkoutUri, $data->confirmationUri, $data->notificationUri, $data->termsUri,
            $data->partnerId, $data->integrationInfo);
        $merchant->reference = $data->reference;
        $merchant->validationUri = $data->validationUri;

        return $merchant;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
