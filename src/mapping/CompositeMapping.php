<?php

namespace WebmarketerPluginCore\Mapping;

use JsonSerializable;

class CompositeMapping extends AbstractFieldMapping implements JsonSerializable
{
    /** @var CompositeMappingComponent[] */
    private $components;

    /**
     * @param $components CompositeMappingComponent[]
     */
    public function __construct($components)
    {
        $this->components = $components;
    }

    /**
     * @return CompositeMappingComponent[]
     */
    public function getComponents()
    {
        return $this->components;
    }

    /**
     * @return string "composite"
     */
    function getType()
    {
        return self::COMPOSITE_BASED_MAPPING;
    }

    public function jsonSerialize()
    {
        return [
            'type' => 'composite',
            'components' => $this->components
        ];
    }
}
