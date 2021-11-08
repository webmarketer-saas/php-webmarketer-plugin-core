<?php

namespace WebmarketerPluginCore\Mapping;

use JsonSerializable;

class CompositeMappingComponent implements JsonSerializable
{
    /** @var string */
    private $composite_key;

    /** @var ConstantBasedMapping|FieldBasedMapping */
    private $mapped;

    /**
     * @param $composite_key
     * @param $mapped ConstantBasedMapping|FieldBasedMapping
     */
    public function __construct($composite_key, $mapped)
    {
        $this->composite_key = $composite_key;
        $this->mapped = $mapped;
    }

    /**
     * @return string
     */
    public function getCompositeKey()
    {
        return $this->composite_key;
    }

    /**
     * @return ConstantBasedMapping|FieldBasedMapping
     */
    public function getMapped()
    {
        return $this->mapped;
    }


    public function jsonSerialize()
    {
        return [
            'compositeKey' => $this->composite_key,
            'mapped' => $this->mapped,
        ];
    }
}