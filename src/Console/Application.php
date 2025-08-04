<?php

namespace Kim1ne\B24\Console;

use Symfony\Component\Console;

class Application
{
    public function __construct(
        private readonly array $commands = []
    ) {}

    public function run()
    {
        $application = new Console\Application();

        foreach ($this->commands as $command) {
            $application->add($command);
        }

        $application->run();
    }
}
