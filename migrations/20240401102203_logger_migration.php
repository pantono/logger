<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class LoggerMigration extends AbstractMigration
{
    public function change(): void
    {
        $this->table('log')
            ->addColumn('date', 'datetime')
            ->addColumn('service', 'string')
            ->addColumn('level', 'string')
            ->addColumn('message', 'string')
            ->addColumn('context', 'json', ['default' => '{}'])
            ->addIndex('date')
            ->addIndex('service')
            ->create();
    }
}
