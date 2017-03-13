<?php

namespace BasilFX\JobQueue\Contrib;

use Phalcon\CLI\Task;

use BasilFX\JobQueue\Job;
use BasilFX\JobQueue\Exception;

/**
 * Phalcon JobQueue related tasks.
 */
class PhalconTask extends Task
{
    /**
     * Start the job broker loop.
     */
    public function mainAction()
    {
        $running = true;
        $success = 0;
        $failed = 0;

        echo "Starting worker task. To interrupt, press CTRL + C.\n";

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
                $success++;
            } catch (\Exception $e) {
                echo "Job with ID {$job->getId()} failed: {$e->getMessage()}.\n";
                $failed++;
            }
        }

        echo "Worker task stopped, $success successful and $failed failed tasks processed.\n";
    }
}
