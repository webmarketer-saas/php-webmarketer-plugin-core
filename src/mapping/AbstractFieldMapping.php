<?php

namespace WebmarketerPluginCore\Mapping;

abstract class AbstractFieldMapping
{
    const FIELD_BASED_MAPPING = 'field';
    const CONSTANT_BASED_MAPPING = 'constant';
    const COMPOSITE_BASED_MAPPING = 'composite';

    abstract function getType();
}