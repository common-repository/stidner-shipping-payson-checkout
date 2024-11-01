<?php

namespace Stidner\Api\Stidner\Objects;

/**
 * Class Item
 * @package Stidner\Api\Stidner\Objects
 */
class Item extends AbstractObject
{
    /**
     * @var string
     */
    protected $uuid;
    /**
     * @var string|null
     */
    protected $article_number;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string|null
     */
    protected $description;
    /**
     * @var integer
     */
    protected $quantity;
    /**
     * @var integer
     */
    protected $unit_price;
    /**
     * @var integer
     */
    protected $weight;
    /**
     * @var integer|null
     */
    protected $height;
    /**
     * @var integer|null
     */
    protected $length;
    /**
     * @var integer|null
     */
    protected $width;
    /**
     * @var string|null
     */
    protected $warehouse_location;
    /**
     * @var boolean|null
     */
    protected $in_stock;

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
     * @return Item
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getArticleNumber()
    {
        return $this->article_number;
    }

    /**
     * @param null|string $article_number
     *
     * @return Item
     */
    public function setArticleNumber($article_number)
    {
        $this->article_number = $article_number;

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
     * @return Item
     */
    public function setName($name)
    {

        if ($name == null) {
            $name = '';
        }

        $this->name = $name;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     *
     * @return Item
     */
    public function setDescription($description)
    {
        if ($description == null) {
            $description = '';
        }

        $this->description = $description;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     *
     * @return Item
     */
    public function setQuantity($quantity)
    {
        if ($quantity == 0 or $quantity == null) {
            $quantity = 1;
        }

        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return int
     */
    public function getUnitPrice()
    {
        return $this->unit_price;
    }

    /**
     * @param int $unit_price
     *
     * @return Item
     */
    public function setUnitPrice($unit_price)
    {
        $this->unit_price = $unit_price;

        return $this;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     *
     * @return Item
     */
    public function setWeight($weight)
    {

        if ($weight == null) {
            $weight = 0;
        }

        $this->weight = $weight;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int|null $height
     *
     * @return Item
     */
    public function setHeight($height)
    {
        if ($height == null) {
            $height = 0;
        }

        $this->height = $height;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param int|null $length
     *
     * @return Item
     */
    public function setLength($length)
    {
        if ($length == null) {
            $length = 0;
        }

        $this->length = $length;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int|null $width
     *
     * @return Item
     */
    public function setWidth($width)
    {
        if ($width == null) {
            $width = 0;
        }
        $this->width = $width;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getWarehouseLocation()
    {
        return $this->warehouse_location;
    }

    /**
     * @param null|string $warehouse_location
     *
     * @return Item
     */
    public function setWarehouseLocation($warehouse_location)
    {
        $this->warehouse_location = $warehouse_location;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getInStock()
    {
        return $this->in_stock;
    }

    /**
     * @param bool|null $in_stock
     *
     * @return Item
     */
    public function setInStock($in_stock)
    {
        if ($in_stock == null) {
            $in_stock = 1;
        }
        $this->in_stock = $in_stock;

        return $this;
    }
}