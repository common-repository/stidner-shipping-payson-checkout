<?php

namespace Stidner\Api\Stidner\Objects;


/**
 * Class Order
 *
 * @package Stidner\Api\Stidner\Objects
 */
class Order extends AbstractObject
{
    /**
     * @const string
     */
    const STATUS_CREATED = 'pending';

    /**
     * @const string
     */
    const STATUS_COMPLETED = 'completed';

    /**
     * @const string
     */
    const SHIPPING_STATUS_SHIPPED = 'shipped';

    /**
     * @const string
     */
    const SHIPPING_STATUS_NOT_SHIPPED = 'not_shipped';

    /**
     * @const string
     */
    const SHIPPING_STATUS_PARTIALLY_SHIPPED = 'partially_shipped';

    /**
     * @const string
     */
    const SOURCE_WIDGET = 'widget';

    /**
     * @const string
     */
    const SOURCE_WOOCOMMERCE = 'woocommerce';

    /**
     * @const string
     */
    const SOURCE_TWO_WOOCOMMERCE = '__stidner_shipping_widget';

    /**
     * @const string
     */
    const SOURCE_API = 'api';

    /**
     * @var Item[]
     */
    public $items;

    /**
     * @var Address[]
     */
    public $addresses;

    /**
     * @var boolean
     */
    protected $find_points;

    /**
     * @var boolean
     */
    protected $info_complete;

    /**
     * @var string
     */
    protected $order_status;

    /**
     * @var string|null
     */
    protected $payment_reference;

    /**
     * @var string
     */
    protected $payment_system;

    /**
     * @var string
     */
    protected $shipping_status;

    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var string
     */
    protected $source;

    /**
     * @var string
     */
    protected $source_two;

    /**
     * @var string|null
     */
    protected $external_reference;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var string
     */
    protected $integration_platform;

    /**
     * @var OrderProducts[]
     */
    protected $order_products;

    /**
     * @var string|null
     */
    protected $product_uuid;

    /**
     * @var string|null
     */
    protected $product_handle;

    /**
     * @var string|null
     */
    protected $point_handle;

    /**
     * @var string|null
     */
    protected $product_type_handle;

    /**
     * @var string|null
     */
    protected $notification_url;

    /**
     * @var
     */
    protected $created_at;

    /**
     * @var
     */
    protected $completed_at;

    /**
     * @var Package[]
     */
    protected $packages;

    /**
     * @var string
     */
    protected $widget_url;

    /**
     * @var string
     */
    protected $widget_embed;

    /**
     * @var string
     */
    protected $internal_reference;

    /**
     * @var integer
     */
    protected $shipping_price;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var boolean
     */
    protected $disallow_option_change;

    /**
     * @var boolean
     */
    protected $disallow_address_change;

    /**
     * Order constructor.
     *
     * @param null $std
     */
    public function __construct($std = null)
    {
        if (!is_null($std)) {
            return $this->toOrder($std);
        }

        return $this;
    }

    /**
     * @param $stdObject
     *
     * @return $this
     */
    public function toOrder($stdObject)
    {
        $arrays = [
            'items'     => Item::class,
            'addresses' => Address::class,
            'order_products'   => OrderProducts::class,
            'packages'  => Package::class,
        ];

        //todo add package objects

        $this->toObject($stdObject);
        $this->setItems([]);
        $this->setAddresses([]);
        $this->setOrderProducts([]);
        $this->setPackages([]);

        foreach ($arrays as $name => $model) {
            foreach ((array)$stdObject->$name as $std) {
                $obj = new $model;
                $obj->toObject($std);
                array_push($this->$name, $obj);
            }
        }

        return $this;
    }

    /**
     * @return boolean
     */
    public function isFindPoints()
    {
        return $this->find_points;
    }

    /**
     * @param boolean $find_points
     *
     * @return Order
     */
    public function setFindPoints($find_points)
    {
        $this->find_points = $find_points;

        return $this;
    }

    /**
     * @return string
     */
    public function getSourceTwo()
    {
        return $this->source_two;
    }

