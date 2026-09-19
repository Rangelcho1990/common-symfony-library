<?php

declare(strict_types=1);

namespace CSL\Tests\Support;

use Doctrine\DBAL\Connection;

final class TestDatabaseGuard
{
    public static function assertSafe(string $environment, Connection $connection): void
    {
        $params = $connection->getParams();
        if ('test' !== $environment
            || !in_array($params['driver'] ?? null, ['pdo_mysql', 'mysqli', 'pdo_pgsql', 'pgsql'], true)
            || isset($params['driverClass'])
            || !is_string($params['dbname'] ?? null)
            || !str_ends_with($params['dbname'], '_csl_test')
            || '_csl_test' === $params['dbname']
            || isset($params['primary'])
            || isset($params['replica'])
        ) {
            throw new \LogicException('Schema operations require the test environment and a MySQL/PostgreSQL database ending in _csl_test.');
        }
    }
}
