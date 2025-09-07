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

        $this->table('http_log')
            ->addColumn('date_started', 'datetime')
            ->addColumn('date_completed', 'datetime', ['null' => true])
            ->addColumn('service', 'string')
            ->addColumn('method', 'string', ['null' => true])
            ->addColumn('uri', 'string', ['null' => true])
            ->addColumn('request_headers', 'json', ['null' => true])
            ->addColumn('request_body', 'text', ['length' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG, 'null' => true])
            ->addColumn('response_code', 'integer', ['null' => true])
            ->addColumn('response_headers', 'json', ['null' => true])
            ->addColumn('response_body', 'text', ['length' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG, 'null' => true])
            ->addColumn('time_taken', 'float', ['null' => true])
            ->addIndex('date_started')
            ->addIndex('date_completed')
            ->addIndex('service')
            ->addIndex('response_code')
            ->create();
    }
}
