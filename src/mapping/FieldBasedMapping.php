<?php

namespace WebmarketerPluginCore\Mapping;

class FieldBasedMapping extends AbstractFieldMapping implements \JsonSerializable
{
    /** @var string */
    private $field_key;

    /**
     * @param $field_key string Key of the mappable field
     */
    public function __construct($field_key)
    {
        $this->field_key = $field_key;
    }

    function getType()
    {
        return self::FIELD_BASED_MAPPING;
    }

    /**
     * @return string
     */
    public function getFieldKey()
    {
        return $this->field_key;
    }


    public function jsonSerialize()
    {
        return [
            'type' => 'field',
            'key' => $this->field_key,
        ];
    }
}