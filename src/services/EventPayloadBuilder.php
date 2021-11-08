<?php

namespace WebmarketerPluginCore\Services;

use Exception;
use Webmarketer\WebmarketerSdk;
use WebmarketerPluginCore\Mappable\MappableElement;
use WebmarketerPluginCore\Mapping\AbstractFieldMapping;
use WebmarketerPluginCore\Mapping\MappedField;
use WebmarketerPluginCore\Mapping\MappingConfiguration;

/**
 * This class builds the event payload according to passed parameters
 */
class EventPayloadBuilder
{
    /**
     * @param $sdk WebmarketerSdk
     * @param $mappable MappableElement
     * @param $mapping_configuration MappingConfiguration
     * @return array
     * @throws Exception
     */
    public static function buildPayload($sdk, $mappable, $mapping_configuration, $context)
    {
        // Fetch Webmarketer remote data
        $webmarketer_event_type = $sdk->getEventTypeService()->getByReference(
            $mapping_configuration->getEventType()
        );
        $webmarketer_fields = [];
        foreach ($webmarketer_event_type->fields as $field) {
            $webmarketer_fields[$field->ingestionKey] = $field;
        }

        // Build output
        $output = [];
        foreach ($mapping_configuration->getFieldsMapping() as $field_mapping) {

            // Expected type
            $destination_field = $webmarketer_fields[$field_mapping->getWebmarketerKey()];


            $mapped = $field_mapping->getMapped();

            $formatted_value = null;

            // If the field is not mapped
            // TODO: maybe we can move build implementations in mapped (this way each class extending AbstractFieldMapping hold building logic) ?
            if ($mapped === null || $field_mapping->isActive() === false) {
                continue;
            } else if ($mapped->getType() === AbstractFieldMapping::FIELD_BASED_MAPPING) {
                $formatted_value = self::buildFieldBasedPayload($mappable, $field_mapping, $context, $destination_field->type);
            } else if ($mapped->getType() === AbstractFieldMapping::CONSTANT_BASED_MAPPING) {
                $formatted_value = self::buildConstantBasedPayload($field_mapping, $destination_field->type);
            } else if ($mapped->getType() === AbstractFieldMapping::COMPOSITE_BASED_MAPPING) {
                $formatted_value = self::buildCompositeBasedPayload($mappable, $field_mapping, $context, $destination_field->type);
            }

            $output[$field_mapping->getWebmarketerKey()] = $formatted_value;
        }

        return $output;
    }

    /**
     * @param $mappable MappableElement
     * @param $field_mapping MappedField
     * @param $context
     * @param $destination_type string
     * @return bool|float|int|mixed|string
     */
    public static function buildFieldBasedPayload($mappable, $field_mapping, $context, $destination_type)
    {
        $field = $mappable->getFieldByKey($field_mapping->getMapped()->getFieldKey());
        $value = $field->getExtractor()->extract($context);
        return self::ensureType($value, $destination_type);
    }

    /**
     * @param $field_mapping MappedField
     * @param $destination_type string
     * @return bool|float|int|mixed|string
     */
    public static function buildConstantBasedPayload($field_mapping, $destination_type)
    {
        return self::ensureType($field_mapping->getMapped()->getConstantValue(), $destination_type);
    }


    /**
     * @param $mappable MappableElement
     * @param $field_mapping MappedField
     * @param $context
     * @param $destination_type string
     * @return array
     */
    public static function buildCompositeBasedPayload($mappable, $field_mapping, $context, $destination_type)
    {
        $components = [];
        foreach ($field_mapping->getMapped()->getComponents() as $component) {
            $value = null;
            if ($component->getMapped() === null) {
                continue;
            } else if ($component->getMapped()->getType() === AbstractFieldMapping::FIELD_BASED_MAPPING) {
                $field = $mappable->getFieldByKey($component->getMapped()->getFieldKey());
                $value = $field->getExtractor()->extract($context);
            } else if ($component->getMapped()->getType() === AbstractFieldMapping::CONSTANT_BASED_MAPPING) {
                $value = $component->getMapped()->getConstantValue();
            }

            $components[$component->getCompositeKey()] = self::ensureType($value, $destination_type, $component->getCompositeKey());
        }

        return $components;
    }

    /**
     * This method transtype input values to Webmarketer expected type
     * @param $value
     * @param $type
     * @param null $subtype
     * @return bool|float|int|mixed|string
     */
    // TODO: move in utils dedicated class ? (in future test each Wmkt expected type correctly transtyped)
    public static function ensureType($value, $type, $subtype = null)
    {
        if ($type == 'string') {
            if (is_int($value) || is_float($value)) {
                return number_format($value, 2, '.', '');
            }
            if (is_array($value)) {
                return 'array_send_as_string';
            }
            if (is_object($value)) {
                return 'object_send_as_string';
            }
            return $value;
        }

        if ($type == 'number') {
            if (is_int($value) || is_float($value)) {
                return $value;
            }
            if (is_string($value)) {
                return floatval($value);
            }
            if (is_bool($value)) {
                return $value ? 1 : 0;
            }
            return $value;
        }

        if ($type === 'boolean') {
            if ($value === 'true' || $value === 1) {
                return true;
            }
            if ($value === 'false' || $value === 0) {
                return false;
            }
            // Else use falsable/trueable property
            return (boolean)$value;
        }

        if ($type == 'phone') {
            return self::ensureType($value, 'string');
        }


        // Composite keys
        if ($type == 'currency' && $subtype === 'amount') {
            return self::ensureType($value, 'number');
        }
        if ($type == 'currency' && $subtype === 'currency') {
            return self::ensureType($value, 'string');
        }

        return $value;
    }

}