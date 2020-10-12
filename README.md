# Phalcon-JobQueue
PHP job queue for asynchronous tasks within the Phalcon Framework.

## Introduction
Phalcon has support for [Beanstalkd](http://kr.github.io/beanstalkd/), but it requires an broker to run jobs.

This is a different implementation that runs within the same environment. It has support for synchronous mode.

To add a job:

```php
$this->jobQueue->add(new Job("sayHello", [
    "name" => "World"
]));
```

Below is an example of an executor service:

```php
<?php

namespace My\Services;

use Phalcon\Di\Injectable;

use BasilFX\JobQueue\ExecutorInterface;
use BasilFX\JobQueue\Queue;
use BasilFX\JobQueue\Job;

class JobExecutor extends Injectable implements ExecutorInterface
{
    public function execute(Queue $queue, Job $job, $update)
    {
        ($queue);

        $action = $job->getAction();
        $parameters = $job->getParameters();

        if ($action === "sayHello") {
            echo "Hello, {$parameters["name"]}!";

            $job->setResult("Done.");
            $job->setProgress(100);

            $update($job);
        }
    }
}
```

## Requirements
* PHP 7.2 or later.
* Phalcon Framework 4.0 or later.

## Installation
Install this dependency using `composer require basilfx/phalcon-jobqueue`.

## License
See the `LICENSE` file (MIT license).
