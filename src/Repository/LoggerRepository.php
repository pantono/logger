<?php

namespace Pantono\Logger\Repository;

use Pantono\Database\Repository\DefaultRepository;
use Pantono\Logger\Model\HttpRequestLog;
use Pantono\Utilities\StringUtilities;

class LoggerRepository extends DefaultRepository
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
        $data = $log->getAllData();
        if (StringUtilities::isBinary($data['response_body'])) {
            $data['response_body'] = base64_encode($data['response_body']);
        }
        $id = $this->insertOrUpdateCheck('http_log', 'id', $log->getId(), $data);
        if ($id) {
            $log->setId($id);
        }
    }
}
