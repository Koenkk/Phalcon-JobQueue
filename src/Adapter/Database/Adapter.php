<?php

namespace BasilFX\JobQueue\Adapter\Database;

use BasilFX\JobQueue\Adapter\Database\Job as Model;

use BasilFX\JobQueue\AdapterInterface;
use BasilFX\JobQueue\Exception;
use BasilFX\JobQueue\Job;

/**
 * Job adapter using a database as backend.
 *
 * Note: this adapter provides supports some means of concurrency, but it is
 * not guaranteed.
 */
class Adapter implements AdapterInterface
{
    /**
     * Local-unique lock ID.
     *
     * @var string
     */
    private $lockID;

    /**
     * Construct a new database job queue adapter.
     */
    public function __construct()
    {
        $this->lockID = bin2hex(random_bytes(32));
    }

    /**
     * @inheritdoc
     */
    public function get($jobID)
    {
        return $this->modelToJob($this->getModel($jobID));
    }

    /**
     * @inheritdoc
     */
    public function getNext()
    {
        $model = Model::findFirst([
            "conditions" => "state = ?0 AND lock IS NULL AND deleted IS NULL",
            "bind" => [Job::PENDING],
            "order" => "created ASC",
        ]);

        if (!$model) {
            return null;
        }

        return $this->modelToJob($model);
    }

    /**
     * @inheritdoc
     */
    public function create($job)
    {
        if ($job->getID()) {
            throw new Exception("Job already in queue.");
        }

        $model = $this->jobToModel($job);

        if (!$model->save()) {
            throw new Exception("Unable to add job to queue.");
        }

        return $this->modelToJob($model, $job);
    }

    /**
     * @inheritdoc
     */
    public function read($job)
    {
        $model = $this->jobToModel($job, $this->getModelFromJob($job));
        $model->refresh();

        return $this->modelToJob($model, $job);
    }

    /**
     * @inheritdoc
     */
    public function update($job)
    {
        $model = $this->jobToModel($job, $this->getModelFromJob($job));
        $model->save();

        return $this->modelToJob($model, $job);
    }

    /**
     * @inheritdoc
     */
    public function delete($job)
    {
        $model = $this->jobToModel($job, $this->getModelFromJob($job));
        $model->setDeleted(time());
        $model->save();
    }

    /**
     * @inheritdoc
     */
    public function lock($job)
    {
        $model = $this->jobToModel($job, $this->getModelFromJob($job), false);

        if ($model->getLock() != null && $model->getLock() != $this->lockID) {
            throw new Exception("Job is locked by another queue.");
        }

        // Set the lock and check for concurrent modifications.
        $model->setLock($this->lockID);
        $model->save();

        usleep(rand(1000, 50000));

        $model->refresh();

        if ($model->getLock() != $this->lockID) {
            throw new Exception("Job lock overwritten by another queue.");
        }
    }

    /**
     * @inheritdoc
     */
    public function unlock($job)
    {
        $model = $this->jobToModel($job, $this->getModelFromJob($job), false);

        if ($model->getLock() != $this->lockID) {
            throw new Exception("Job locked by another queue.");
        }

        // Unset the lock and check for concurrent modifications.
        $model->setLock(null);
        $model->save();

        usleep(rand(1000, 50000));

        $model->refresh();

        if ($model->getLock() != null) {
            throw new Exception("Job unlock overwritten by another queue.");
        }
    }

    /**
     * Convert a model instance to a job instance.
     */
    private function modelToJob($model, $job = null, $full = true)
    {
        if ($job == null) {
            $job = new Job();
        }

        $job->setID($model->getID());
        $job->setState($model->getState());

        if ($full) {
            $job->setAction($model->getAction());
            $job->setParameters($model->getParameters());
            $job->setResult($model->getResult());
            $job->setProgress($model->getProgress());
        }

        return $job;
    }

    /**
     * Convert a job instance to a model instance.
     */
    private function jobToModel($job, $model = null, $full = true)
    {
        if ($model === null) {
            $model = new Model();
        }

        $model->setID($job->getID());
        $model->setState($job->getState());

        if ($full) {
            $model->setAction($job->getAction());
            $model->setParameters($job->getParameters());
            $model->setResult($job->getResult());
            $model->setProgress($job->getProgress());
        }

        return $model;
    }

    /**
     * Get the model from a job.
     */
    private function getModelFromJob($job)
    {
        if (!$job->getID()) {
            throw new Exception("Job not in queue.");
        }

        return $this->getModel($job->getID());
    }

    /**
     * Get the model from a job ID.
     */
    private function getModel($jobID)
    {
        $model = Model::findFirstByID($jobID);

        if (!$model || $model->getDeleted()) {
            throw new Exception("Job not found.");
        }

        return $model;
    }
}
