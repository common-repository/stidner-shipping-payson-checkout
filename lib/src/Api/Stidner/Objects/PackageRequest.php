<?php

namespace Stidner\Api\Stidner\Objects;

/**
 * Class PackageRequest
 * @package Stidner\Api\Stidner\Objects
 */
class PackageRequest extends AbstractObject
{

    /**
     * @var \stdClass
     */
    protected $order;
    /**
     * @var \stdClass
     */
    protected $package;
    /**
     * @var \stdClass
     */
    protected $pickup;

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param $order
     *
     * @return PackageRequest
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return Package
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @return Package
     */
    public function getPackageItems()
    {
        return $this->package->items;
    }

    /**
     * @param $package
     *
     * @return PackageRequest
     */
    public function setPackage($package)
    {
        $this->package = $package;

        return $this;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->package->direction ?: null;
    }

    /**
     * @return \stdClass
     */
    public function getPickup()
    {
        return $this->pickup;
    }

    /**
     * @param $pickup
     *
     * @return PackageRequest
     */
    public function setPickup($pickup)
    {
        $this->pickup = $pickup;

        return $this;
    }


}