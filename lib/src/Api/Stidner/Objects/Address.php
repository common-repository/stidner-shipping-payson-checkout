<?php

namespace Stidner\Api\Stidner\Objects;

/**
 * Class Address
 * @package Stidner\Api\Stidner\Objects
 */
/**
 * Class Address
 * @package Stidner\Api\Stidner\Objects
 */
class Address extends AbstractObject
{

    /**
     *
     */
    const TYPE_SENDER = 'sender';

    /**
     *
     */
    const TYPE_RECIPIENT = 'recipient';

    /**
     *
     */
    const TYPE_RETURN = 'return';

    /**
     *
     */
    const CUSTOMER_PERSON = 'person';

    /**
     *
     */
    const CUSTOMER_BUSINESS = 'business';

    /**
     * @var string
     */
    public $customer_type;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $country_code;
    /**
     * @var string
     */
    public $postal_code;
    /**
     * @var string
     */
    public $city;
    /**
     * @var string|null
     */
    public $region;
    /**
     * @var string
     */
    public $address_line;
    /**
     * @var string|null
     */
    public $address_line_2;
    /**
     * @var string
     */
    public $contact_name;
    /**
     * @var string
     */
    public $contact_phone;
    /**
     * @var string
     */
    public $contact_email;

    /**
     * @return string
     */
    public function getCustomerType()
    {
        return $this->customer_type;
    }

    /**
     * @param string $customer_type
     *
     * @return Address
     */
    public function setCustomerType($customer_type)
    {
        $this->customer_type = $customer_type;

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
     * @return Address
     */
    public function setType($type)
    {
        $this->type = $type;

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
     * @return Address
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }

    /**
     * @param string $country_code
     *
     * @return Address
     */
    public function setCountryCode($country_code)
    {
        $this->country_code = $country_code;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postal_code;
    }

    /**
     * @param string $postal_code
     *
     * @return Address
     */
    public function setPostalCode($postal_code)
    {
        $this->postal_code = $postal_code;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     *
     * @return Address
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param null|string $region
     *
     * @return Address
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddressLine()
    {
        return $this->address_line;
    }

    /**
     * @param string $address_line
     *
     * @return Address
     */
    public function setAddressLine($address_line)
    {
        $this->address_line = $address_line;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getAddressLine2()
    {
        return $this->address_line_2;
    }

    /**
     * @param null|string $address_line_2
     *
     * @return Address
     */
    public function setAddressLine2($address_line_2)
    {
        $this->address_line_2 = $address_line_2;

        return $this;
    }

    /**
     * @return string
     */
    public function getContactName()
    {
        return $this->contact_name;
    }

    /**
     * @param string $contact_name
     *
     * @return Address
     */
    public function setContactName($contact_name)
    {
        $this->contact_name = $contact_name;

        return $this;
    }

    /**
     * @return string
     */
    public function getContactPhone()
    {
        return $this->contact_phone;
    }

    /**
     * @param string $contact_phone
     *
     * @return Address
     */
    public function setContactPhone($contact_phone)
    {
        $this->contact_phone = $contact_phone;

        return $this;
    }

    /**
     * @return string
     */
    public function getContactEmail()
    {
        return $this->contact_email;
    }

    /**
     * @param string $contact_email
     *
     * @return Address
     */
    public function setContactEmail($contact_email)
    {
        $this->contact_email = $contact_email;

        return $this;
    }
}