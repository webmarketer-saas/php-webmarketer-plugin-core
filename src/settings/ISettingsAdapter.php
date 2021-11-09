<?php

namespace WebmarketerPluginCore\Settings;

interface ISettingsAdapter
{
    /**
     * @return string | null
     */
    public function getServiceAccount();

    /**
     * @param string | null $sa
     * @return void
     */
    public function setServiceAccount($sa);

    /**
     * @return string | null
     */
    public function getProjectId();

    /**
     * @param string | null $project_id
     * @return void
     */
    public function setProjectId($project_id);

    /**
     * @return array
     */
    public function getNdrstndSettings();

    /**
     * @param array $settings
     * @return void
     */
    public function setNdrstndSettings($settings);
}
