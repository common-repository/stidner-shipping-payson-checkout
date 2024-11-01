<?php

namespace Stidner\Api\Stidner\Objects;

/**
 * Class ServicePoint
 * @package Stidner\Api\Stidner\Objects
 */
/**
 * Class ServicePoint
 * @package Stidner\Api\Stidner\Objects
 */
class ServicePoint extends AbstractObject
{
    /**
     * @var string
     */
    protected $handle;
    /**
     * @var string
     */
    protected $name;

    /**
     * @var
     */
    protected $distance;
    /**
     * @var
     */
    protected $country_code;
    /**
     * @var
     */
    protected $postal_code;
    /**
     * @var
     */
    protected $city;
    /**
     * @var
     */
    protected $address_line;
    /**
     * @var
     */
    protected $latitude;
    /**
     * @var
     */
    protected $longitude;
    /**
     * @var
     */
    protected $operation_hours;

    /**
     * ServicePoint constructor.
     *
     * @param null $std
     */
    public function __construct($std = null)
    {
        if (!is_null($std)) {
            return $this->toObject($std);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @param mixed $distance
     *
     * @return ServicePoint
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }

    /**
     * @param mixed $country_code
     *
     * @return ServicePoint
     */
    public function setCountryCode($country_code)
    {
        $this->country_code = $country_code;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostalCode()
    {
        return $this->postal_code;
    }

    /**
     * @param mixed $postal_code
     *
     * @return ServicePoint
     */
    public function setPostalCode($postal_code)
    {
        $this->postal_code = $postal_code;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     *
     * @return ServicePoint
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddressLine()
    {
        return $this->address_line;
    }

    /**
     * @param mixed $address_line
     *
     * @return ServicePoint
     */
    public function setAddressLine($address_line)
    {
        $this->address_line = $address_line;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param mixed $latitude
     *
     * @return ServicePoint
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param mixed $longitude
     *
     * @return ServicePoint
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOperationHours()
    {
        return $this->operation_hours;
    }

    /**
     * @param mixed $operation_hours
     *
     * @return ServicePoint
     */
    public function setOperationHours($operation_hours)
    {
        $this->operation_hours = $operation_hours;

        return $this;
    }

    /**
     * @return string
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * @param string $handle
     *
     * @return ServicePoint
     */
    public function setHandle($handle)
    {
        $this->handle = $handle;

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
     * @return ServicePoint
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}