<?php


namespace WebmarketerPluginCore\Queue;

use Exception;

class WebmarketerQueueManager
{
    private $before_job = [];
    private $after_job = [];

    // Singleton
    private static $instance;

    public static function getInstance($adapter = [])
    {
        if (self::$instance === null) {
            self::$instance = new self($adapter);
        }
        return self::$instance;
    }

    /** @var IQueueStorageAdapter */
    private $adapter;

    private function __construct($adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param $job AbstractJob
     * @retun int
     */
    public function createJob($job)
    {
        return $this->adapter->createJob($job);
    }

    /**
     * Allows to run callbacks before a job is run
     * @param $callable
     */
    public function registerBeforeJob($callable)
    {
        if (is_callable($callable)) {
            $this->before_job[] = $callable;
        }
    }

    /**
     * Allows to run callbacks after a job is run
     * @param $callable
     */
    public function registerAfterJob($callable)
    {
        if (is_callable($callable)) {
            $this->after_job[] = $callable;
        }
    }

    /**
     * Delete a job in
     * @param $job_id
     */
    public function deletejob($job_id)
    {
        $this->adapter->deletejob($job_id);
    }

    /**
     * Get the list of current jobs.
     *
     * Options :
     * - status (all:default, awaiting, playable)
     * @param $options
     */
    public function getJobs($options)
    {
        return $this->adapter->getJobs($options);
    }


    /**
     * This method run a job
     * @param $id
     */
    public function runJob($sdk, $id)
    {
        global $wpdb;
        $job = $this->adapter->getJobById($id);

        // Call macros
        foreach ($this->before_job as $callable) {
            call_user_func_array($callable, [
                $job
            ]);
        }

        /** @var AbstractJob $job_class */
        $job_class = unserialize($job->serialized);
        try {
            // Run the job
            $job_class->handle($sdk);
            $this->adapter->markJobSuccess($id);

            // Call macros
            foreach ($this->after_job as $callable) {
                call_user_func_array($callable, [
                    $job,
                    'success',
                    null
                ]);
            }

            return true;
        } catch (JobSkipException $ex) {
            $this->adapter->markJobSkipped($id, $ex->getMessage());

            // Call macros
            foreach ($this->after_job as $callable) {
                call_user_func_array($callable, [
                    $job,
                    'skipped',
                    $ex
                ]);
            }
            return false;
        } catch (Exception $ex) {
            $this->adapter->markJobFailed($id, $ex->getMessage());

            // Call macros
            foreach ($this->after_job as $callable) {
                call_user_func_array($callable, [
                    $job,
                    'failure',
                    $ex
                ]);
            }
            return false;
        }
    }

}