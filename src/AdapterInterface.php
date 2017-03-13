<?php

namespace BasilFX\JobQueue;

/**
 * Interface of an adapter.
 */
interface AdapterInterface
{
    /**
     * Given a job ID, return the Job, or null if not found.
     *
     * @param string $jobID
     * @return Job
     */
    public function get($jobID);

    /**
     * Return the next job on the queue
     *
     * @return Job
     */
    public function getNext();

    /**
     * Add a job to the queue.
     *
     * @param Job $job
     * @return Job
     */
    public function create($job);

    /**
     * Refresh a given job.
     *
     * @param Job $job
     * @return Job
     */
    public function read($job);

    /**
     * Update a given job.
     *
     * @param Job $job
     * @return Job
     */
    public function update($job);

    /**
     * Delete the given job.
     *
     * @param Job $job
     * @return Job
     */
    public function delete($job);

    /**
     * Lock the job for mutual exclusive operation.
     *
     * @param Job $job
     * @return Job
     */
    public function lock($job);

    /**
     * Unlock the job.
     *
     * @param Job $job
     * @return Job
     */
    public function unlock($job);
}
