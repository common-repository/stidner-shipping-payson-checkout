<?php

namespace Stidner\Api\Stidner\Objects;

/**
 * Class OrderProducts
 * @package Stidner\Api\Stidner\Objects
 */
class OrderProducts extends AbstractObject
{
    /**
     * @var string
     */
    protected $uuid;
    /**
     * @var string
     */
    protected $type;
    /**
     * @var string
     */
    protected $carrier_handle;
    /**
     * @var string
     */
    protected $carrier_logo;
    /**
     * @var boolean
     */
    protected $trackable;
    /**
     * @var ServicePoint[]
     */
    protected $service_points;
    /**
     * @var array|null
     */
    protected $products;

    public function __construct($std = null)
    {
        if (!is_null($std)) {
            return $this->toOrder($std);
        }

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
     * @return OrderProducts
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return OrderProducts
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getCarrierHandle()
    {
        return $this->carrier_handle;
    }

    /**
     * @param string $carrier
     *
     * @return OrderProducts
     */
    public function setCarrierHandle($carrier_handle)
    {
        $this->carrier_handle = $carrier_handle;

        return $this;
    }

    /**
     * @return string
     */
    public function getCarrierLogo()
    {
        return 'https://cdn.stidner.com/images/carriers/' . ($this->getCarrierHandle() ?: 'empty') . '.png';
    }

    /**
     * @return boolean
     */
    public function isTrackable()
    {
        return $this->trackable;
    }

    /**
     * @param boolean $trackable
     *
     * @return OrderProducts
     */
    public function setTrackable($trackable)
    {
        $this->trackable = $trackable;

        return $this;
    }

    /**
     * @return ServicePoint[]
     */
    public function getServicePoints()
    {
        $service_points = [];
        foreach ($this->service_points as $service_point) {
            $service_points[] = new ServicePoint($service_point);
        }

        return $service_points;
    }

    /**
     * @param array|null $service_points
     *
     * @return OrderProducts
     */
    public function setServicePoints($service_points)
    {
        $this->service_points = $service_points;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param array|null $products
     *
     * @return OrderProducts
     */
    public function setProducts($products)
    {
        $this->products = $products;

        return $this;
    }

}