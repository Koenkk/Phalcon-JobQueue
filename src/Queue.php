<?php

namespace BasilFX\JobQueue;

use Phalcon\Di\Injectable;

/**
 * Implementation of the job queue.
 *
 * The queue is responsible for executing jobs in asynchronous in an ordered
 * fashion.
 */
class Queue extends Injectable
{
    /**
     * The queue adapter.
     *
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * If true, run jobs when added (useful for testing).
     *
     * @var bool
     */
    private $synchronous;

    /**
     *
     */
    public function __construct(AdapterInterface $adapter, ExecutorInterface $executor, $synchronous = false)
    {
        $this->adapter = $adapter;
        $this->executor = $executor;
        $this->synchronous = $synchronous;
    }

    /**
     * Get the next job.
     *
     * @param string $jobID
     * @return Job
     */
    public function get($jobID)
    {
        return $this->adapter->get($jobID);
    }

    /**
     * Retrieve the next job to process.
     *
     * @return Job
     */
    public function getNext()
    {
        return $this->adapter->getNext();
    }

    /**
     * Add a new job to the queue.
     *
     * @return Job
     */
    public function add($job)
    {
        $this->adapter->create($job);

        if ($this->synchronous) {
            $this->process($job);
        }
    }

    /**
     * Remove the job from the queue.
     *
     * @return Job
     */
    public function remove($job)
    {
        $this->adapter->lock($job);

        try {
            $this->adapter->delete($job);
        } finally {
            $this->adapter->unlock($job);
        }
    }

    /**
     * Wait for the job to become available.
     *
     * @return boolean True if the job is available.
     */
    public function wait($job, $timeout = null)
    {
        $done = false;

        while (!$done) {
            $this->adapter->read($job);

            if ($job->getState() == Job::DONE) {
                return true;
            }

            // Add sleep to reduce CPU load.
            $sleep = rand(10000, 100000);

            if ($timeout) {
                if ($timeout - $sleep < 0) {
                    $sleep = $timeout;
                    $done = true;
                } else {
                    $timeout = $timeout - $sleep;
                }
            }

            usleep($sleep);
        }

        return false;
    }

    /**
     * Process the given job.
     *
     * The job is locked for operation, and then processed given the executor.
     *
     * @param Job $job
     */
    public function process(Job $job)
    {
        // Ensure job is not already processed.
        if ($job->getState() != Job::PENDING) {
            throw new Exception("Job already processed.");
        }

        // Process the job.
        $this->adapter->lock($job);

        try {
            $job->setState(Job::BUSY);
            $this->adapter->update($job);

            // Run the job. An exception is a result too. Note that the handle
            // itself should update the job.
            try {
                $this->executor->execute($this, $job, function ($job) {
                    $this->adapter->update($job);
                });
            } catch (\Exception $e) {
                $job->setResult($e->getTraceAsString());

                if ($this->synchronous) {
                    throw $e;
                }
            }

            $job->setState(Job::DONE);
            $this->adapter->update($job);
        } finally {
            $this->adapter->unlock($job);
        }
    }
}
