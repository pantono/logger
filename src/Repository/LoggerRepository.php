<?php

namespace Pantono\Logger\Repository;

use Pantono\Database\Repository\MysqlRepository;
use Pantono\Logger\Model\HttpRequestLog;

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

    public function logHttpRequest(HttpRequestLog $log): void
    {
        $id = $this->insertOrUpdateCheck('http_log', 'id', $log->getId(), $log->getAllData());
        if ($id) {
            $log->setId($id);
        }
    }
}
