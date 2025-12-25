<?php

namespace Pantono\Logger\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Pantono\Logger\Model\AuditLog;

abstract class AbstractAuditLogSaveEvent extends Event
{
    private AuditLog $current;
    private ?AuditLog $previous = null;

    public function getCurrent(): AuditLog
    {
        return $this->current;
    }

    public function setCurrent(AuditLog $current): void
    {
        $this->current = $current;
    }

    public function getPrevious(): ?AuditLog
    {
        return $this->previous;
    }

    public function setPrevious(?AuditLog $previous): void
    {
        $this->previous = $previous;
    }
}
