<?php

namespace WebmarketerPluginCore\Services;

use Webmarketer\Api\Project\EventTypes\EventType;
use Webmarketer\Api\Project\Fields\Field;
use Webmarketer\WebmarketerSdk;
use WebmarketerPluginCore\Mapping\AbstractFieldMapping;
use WebmarketerPluginCore\Mapping\CompositeMapping;
use WebmarketerPluginCore\Mapping\CompositeMappingComponent;
use WebmarketerPluginCore\Mapping\ConstantBasedMapping;
use WebmarketerPluginCore\Mapping\FieldBasedMapping;
use WebmarketerPluginCore\Mapping\MappedField;
use WebmarketerPluginCore\Services\EventType as WebmarketerPluginCoreEventTypeService;
use WebmarketerPluginCore\Mappable\FieldConfig;
use WebmarketerPluginCore\Mappable\MappableElement;
use WebmarketerPluginCore\Mapping\MappingConfiguration;

/**
 * This class generate a bag of alerts and warning.
 *
 */
class MappingChecker
{
    // Webmarketer type => local type
    const TYPE_INCOMPATIBILITIES = [
        'phone' => [
            FieldConfig::TYPE_BOOLEAN,
            FieldConfig::TYPE_DATE,
            FieldConfig::TYPE_EMAIL,
        ],
        'email' => [
            FieldConfig::TYPE_BOOLEAN,
            FieldConfig::TYPE_DATE,
            FieldConfig::TYPE_NUMBER,
            FieldConfig::TYPE_PHONE,
        ],
        'boolean' => [
        ],
        'date' => [
            FieldConfig::TYPE_BOOLEAN,
            FieldConfig::TYPE_EMAIL,
            FieldConfig::TYPE_PHONE,
            FieldConfig::TYPE_STRING,
        ],
        'number' => [
            FieldConfig::TYPE_BOOLEAN,
            FieldConfig::TYPE_DATE,
            FieldConfig::TYPE_EMAIL,
            FieldConfig::TYPE_PHONE,
        ],
    ];

    // Webmarketer type => local type
    const TYPE_WARNINGS = [
        'phone' => [
            FieldConfig::TYPE_NUMBER,
        ],
        'email' => [
            FieldConfig::TYPE_STRING,
        ],
        'boolean' => [
            FieldConfig::TYPE_BOOLEAN,
            FieldConfig::TYPE_DATE,
            FieldConfig::TYPE_NUMBER,
            FieldConfig::TYPE_STRING,
            FieldConfig::TYPE_PHONE,
            FieldConfig::TYPE_EMAIL,
        ],
        'date' => [
            FieldConfig::TYPE_NUMBER,
            FieldConfig::TYPE_STRING,
        ],
        'number' => [
            FieldConfig::TYPE_STRING,
            FieldConfig::TYPE_NUMBER,
        ],
    ];

    /** @var WebmarketerSdk */
    private $sdk;

    /** @var MappableElement[] */
    private $mappables;

    /** @var MappingConfiguration */
    private $mapping_configurations;

    /** @var EventType */
    private $remote_event_types;

    /** @var array */
    private $bag = [];

    public function __construct($sdk, $mappables, $mapping_configurations)
    {
        $this->sdk = $sdk;
        $this->mappables = $mappables;
        $this->mapping_configurations = $mapping_configurations;
    }

    /**
     * Evaluate the errors and warning for the passed configuration.
     * The results are then available through getResults() method.
     * @return $this
     */
    public function evaluate()
    {
        $this->remote_event_types = WebmarketerPluginCoreEventTypeService::getFlattenedEventTypes($this->sdk);

        // For each mappable item
        foreach ($this->mappables as $mappable) {
            $this->evaluateSingleMappable($mappable);
        }
        return $this;
    }

    public function getResults()
    {
        return $this->bag;
    }

