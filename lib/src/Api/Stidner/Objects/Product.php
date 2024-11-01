<?php

namespace Stidner\Api\Stidner\Objects;

/**
 * Class Product
 * @package Stidner\Api\Stidner\Objects
 */
class Product extends AbstractObject
{
    /**
     * @var string|null
     */
    protected $handle;
    /**
     * @var string|null
     */
    protected $name;
    /**
     * @var integer
     */
    protected $price;
    /**
     * @var
     */
    protected $delivery_time;
    /**
     * @var string
     */
    protected $delivery_time_trust_level;

    /**
     * @return null|string
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * @param null|string $handle
     *
     * @return Product
     */
    public function setHandle($handle)
    {
        $this->handle = $handle;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param null|string $name
     *
     * @return Product
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param int $price
     *
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDeliveryTime()
    {
        return $this->delivery_time;
    }

    /**
     * @param mixed $delivery_time
     *
     * @return Product
     */
    public function setDeliveryTime($delivery_time)
    {
        $this->delivery_time = $delivery_time;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryTimeTrustLevel()
    {
        return $this->delivery_time_trust_level;
    }

    /**
     * @param string $delivery_time_trust_level
     *
     * @return Product
     */
    public function setDeliveryTimeTrustLevel($delivery_time_trust_level)
    {
        $this->delivery_time_trust_level = $delivery_time_trust_level;

        return $this;
    }


}