<?php

namespace Stidner\Woocommerce\Gateways\Payson\Lib;

/**
 * Class Customer
 * @package Stidner\Woocommerce\Gateways\Payson\Lib
 */
class Customer
{
    /** @var string $city */
    public $city;
    /** @var string $countryCode */
    public $countryCode;
    /** @var int $identityNumber Date of birth YYMMDD (digits). */
    public $identityNumber;
    /** @var string $email */
    public $email;
    /** @var string $firstName */
    public $firstName;
    /** @var string $lastName */
    public $lastName;
    /** @var string $phone Phone number. */
    public $phone;
    /** @var string $postalCode Postal code. */
    public $postalCode;
    /** @var string $street Street address. */
    public $street;
    /** @var string $type Type of customer ("business", "person" (default)). */
    public $type;

    /**
     * Customer constructor.
     *
     * @param null $firstName
     * @param null $lastName
     * @param null $email
     * @param null $phone
     * @param null $identityNumber
     * @param null $city
     * @param null $countryCode
     * @param null $postalCode
     * @param null $street
     * @param string $type
     */
    public function __construct(
        $firstName = null,
        $lastName = null,
        $email = null,
        $phone = null,
        $identityNumber = null,
        $city = null,
        $countryCode = null,
        $postalCode = null,
        $street = null,
        $type = 'person'
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone = $phone;
        $this->identityNumber = $identityNumber;
        $this->city = $city;
        $this->countryCode = $countryCode;
        $this->postalCode = $postalCode;
        $this->street = $street;
        $this->type = $type;
    }

    /**
     * @param $data
     *
     * @return Customer
     */
    public static function create($data)
    {
        return new Customer($data->firstName, $data->lastName, $data->email, $data->phone, $data->identityNumber,
            $data->city, $data->countryCode, $data->postalCode, $data->street, $data->type);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}