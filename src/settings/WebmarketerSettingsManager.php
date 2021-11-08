<?php

namespace WebmarketerPluginCore\Settings;

class WebmarketerSettingsManager
{
    const NDRSTND_DEFAULT_SETTINGS = [
        'ndrstndActive' => true,
        'ndrstndCookieName' => 'ndrstnd',
        'ndrstndTrackingDomain' => 'ndrstnd.io',
        'trackerUserActive' => true,
    ];

    private static $instance;

    /** @var ISettingsAdapter */
    private $adapter;

    private function __construct($adapter)
    {
        $this->adapter = $adapter;
    }

    public function getSettings()
    {
        $saCredentialEmail = $this->extractEmailFromSa($this->adapter->getServiceAccount());
        $projectId = $this->adapter->getProjectId();
        $ndrstndConfig = $this->adapter->getNdrstndSettings();

        return array_merge([
            'saCredentialEmail' => $saCredentialEmail,
            'projectId' => $projectId,
        ], $ndrstndConfig);
    }

    public function getServiceAccount()
    {
        return $this->adapter->getServiceAccount();
    }

    /**
     * @param string | null $project_id
     * @return void
     */
    public function updateProjectId($project_id)
    {
        $this->adapter->setProjectId($project_id);
    }

    /**
     * @param array $settings
     * @return void
     */
    public function updateNdrstndSettings($settings)
    {
        $this->adapter->setNdrstndSettings($settings);
    }

    /**
     * @param string | null $sa
     * @return void
     */
    public function updateServiceAccount($sa)
    {
        $this->adapter->setProjectId(null);
        $this->adapter->setServiceAccount($sa);
    }

    /**
     * @param string | null $sa
     * @return string | null
     */
    private function extractEmailFromSa($sa)
    {
        if (is_null($sa)) {
            return null;
        }

        return json_decode($sa)->serviceAccountEmail;
    }

    public static function getInstance($adapter = [])
    {
        if (self::$instance === null) {
            self::$instance = new self($adapter);
        }
        return self::$instance;
    }
}
