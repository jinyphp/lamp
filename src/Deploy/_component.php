<?php

namespace Jiny\Lamp\Deploy;

abstract class Component
{
    public $chmod;
    public $type;
    public $num;
    public $owner;
    public $group;
    public $size;
    public $month;
    public $day;
    public $time;
    public $name;

    public function getTime()
    {
        return $this->time;
    }

    public function setTime($time)
    {
        $this->time = $time;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }




}