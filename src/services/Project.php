<?php

namespace WebmarketerPluginCore\Services;

use Webmarketer\WebmarketerSdk;

class Project
{
    /**
     * @param WebmarketerSdk $sdk
     * @return array
     */
    public static function getAccountProjects($sdk)
    {
        return $sdk->getProjectService()
            ->getAll();
    }
}