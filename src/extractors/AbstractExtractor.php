<?php

namespace WebmarketerPluginCore\Extractors;

abstract class AbstractExtractor
{
    public abstract function extract($context);
}