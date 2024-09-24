<?php

declare(strict_types=1);

namespace App\Service\Processes;

use App\Service\Types\ProcessError;

/**
 * The framework of a service process.
 */
abstract class ProcessService
{
    /**
     * Run the process.
     */
    public function run(): void
    {
        try {
            $stdin = file_get_contents('php://stdin');
            if (false === $stdin) {
                throw new \RuntimeException('could not read from stdin');
            }

            $input = unserialize($stdin);
            if (!\is_object($input)) {
                throw new \InvalidArgumentException('input must be an object');
            }

            $result = $this->main($input);
            fwrite(\STDOUT, serialize($result));
            exit(0);
        } catch (\Throwable $throwable) {
            fwrite(\STDERR, serialize(new ProcessError($throwable)));
            exit(1);
        }
    }

    /**
     * The main function of the process.
     *
     * @param object $input the input of the process
     *
     * @return object the result of the process
     */
    abstract public function main(object $input): object;
}
