<?php

namespace WebmarketerPluginCore\Mappable;

use JsonSerializable;

class MappableElement implements JsonSerializable
{
    /** @var string */
    private $mappable_identifier;

    /** @var string */
    private $label;

    /** @var FieldConfig[] */
    private $available_fields;

    /** @var string */
    private $edit_url;

    /**
     * @param $mappable_identifier string
     * @param $label string
     * @param $available_fields FieldConfig[]
     * @param $edit_url string
     */
    public function __construct($mappable_identifier, $label, $available_fields, $edit_url = null)
    {
        $this->mappable_identifier = $mappable_identifier;
        $this->label = $label;
        $this->available_fields = $available_fields;
        $this->edit_url = $edit_url;
    }

    /**
     * @return string
     */
    public function getMappableIdentifier()
    {
        return $this->mappable_identifier;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return FieldConfig[]
     */
    public function getAvailableFields()
    {
        return $this->available_fields;
    }

    /**
     * @return FieldConfig|null
     */
    public function getFieldByKey($key)
    {
        $found = array_filter($this->available_fields, function ($field) use ($key) {
            return $field->getKey() == $key;
        });
        if (count($found) > 0) {
            return array_values($found)[0];
        }
        return null;
    }

    /**
     * @return string
     */
    public function getEditUrl()
    {
        return $this->edit_url;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @param FieldConfig[] $available_fields
     */
    public function setAvailableFields($available_fields)
    {
        $this->available_fields = $available_fields;
    }

    /**
     * @param string $edit_url
     */
    public function setEditUrl($edit_url)
    {
        $this->edit_url = $edit_url;
    }

    public function jsonSerialize()
    {
        return [
            'mappableIdentifier' => $this->mappable_identifier,
            'label' => $this->label,
            'availableFields' => $this->available_fields,
            'editUrl' => $this->edit_url,
        ];
    }
}