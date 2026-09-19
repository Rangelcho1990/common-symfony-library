<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Support;

use CSL\Tests\Support\TestDatabaseGuard;
use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class TestDatabaseGuardTest extends TestCase
{
    public function testConfigurationAppendsSuffixToDatabaseUrlWithoutConnecting(): void
    {
        $config = Yaml::parseFile(dirname(__DIR__, 3).'/config/packages/doctrine.yaml');
        self::assertIsArray($config);
        self::assertIsArray($config['when@test']);
        self::assertIsArray($config['when@test']['doctrine']);
        $testConfig = $config['when@test']['doctrine']['dbal'];
        self::assertIsArray($testConfig);
        self::assertSame('_csl_test', $testConfig['dbname_suffix']);
        $connection = (new ConnectionFactory([]))->createConnection([
            'url' => 'mysql://user:password@localhost/development',
            'dbname_suffix' => $testConfig['dbname_suffix'],
            'serverVersion' => '8.0.0',
        ]);
        self::assertSame('development_csl_test', $connection->getParams()['dbname']);
        TestDatabaseGuard::assertSafe('test', $connection);
        self::assertFalse($connection->isConnected());
    }

    #[DataProvider('unsafeConnections')]
    public function testRejectsUnsafeConnections(string $environment, ?string $database): void
    {
        $params = ['driver' => 'pdo_mysql'];
        if (null !== $database) {
            $params['dbname'] = $database;
        }
        $connection = DriverManager::getConnection($params);
        $this->expectException(\LogicException::class);
        TestDatabaseGuard::assertSafe($environment, $connection);
    }

    public function testRejectsSqliteEvenWithTestDatabaseName(): void
    {
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'dbname' => 'app_csl_test', 'path' => '/tmp/development.sqlite']);
        $this->expectException(\LogicException::class);
        TestDatabaseGuard::assertSafe('test', $connection);
    }

    /** @return iterable<string, array{string, ?string}> */
    public static function unsafeConnections(): iterable
    {
        yield 'development environment' => ['dev', 'app_csl_test'];
        yield 'production environment' => ['prod', 'app_csl_test'];
        yield 'development database' => ['test', 'app'];
        yield 'suffix in middle' => ['test', 'app_csl_test_backup'];
        yield 'suffix only' => ['test', '_csl_test'];
        yield 'missing database' => ['test', null];
    }
}
