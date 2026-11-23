<?php

declare(strict_types=1);

use App\Cli\CronRunner;

require __DIR__ . '/../vendor/autoload.php';

$container = require __DIR__ . '/../config/container.php';

exit($container->get(CronRunner::class)->run($argv));
