<?php

declare(strict_types=1);

namespace App\Service\Processes;

use App\Service\Types\DbRunnerProcessPayload;
use App\Service\Types\DbRunnerProcessResponse;
use App\Service\Types\SchemaDatabase;

class DbRunnerProcessService extends ProcessService
{
    public function main(object $input): object
    {
        if (!($input instanceof DbRunnerProcessPayload)) {
            throw new \InvalidArgumentException('Invalid input type');
        }

        $db = SchemaDatabase::get($input->getSchema());
        $result = $db->query($input->getQuery());

        /**
         * @var array<array<string, mixed>> $resultArray
         */
        $resultArray = [];

        try {
            while ($row = $result->fetchArray(\SQLITE3_ASSOC)) {
                $rowCasted = [];

                foreach ($row as $key => $value) {
                    $rowCasted[(string) $key] = $value;
                }

                $resultArray[] = $rowCasted;
            }
        } finally {
            $result->finalize();
        }

        return new DbRunnerProcessResponse($resultArray);
    }
}
