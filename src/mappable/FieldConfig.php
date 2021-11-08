<?php

namespace WebmarketerPluginCore\Mappable;

use JsonSerializable;
use WebmarketerPluginCore\Extractors\AbstractExtractor;

class FieldConfig implements JsonSerializable
{
    const TYPE_STRING = 'string';
    const TYPE_PHONE = 'phone';
    const TYPE_EMAIL = 'email';
    const TYPE_NUMBER = 'number';
    const TYPE_DATE = 'date';
    const TYPE_BOOLEAN = 'boolean';

    /** @var string */
    private $key;
    /** @var string */
    private $label;
    /** @var string */
    private $type;
    /** @var bool */
    private $optional;
    /** @var bool */
    private $mutable;
    /** @var string */
    private $source;
    /** @var AbstractExtractor */
    private $extractor;

    /**
     * @param $key string
     * @param $label string
     * @param $type string
     * @param $optional boolean
     * @param $source string
     * @param $extractor AbstractExtractor
     */
    public function __construct($key, $label, $type, $optional, $mutable, $source, $extractor = null)
    {
        $this->key = $key;
        $this->label = $label;
        $this->type = $type;
        $this->optional = $optional;
        $this->mutable = $mutable;
        $this->source = $source;
        $this->extractor = $extractor;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isOptional()
    {
        return $this->optional;
    }


    /**
     * @return bool
     */
    public function isMutable()
    {
        return $this->mutable;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @param bool $optional
     */
    public function setOptional($optional)
    {
        $this->optional = $optional;
    }

    /**
     * @param bool $mutable
     */
    public function setMutable($mutable)
    {
        $this->mutable = $mutable;
    }

    /**
     * @param string $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @param AbstractExtractor $extractor
     */
    public function setExtractor($extractor)
    {
        $this->extractor = $extractor;
    }

    /**
     * @return AbstractExtractor
     */
    public function getExtractor()
    {
        return $this->extractor;
    }

    public function jsonSerialize()
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'type' => $this->type,
            'mutable' => $this->mutable,
            'optional' => $this->optional,
            'source' => $this->source,
        ];
    }
}