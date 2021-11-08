<?php

namespace WebmarketerPluginCore\Services;

use Exception;
use WebmarketerPluginCore\Mapping\CompositeMapping;
use WebmarketerPluginCore\Mapping\CompositeMappingComponent;
use WebmarketerPluginCore\Mapping\ConstantBasedMapping;
use WebmarketerPluginCore\Mapping\FieldBasedMapping;
use WebmarketerPluginCore\Mapping\MappedField;
use WebmarketerPluginCore\Mapping\MappingConfiguration;

/**
 * This class parse JSON mapping payload to map them to PHP objects.
 *
 * This allows to store mapping data as json instead of PHP serialized object.
 * This storage format make data migration easier and avoid unserialize error on plugin updates.
 */
class MappingJsonParser
{
    /**
     * @param $mapping_configurations_json
     * @return MappingConfiguration[]
     * @throws Exception
     */
    public static function parse($mapping_configurations_json)
    {
        if (is_array($mapping_configurations_json)) {
            $mapping_configurations = $mapping_configurations_json;
        } else {
            $mapping_configurations = json_decode($mapping_configurations_json);
            if ($mapping_configurations == null) {
                $mapping_configurations = json_decode(self::stripslashesDeep($mapping_configurations_json));
            }
        }
        if ($mapping_configurations === null) {
            throw new Exception("Unable to parse mapping configurations");
        }

        return array_map(function ($mapping_configuration_std) {
            return self::parseMappingConfiguration($mapping_configuration_std);
        }, $mapping_configurations);
    }

    private static function parseMappingConfiguration($mapping_configuration_std)
    {
        $fields_mapping = array_map(function ($mapped_field_std) {
            return self::parseMappedField($mapped_field_std);
        }, $mapping_configuration_std->fieldsMapping);

        return new MappingConfiguration(
            $mapping_configuration_std->mappableIdentifier,
            $mapping_configuration_std->active,
            $mapping_configuration_std->eventType,
            $fields_mapping
        );
    }

    private static function parseMappedField($mapped_field_std)
    {
        return new MappedField(
            $mapped_field_std->active,
            $mapped_field_std->webmarketerKey,
            self::parseAbstractFieldMapping($mapped_field_std->mapped)
        );
    }

    private static function parseAbstractFieldMapping($payload)
    {
        if ($payload === null) {
            return null;
        } else if ($payload->type === 'field') {
            return new FieldBasedMapping($payload->key);
        } else if ($payload->type === 'constant') {
            return new ConstantBasedMapping($payload->constantValue);
        } else {
            return new CompositeMapping(
                array_map(function ($component_std) {
                    return new CompositeMappingComponent(
                        $component_std->compositeKey,
                        self::parseAbstractFieldMapping($component_std->mapped)
                    );
                }, $payload->components)
            );

        }
    }

    private static function stripslashesDeep($value)
    {
        if (is_array($value)) {
            foreach ($value as $index => $item) {
                $value[$index] = self::stripslashesDeep($item);
            }
        } elseif (is_object($value)) {
            $object_vars = get_object_vars($value);
            foreach ($object_vars as $property_name => $property_value) {
                $value->$property_name = self::stripslashesDeep($property_value);
            }
        } else {
            $value = is_string($value) ? stripslashes($value) : $value;
        }

        return $value;
    }
}