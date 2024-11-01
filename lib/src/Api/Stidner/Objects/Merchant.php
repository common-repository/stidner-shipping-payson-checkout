<?php

namespace Stidner\Api\Stidner\Objects;

/**
 * Class Merchant
 * @package Stidner\Api\Stidner\Objects
 */
class Merchant extends AbstractObject
{
    /**
     * @var integer
     */
    protected $id;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $type;
    /**
     * @var string
     */
    protected $organisation_number;
    /**
     * @var string
     */
    protected $vat_number;
    /**
     * @var string
     */
    protected $api_key_live;
    /**
     * @var string
     */
    protected $api_key_sandbox;
    /**
     * @var boolean
     */
    protected $active;
    /**
     * @var Address[]
     */
    protected $addresses;
    /**
     * @var \datetime
     */
    protected $created_at;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Merchant
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Merchant
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * @return Merchant
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrganisationNumber()
    {
        return $this->organisation_number;
    }

    /**
     * @param string $organisation_number
     *
     * @return Merchant
     */
    public function setOrganisationNumber($organisation_number)
    {
        $this->organisation_number = $organisation_number;

        return $this;
    }

    /**
     * @return string
     */
    public function getVatNumber()
    {
        return $this->vat_number;
    }

    /**
     * @param string $vat_number
     *
     * @return Merchant
     */
    public function setVatNumber($vat_number)
    {
        $this->vat_number = $vat_number;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiKeyLive()
    {
        return $this->api_key_live;
    }

    /**
     * @param string $api_key_live
     *
     * @return Merchant
     */
    public function setApiKeyLive($api_key_live)
    {
        $this->api_key_live = $api_key_live;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiKeySandbox()
    {
        return $this->api_key_sandbox;
    }

    /**
     * @param string $api_key_sandbox
     *
     * @return Merchant
     */
    public function setApiKeySandbox($api_key_sandbox)
    {
        $this->api_key_sandbox = $api_key_sandbox;

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
     * @return Merchant
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Address[]
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @param Address[] $addresses
     *
     * @return Merchant
     */
    public function setAddresses($addresses)
    {
        $this->addresses = $addresses;

        return $this;
    }

    /**
     * @return \datetime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param \datetime $created_at
     *
     * @return Merchant
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }
}