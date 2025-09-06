<?php

namespace Pantono\Logger\Repository;

use Pantono\Database\Repository\MysqlRepository;

class LoggerRepository extends MysqlRepository
{
    public function logMessage(string $service, string $level, string $message, array $context = []): void
    {
        $this->getDb()->insert('log', [
            'date' => (new \DateTime())->format('Y-m-d H:i:s'),
            'service' => $service,
            'level' => $level,
            'message' => $message,
            'context' => json_encode($context)
        ]);
    }
}
