<?php

namespace WebmarketerPluginCore\Queue;

interface IQueueStorageAdapter
{
    /**
     * @param $job AbstractJob
     * @return mixed
     */
    public function createJob($job);

    public function getJobs($options);

    public function getJobById($id);

    public function markJobSuccess($id);

    public function markJobSkipped($id, $reason = null);

    public function markJobFailed($id, $reason = null);

    public function deletejob($job_id);
}