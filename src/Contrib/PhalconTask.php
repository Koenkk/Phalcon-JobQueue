<?php

namespace BasilFX\JobQueue\Contrib;

use Phalcon\CLI\Task;

use BasilFX\JobQueue\Job;
use BasilFX\JobQueue\Exception;

/**
 * @description("Job queue tasks runner")
 */
class JobQueueTask extends Task
{
    /**
     * Start the job broker loop.
     *
     * @description("Spawn a new worker process to handle queued jobs")
     */
    public function mainAction()
    {
        $running = true;
        echo "Starting worker task until. To interrupt, press CTRL + C.\n";

        // This is the main loop that will poll for tasks and process them.
        while ($running) {
            // Fetch the next job from the queue.
            $job = $this->jobQueue->getNext();

            if (!$job) {
                usleep(500000);
                continue;
            }

            // Process a job.
            try {
                echo "Processing job with ID {$job->getId()}.\n";

                $start = microtime(true);
                $this->jobQueue->process($job);
                $time = (microtime(true) - $start) * 1000;

                echo "Job with ID {$job->getId()} finished in $time ms.\n";
            } catch (\Exception $e) {
                echo "Job with ID {$job->getId()} failed: {$e->getMessage()}.\n";
            }
        }

        echo "Worker task stopped.\n";
    }
}
