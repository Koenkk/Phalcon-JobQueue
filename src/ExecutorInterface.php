<?php

namespace BasilFX\JobQueue;

/**
 * Interface of a job executor.
 */
interface ExecutorInterface
{
    public function execute(Queue $queue, Job $job, $update);
}
