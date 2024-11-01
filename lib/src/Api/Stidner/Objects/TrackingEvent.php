<?php

namespace Stidner\Api\Stidner\Objects;

/**
 * Class TrackingEvent
 * @package Stidner\Api\Stidner\Objects
 */
class TrackingEvent extends AbstractObject
{
    /**
     * @var \datetime
     */
    protected $time;
    /**
     * @var string
     */
    protected $description;

    /**
     * @return \datetime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param \datetime $time
     *
     * @return TrackingEvent
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return TrackingEvent
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
}