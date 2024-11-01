<?php

namespace Stidner\Api\Stidner\Objects;

/**
 * Class Agreement
 * @package Stidner\Api\Stidner\Objects
 */
class Agreement extends AbstractObject
{
    /**
     * @var string
     */
    protected $carrier;
    /**
     * @var string
     */
    protected $credentials;
    /**
     * @var boolean
     */
    protected $active;

    /**
     * @return string
     */
    public function getCarrier()
    {
        return $this->carrier;
    }

    /**
     * @param string $carrier
     *
     * @return Agreement
     */
    public function setCarrier($carrier)
    {
        $this->carrier = $carrier;

        return $this;
    }

    /**
     * @return string
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * @param string $credentials
     *
     * @return Agreement
     */
    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     *
     * @return Agreement
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }


}