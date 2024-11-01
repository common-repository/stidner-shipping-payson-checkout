<?php

namespace Stidner\Api\Stidner\Objects;

/**
 * Class Package
 * @package Stidner\Api\Stidner\Objects
 */
/**
 * Class Package
 * @package Stidner\Api\Stidner\Objects
 */
class Package extends AbstractObject
{

    /**
     * @var boolean
     */
    public $also_return;
    /**
     * @var string
     */
    protected $uuid;
    /**
     * @var string
     */
    protected $direction;
    /**
     * @var string
     */
    protected $shipment_number;
    /**
     * @var string
     */
    protected $label_url;
    /**
     * @var string|null
     */
    protected $customs_invoice_url;
    /**
     * @var Item[]
     */
    protected $items;
    /**
     * @var boolean
     */
    protected $autoprint;
    /**
     * @var \datetime
     */
    protected $shipped_at;
    protected $tracking_url;
    protected $pickup_status;
    protected $pickup_identifier;
    protected $pickup_errors;

    /**
     * @return mixed
     */
    public function getPickupStatus()
    {
        return $this->pickup_status;
    }

    /**
     * @param mixed $pickup_status
     *
     * @return Package
     */
    public function setPickupStatus($pickup_status)
    {
        $this->pickup_status = $pickup_status;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPickupIdentifier()
    {
        return $this->pickup_identifier;
    }

    /**
     * @param mixed $pickup_identifier
     *
     * @return Package
     */
    public function setPickupIdentifier($pickup_identifier)
    {
        $this->pickup_identifier = $pickup_identifier;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPickupErrors()
    {
        return $this->pickup_errors;
    }

    /**
     * @param mixed $pickup_errors
     *
     * @return Package
     */
    public function setPickupErrors($pickup_errors)
    {
        $this->pickup_errors = $pickup_errors;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTrackingUrl()
    {
        return $this->tracking_url;
    }

    /**
     * @param mixed $tracking_url
     *
     * @return Package
     */
    public function setTrackingUrl($tracking_url)
    {
        $this->tracking_url = $tracking_url;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isAlsoReturn()
    {
        return $this->also_return;
    }

    /**
     * @param boolean $also_return
     *
     * @return Package
     */
    public function setAlsoReturn($also_return)
    {
        $this->also_return = $also_return;

        return $this;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     *
     * @return Package
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @param string $direction
     *
     * @return Package
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;

        return $this;
    }

    /**
     * @return string
     */
    public function getShipmentNumber()
    {
        return $this->shipment_number;
    }

    /**
     * @param string $shipment_number
     *
     * @return Package
     */
    public function setShipmentNumber($shipment_number)
    {
        $this->shipment_number = $shipment_number;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabelUrl()
    {
        return $this->label_url;
    }

    /**
     * @param string $label_url
     *
     * @return Package
     */
    public function setLabelUrl($label_url)
    {
        $this->label_url = $label_url;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCustomsInvoiceUrl()
    {
        return $this->customs_invoice_url;
    }

    /**
     * @param null|string $customs_invoice_url
     *
     * @return Package
     */
    public function setCustomsInvoiceUrl($customs_invoice_url)
    {
        $this->customs_invoice_url = $customs_invoice_url;

        return $this;
    }

    /**
     * @return Item[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param Item[] $items
     *
     * @return Package
     */
    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isAutoprint()
    {
        return $this->autoprint;
    }

    /**
     * @param boolean $autoprint
     *
     * @return Package
     */
    public function setAutoprint($autoprint)
    {
        $this->autoprint = $autoprint;

        return $this;
    }

    /**
     * @return \datetime
     */
    public function getShippedAt()
    {
        return $this->shipped_at;
    }

    /**
     * @param \datetime $shipped_at
     *
     * @return Package
     */
    public function setShippedAt($shipped_at)
    {
        $this->shipped_at = $shipped_at;

        return $this;
    }
}