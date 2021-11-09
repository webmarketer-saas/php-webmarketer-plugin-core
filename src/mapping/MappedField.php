<?php

namespace WebmarketerPluginCore\Mapping;

use JsonSerializable;

class MappedField implements JsonSerializable
{
    /** @var boolean */
    private $active;

    /** @var string */
    private $webmarketer_key;

    /** @var AbstractFieldMapping */
    private $mapped;

    /**
     * @param $active boolean
     * @param $webmarketer_key string
     * @param $mapped AbstractFieldMapping
     */
    public function __construct($active, $webmarketer_key, $mapped)
    {
        $this->active = $active;
        $this->webmarketer_key = $webmarketer_key;
        $this->mapped = $mapped;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return string
     */
    public function getWebmarketerKey()
    {
        return $this->webmarketer_key;
    }

    /**
     * @return AbstractFieldMapping
     */
    public function getMapped()
    {
        return $this->mapped;
    }


    public function jsonSerialize()
    {
        return [
            'active' => $this->active,
            'webmarketerKey' => $this->webmarketer_key,
            'mapped' => $this->mapped,
        ];
    }
}