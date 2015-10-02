<?php

namespace SensioLabs\JobBoardBundle\Logger\Processor;

class ExceptionProcessor
{
    public function __invoke(array $record)
    {
        foreach ($record['context'] as $key => $value) {
            if (!$record['context'][$key] instanceof \Exception) {
                continue;
            }

            $e = $record['context'][$key];

            $record['context'][$key] = [
                'type' => get_class($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ];
        }

        return $record;
    }
}
