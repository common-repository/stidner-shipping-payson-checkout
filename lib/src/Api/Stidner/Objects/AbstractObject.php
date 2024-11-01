<?php

namespace Stidner\Api\Stidner\Objects;

use JsonSerializable;

/**
 * Class AbstractObject
 * @package Stidner\Api\Stidner\Objects
 */
abstract class AbstractObject implements JsonSerializable
{


    /**
     * @param null $flags
     *
     * @return mixed|string|void
     */
    public function toJson($flags = null)
    {
        return json_encode(array_filter($this->jsonSerialize()), $flags);
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return (get_object_vars($this));
    }

    public function toObject($data)
    {
        foreach ($data as $key => $value) {
            if (is_string($key)) {
                $this->{$key} = $value;
            }
        }
    }


}