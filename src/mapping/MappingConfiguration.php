<?php

namespace WebmarketerPluginCore\Mapping;

class MappingConfiguration implements \JsonSerializable
{
    /** @var string */
    private $mappable_identifier;
    /** @var boolean */
    private $active;
    /** @var string */
    private $event_type;

    /** @var MappedField[] */
    private $fields_mapping;

    public function __construct($mappable_identifier, $active, $event_type, $fields_mapping)
    {
        $this->mappable_identifier = $mappable_identifier;
        $this->active = $active;
        $this->event_type = $event_type;
        $this->fields_mapping = $fields_mapping;
    }

    /**
     * @return string
     */
    public function getMappableIdentifier()
    {
        return $this->mappable_identifier;
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
    public function getEventType()
    {
        return $this->event_type;
    }

    /**
     * @return MappedField[]
     */
    public function getFieldsMapping()
    {
        return $this->fields_mapping;
    }


    public function jsonSerialize()
    {
        return [
            'mappableIdentifier' => $this->mappable_identifier,
            'active' => $this->active,
            'eventType' => $this->event_type,
            'fieldsMapping' => $this->fields_mapping,
        ];
    }
}