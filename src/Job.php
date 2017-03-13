<?php

namespace BasilFX\JobQueue;

/**
 * Definition of a Job.
 */
class Job
{
    const PENDING = 1;
    const BUSY = 2;
    const DONE = 3;

    private $ID = null;

    private $state = Job::PENDING;

    private $action = null;

    private $parameters = null;

    private $result = null;

    private $progress = null;

    public function __construct($action = null, $parameters = null)
    {
        $this->action = $action;
        $this->parameters = $parameters;
    }

    public function getID()
    {
        return $this->ID;
    }

    public function getState()
    {
        return $this->state;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getProgress()
    {
        return $this->progress;
    }

    public function setID($ID)
    {
        $this->ID = $ID;
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    public function setResult($result)
    {
        $this->result = $result;
    }

    public function setProgress($progress)
    {
        $this->progress = $progress;
    }
}
