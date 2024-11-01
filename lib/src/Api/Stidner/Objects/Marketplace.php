<?php

namespace Stidner\Api\Stidner\Objects;

/**
 * Class Marketplace
 * @package Stidner\Api\Stidner\Objects
 */
class Marketplace extends AbstractObject
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
     * @var string Marketplace credentials in JSON format.
     */
    protected $credentials;
    /**
     * @var boolean
     */
    protected $active;

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
     * @return Marketplace
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
     * @return Marketplace
     */
    public function setType($type)
    {
        $this->type = $type;

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
     * @return Marketplace
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
     * @return Marketplace
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }
}