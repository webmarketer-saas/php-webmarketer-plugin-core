<?php

namespace WebmarketerPluginCore\Services;

use Webmarketer\WebmarketerSdk;

class EventType
{
    /**
     * @param WebmarketerSdk $sdk
     * @return array
     */
    public static function getFlattenedEventTypes($sdk)
    {
        $event_type_service_pages = $sdk->getEventTypeService()
            ->getAll();

        $all_fields = $sdk->getFieldService()
            ->getAll(['limit' => 0]);

        $event_types = [];
        foreach ($event_type_service_pages->paginationIterator() as $event_type) {
            $event_types[] = [
                'id' => $event_type->_id,
                'name' => $event_type->name,
                'reference' => $event_type->reference,
                'fields' => self::treatEventTypeFields($all_fields, $event_type->fields)
            ];
        }
        return $event_types;
    }

    /**
     * This method build a flat field list by merging project fields with event type fields data
     *
     * @param $project_fields
     * @param $event_type_fields
     * @return array
     */
    private static function treatEventTypeFields($project_fields, $event_type_fields)
    {
        return array_map(function ($field) use ($project_fields) {
            $found = array_values(array_filter($project_fields, function ($project_field) use ($field) {
                return $project_field->key == $field->storageKey;
            }));

            if ($found && count($found) > 0) {
                $field->label = $found[0]->label;
            }

            return $field;
        }, $event_type_fields);
    }
}