    /**
     * This method evaluate a single mappable element
     *
     * @param MappableElement $mappable
     */
    private function evaluateSingleMappable(MappableElement $mappable)
    {
        /** @var MappingConfiguration $mapping_configuration */
        $mapping_configuration = null;

        /** @var MappingConfiguration $mapping_conf */
        foreach ($this->mapping_configurations as $mapping_conf) {
            if ($mapping_conf->getMappableIdentifier() == $mappable->getMappableIdentifier()) {
                $mapping_configuration = $mapping_conf;
                break;
            }
        }

        // Nothing to do if the form is not enabled
        if (!$mapping_configuration->isActive()) {
            return;
        }

        if ($mapping_configuration->getEventType() === null) {
            $this->bag[] = self::error('no_event_type_to_form', [
                'mappableIdentifier' => $mapping_configuration->getMappableIdentifier(),
            ]);
            return;
        }


        // Get remote event type configuration
        $event_type = null;
        foreach ($this->remote_event_types as $remote_event_type) {
            if ($remote_event_type['reference'] === $mapping_configuration->getEventType()) {
                $event_type = (object)$remote_event_type;
                break;
            }
        }

        // Check that all the mappings are defined
        /** @var MappedField $field_mapping */
        foreach ($mapping_configuration->getFieldsMapping() as $field_mapping) {
            if ($field_mapping->isActive() && $field_mapping->getMapped() == null) {
                $this->bag[] = self::error('no_mapped_defined_for_enabled_field', [
                    'mappableIdentifier' => $mappable->getMappableIdentifier(),
                    'webmarketerKey' => $field_mapping->getWebmarketerKey()
                ]);
            }
        }

        // Check that the event type mandatory fields are correctly mapped (+ type check)
        foreach ($event_type->fields as $event_type_field) {
            if ($event_type_field->optional) {
                continue;
            }

            /** @var MappedField[] $field_mapping_array */
            $field_mapping_array = array_filter($mapping_configuration->getFieldsMapping(), function ($f) use ($event_type_field) {
                /** @var $f MappedField */
                return $f->getWebmarketerKey() == $event_type_field->ingestionKey;
            });

            if (count($field_mapping_array) == 0) {
                $this->bag[] = self::error('no_field_mapped_to_mandatory_field', [
                    'mappableIdentifier' => $mappable->getMappableIdentifier(),
                    'webmarketerKey' => $event_type_field->ingestionKey
                ]);
                continue;
            }


            /** @var MappedField $field_mapping */
            $field_mapping = array_values($field_mapping_array)[0];

            if (!$field_mapping->isActive()) {
                $this->bag[] = self::error('no_field_mapped_to_mandatory_field', [
                    'mappableIdentifier' => $mappable->getMappableIdentifier(),
                    'webmarketerKey' => $event_type_field->ingestionKey
                ]);
                continue;
            }
            if ($field_mapping->getMapped() == null) {
                $this->bag[] = self::error('no_field_mapped_to_mandatory_field', [
                    'mappableIdentifier' => $mappable->getMappableIdentifier(),
                    'webmarketerKey' => $event_type_field->ingestionKey
                ]);
                continue;
            }

            switch ($field_mapping->getMapped()->getType()) {
                case AbstractFieldMapping::FIELD_BASED_MAPPING:
                    $this->checkFieldBasedMapping($mappable, $field_mapping, $event_type_field);
                    break;
                case AbstractFieldMapping::CONSTANT_BASED_MAPPING:
                    $this->checkConstantBasedMapping($mappable, $field_mapping, $event_type_field);
                    break;
                case AbstractFieldMapping::COMPOSITE_BASED_MAPPING:
                    $this->checkCompositeMapping($mappable, $field_mapping, $event_type_field);
                    break;
            }
        }
    }


    /**
     * This method check a FieldBasedMapping
     *
     * Checks :
     * - Ensure that the mappable field is still available (can happen with editables forms, ie CF7)
     * - Ensure that the types are corrects (TO BE FIXED)
     * @param $mappable MappableElement
     * @param $field_mapping MappedField
     * @param $event_type_field Field
     */
    private function checkFieldBasedMapping($mappable, $field_mapping, $event_type_field)
    {
        $available_field_array = array_filter($mappable->getAvailableFields(), function ($field) use ($field_mapping) {
            /** @var FieldConfig $field */
            /** @var FieldBasedMapping $mapped */
            $mapped = $field_mapping->getMapped();
            return $field->getKey() == $mapped->getFieldKey();
        });

        // Check if the field has been deleted
        if (count($available_field_array) == 0) {
            /** @var FieldBasedMapping $mapped */
            $mapped = $field_mapping->getMapped();

            $this->bag[] = self::error('deleted_field_mapped_to_mandatory_field', [
                'mappableIdentifier' => $mappable->getMappableIdentifier(),
                'webmarketerKey' => $event_type_field->ingestionKey,
                'key' => $mapped->getFieldKey(),
            ]);
            return;
        }

        /** @var FieldConfig $available_field */
        $available_field = array_values($available_field_array)[0];


        // Check if mutable field and not statistic or state field
        if ($available_field->isMutable() && !in_array($event_type_field->entity, ['state', 'statistic'])) {
            /** @var FieldBasedMapping $mapped */
            $mapped = $field_mapping->getMapped();
            $this->bag[] = self::warning('mutable_field_mapped_to_fixed_field', [
                'mappableIdentifier' => $mappable->getMappableIdentifier(),
                'webmarketerKey' => $event_type_field->ingestionKey,
                'key' => $mapped->getFieldKey(),
            ]);
        }

        // TODO : FIXME
        /*
                // Check type incompatibilities
                if (key_exists($event_type_field->type, self::TYPE_INCOMPATIBILITIES)) {
                    $incompatible_types = self::TYPE_INCOMPATIBILITIES[$event_type_field->type];
                    // TODO : Canonize types cause there we get them from another type set (CF 7 referential)
                    if (in_array($cf7_field->basetype, $incompatible_types)) {
                        $bag[] = self::warning('incompatible_fields_types', [
                            'mappableIdentifier' => $mappable->mappableIdentifier,
                            'webmarketerKey' => $event_type_field->key,
                            'webmarketer_type' => $event_type_field->type,
                            'key' => $field_mapping->mapped->key,
                            'type' => $cf7_field->basetype,
                        ]);
                    }
                }

                // Check if the cf7 mapped field is optional
                $optional = strpos($cf7_field->type, '*') === false;

                if (!$event_type_field->optional && $optional) {
                    $bag[] = self::warning('mandatory_field_mapped_to_optional', [
                        'mappableIdentifier' => $mappable->mappableIdentifier,
                        'webmarketerKey' => $event_type_field->key,
                        'key' => $field_mapping->mapped->key,
                    ]);
                }
        */
    }