    /**
     * @param string $source_two
     *
     * @return Order
     */
    public function setSourceTwo($source_two)
    {
        $this->source_two = $source_two;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isDisallowOptionChange()
    {
        return $this->disallow_option_change;
    }

    /**
     * @param boolean $disallow_option_change
     *
     * @return Order
     */
    public function setDisallowOptionChange($disallow_option_change)
    {
        $this->disallow_option_change = $disallow_option_change;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isDisallowAddressChange()
    {
        return $this->disallow_address_change;
    }

    /**
     * @param boolean $disallow_address_change
     *
     * @return Order
     */
    public function setDisallowAddressChange($disallow_address_change)
    {
        $this->disallow_address_change = $disallow_address_change;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param mixed $locale
     *
     * @return Order
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getWidgetUrl()
    {
        return $this->widget_url;
    }

    /**
     * @param mixed $widget_url
     *
     * @return Order
     */
    public function setWidgetUrl($widget_url)
    {
        $this->widget_url = $widget_url;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getWidgetEmbed()
    {
        return $this->widget_embed;
    }

    /**
     * @param mixed $widget_embed
     *
     * @return Order
     */
    public function setWidgetEmbed($widget_embed)
    {
        $this->widget_embed = $widget_embed;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getInternalReference()
    {
        return $this->internal_reference;
    }

    /**
     * @param mixed $internal_reference
     *
     * @return Order
     */
    public function setInternalReference($internal_reference)
    {
        $this->internal_reference = $internal_reference;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIntegrationPlatform()
    {
        return $this->integration_platform;
    }

    /**
     * @param mixed $integration_platform
     *
     * @return Order
     */
    public function setIntegrationPlatform($integration_platform)
    {
        $this->integration_platform = $integration_platform;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getShippingPrice()
    {
        return $this->shipping_price;
    }

    /**
     * @param mixed $shipping_price
     *
     * @return Order
     */
    public function setShippingPrice($shipping_price)
    {
        $this->shipping_price = $shipping_price;

        return $this;
    }

    /**
     * @param Item $item
     *
     * @return $this
     */
    public function addItem(Item $item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @param Address $address
     *
     * @return $this
     */
    public function addAddresses(Address $address)
    {
        $this->addresses[] = $address;

        return $this;
    }

    /**
     * @param OrderProducts $option
     *
     * @return $this
     */
    public function addOption(OrderProducts $order_products)
    {
        $this->order_products[] = $order_products;

        return $this;
    }

    /**
     * @return Package[]
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * @param Package[] $packages
     *
     * @return Order
     */
    public function setPackages($packages)
    {
        $this->packages = $packages;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getNotificationUrl()
    {
        return $this->notification_url;
    }

    /**
     * @param null|string $notification_url
     *
     * @return Order
     */
    public function setNotificationUrl($notification_url)
    {
        $this->notification_url = $notification_url;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentReference()
    {
        return $this->payment_reference;
    }

    /**
     * @param string|null $payment_reference
     *
     * @return Order
     */
    public function setPaymentReference($payment_reference)
    {
        $this->payment_reference = $payment_reference;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentSystem()
    {
        return $this->payment_system;
    }

    /**
     * @param string $payment_system
     *
     * @return Order
     */
    public function setPaymentSystem($payment_system)
    {
        $this->payment_system = $payment_system;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isInfoComplete()
    {
        return $this->info_complete;
    }

    /**
     * @param boolean $info_complete
     *
     * @return Order
     */
    public function setInfoComplete($info_complete)
    {
        $this->info_complete = $info_complete;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrderStatus()
    {
        return $this->order_status;
    }

    /**
     * @param string $order_status
     *
     * @return Order
     */
    public function setOrderStatus($order_status)
    {
        $this->order_status = $order_status;

        return $this;
    }

    /**
     * @return string
     */
    public function getShippingStatus()
    {
        return $this->shipping_status;
    }

    /**
     * @param string $shipping_status
     *
     * @return Order
     */
    public function setShippingStatus($shipping_status)
    {
        $this->shipping_status = $shipping_status;

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
     * @return Order
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     *
     * @return Order
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getExternalReference()
    {
        return $this->external_reference;
    }

    /**
     * @param null|string $external_reference
     *
     * @return Order
     */
    public function setExternalReference($external_reference)
    {
        $this->external_reference = (string)$external_reference;

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
     * @return Order
     */
    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return Order
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getProductHandle()
    {
        return $this->product_handle;
    }

    /**
     * @param null|string $product_handle
     *
     * @return Order
     */
    public function setProductHandle($product_handle)
    {
        $this->product_handle = $product_handle;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getProductTypeHandle()
    {
        return $this->product_type_handle;
    }

    /**
     * @param null|string $option_type_handle
     *
     * @return Order
     */
    public function setProductTypeHandle($product_type_handle)
    {
        $this->product_type_handle = $product_type_handle;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param mixed $created_at
     *
     * @return Order
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCompletedAt()
    {
        return $this->completed_at;
    }

    /**
     * @param mixed $completed_at
     *
     * @return Order
     */
    public function setCompletedAt($completed_at)
    {
        $this->completed_at = $completed_at;

        return $this;
    }

    /**
     * @param $type
     *
     * @return Address
     */
    public function getAddressByType($type)
    {
        foreach ($this->getAddresses() as $address) {
            if ($address->type == $type) {
                return $address;
            }
        }

        return new Address;
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
     * @return Order
     */
    public function setAddresses($addresses)
    {
        $this->addresses = $addresses;

        return $this;
    }

    /**
     * @return ServicePoint
     */
    public function getSelectedServicePoint()
    {
        foreach ($this->getSelectedShippingOption()->getServicePoints() as $service_point) {
            if ($service_point->getHandle() == $this->getPointHandle()) {
                return $service_point;
            }
        }

        return new ServicePoint;
    }

    /**
     * @return OrderProducts
     */
    public function getSelectedShippingOption()
    {
        foreach ($this->getOrderProducts() as $option) {
            if ($this->getProductUuid() == $option->getUuid()) {
                return $option;
            }
        }

        return new OrderProducts;
    }

    /**
     * @return OrderProducts[]
     */
    public function getOrderProducts()
    {
        return $this->order_products;
    }

    /**
     * @param OrderProducts[] $order_products
     *
     * @return Order
     */
    public function setOrderProducts($order_products)
    {
        $this->order_products = $order_products;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getProductUuid()
    {
        return $this->product_uuid;
    }

    /**
     * @param null|string $product_uuid
     *
     * @return Order
     */
    public function setProductUuid($product_uuid)
    {
        $this->product_uuid = $product_uuid;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPointHandle()
    {
        return $this->point_handle;
    }

    /**
     * @param null|string $point_handle
     *
     * @return Order
     */
    public function setPointHandle($point_handle)
    {
        $this->point_handle = $point_handle;

        return $this;
    }
}