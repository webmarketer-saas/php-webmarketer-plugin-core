<?php

namespace WebmarketerPluginCore\Mapping;

use JsonSerializable;

class ConstantBasedMapping extends AbstractFieldMapping implements JsonSerializable
{
    /** @var string */
    private $constant_value;

    /**
     * @param $constant_value string Value of the constant
     */
    public function __construct($constant_value)
    {
        $this->constant_value = $constant_value;
    }

    /**
     * @return string
     */
    public function getConstantValue()
    {
        return $this->constant_value;
    }

    /**
     * @return string "constant"
     */
    function getType()
    {
        return self::CONSTANT_BASED_MAPPING;
    }

    public function jsonSerialize()
    {
        return [
            'type' => 'constant',
            'constantValue' => $this->constant_value,
        ];
    }
}