    /**
     * This method check a ConstantBaseMapping.
     *
     * Checks:
     * - Ensure that the constant value is set (not empty)
     * @param $mappable MappableElement
     * @param $field_mapping MappedField
     * @param $event_type_field
     */
    private function checkConstantBasedMapping($mappable, $field_mapping, $event_type_field)
    {
        /** @var ConstantBasedMapping $mapped */
        $mapped = $field_mapping->getMapped();
        if (empty($mapped->getConstantValue())) {
            $this->bag[] = self::error('empty_constant_mapped_to_mandatory_field', [
                'mappableIdentifier' => $mappable->getMappableIdentifier(),
                'webmarketerKey' => $event_type_field->ingestionKey,
            ]);
        }
    }

    /**
     * This method check the inner component of a composite mapping.
     *
     * Checks :
     * - Ensure that the mandatory components are correctly defined (mapped to field or not empty constant)
     * - Warn if some recommended components are not correctly defined (mapped to field or not empty constant)
     *
     * @param $mappable MappableElement
     * @param $field_mapping MappedField
     * @param $event_type_field
     */
    private function checkCompositeMapping($mappable, $field_mapping, $event_type_field)
    {
        $rules = [
            'currency' => [
                [
                    'level' => 'error',
                    'message' => 'no_field_mapped_to_mandatory_field',
                    'missing_keys' => ['amount', 'currency']
                ]
            ],
            'location' => [
                [
                    'level' => 'warning',
                    'message' => 'recommended_field_is_not_mapped',
                    'missing_keys' => ['zipcode', 'country']
                ]
            ]
        ];

        $field_type_rules = $rules[$event_type_field->type];

        // For each rule for this field type
        foreach ($field_type_rules as $type_rule) {


            foreach ($type_rule['missing_keys'] as $evaluate_key) {
                /** @var CompositeMapping $mapped */
                $mapped = $field_mapping->getMapped();

                $component_array = array_filter($mapped->getComponents(), function ($c) use ($evaluate_key) {
                    return $c->getCompositeKey() === $evaluate_key;
                });
                $level = $type_rule['level'];
                $message = $type_rule['message'];


                if (count($component_array) == 0) {
                    $this->bag[] = self::$level($message, [
                        'mappableIdentifier' => $mappable->getMappableIdentifier(),
                        'webmarketerKey' => $event_type_field->ingestionKey . ':' . $evaluate_key
                    ]);
                    continue;
                }


                /** @var CompositeMappingComponent $component */
                $component = array_values($component_array)[0];

                if ($component->getMapped() == null) {
                    $this->bag[] = self::$level($message, [
                        'mappableIdentifier' => $mappable->getMappableIdentifier(),
                        'webmarketerKey' => $event_type_field->ingestionKey . ':' . $evaluate_key
                    ]);
                    continue;
                }

                if ($component->getMapped()->getType() == AbstractFieldMapping::FIELD_BASED_MAPPING && empty($component->getMapped()->getFieldKey())) {
                    $this->bag[] = self::$level($message, [
                        'mappableIdentifier' => $mappable->getMappableIdentifier(),
                        'webmarketerKey' => $event_type_field->ingestionKey . ':' . $evaluate_key
                    ]);
                    continue;
                }
                if ($component->getMapped()->getType() == AbstractFieldMapping::CONSTANT_BASED_MAPPING && empty($component->getMapped()->getConstantValue())) {
                    $this->bag[] = self::$level($message, [
                        'mappableIdentifier' => $mappable->getMappableIdentifier(),
                        'webmarketerKey' => $event_type_field->ingestionKey . ':' . $evaluate_key
                    ]);
                    continue;
                }

            }
        }
    }


    private static function error($message, $options = [])
    {
        return [
                'level' => 'error',
                'message' => $message
            ] + $options;
    }

    private static function warning($message, $options = [])
    {
        return [
                'level' => 'warning',
                'message' => $message
            ] + $options;
    }

}
