# Common Symfony Library

## What is this?

Common Symfony Library (CSL) is a Symfony 7.4 project-style library that provides reusable building blocks for Symfony applications:

- `CSL\` PSR-4 application namespace under `src/`
- Doctrine ORM entities, repositories, and migrations
- Custom logging module built on Monolog with stream and GELF/Graylog handlers
- Request, response, and error event subscribers
- Shared exceptions, controller base class, and client communication service
- Nelmio API documentation integration
- Redis service integration through `uzunov-labs/redis-service`

Composer classifies this repository as a `project` with a proprietary license.

## Composer Requirements

- PHP `>=8.4`
- PHP extensions: `ctype`, `iconv`
- Symfony `7.4.*` components and bundles:
  - `symfony/framework-bundle`
  - `symfony/console`
  - `symfony/dependency-injection`
  - `symfony/dotenv`
  - `symfony/runtime`
  - `symfony/asset`
  - `symfony/twig-bundle`
  - `symfony/validator`
  - `symfony/yaml`
  - `symfony/monolog-bundle`
- Doctrine:
  - `doctrine/orm` `^3.5`
  - `doctrine/doctrine-bundle` `^2.16`
  - `doctrine/doctrine-migrations-bundle` `^3.4`
- Logging and documentation:
  - `monolog/monolog` `^3.9`
  - `graylog2/gelf-php` `^2.0`
  - `nelmio/api-doc-bundle` `^5.6`
- Twig:
  - `twig/twig` `^2.12|^3.0`
  - `twig/extra-bundle` `^2.12|^3.0`
- Other runtime packages:
  - `ramsey/uuid-doctrine` `^2.1`
  - `uzunov-labs/redis-service` `1.0.2`

The Redis package is resolved through the configured VCS repository:

```json
{
    "type": "vcs",
    "url": "https://github.com/Rangelcho1990/redis-toolkit"
}
```

## Development Requirements

Composer development dependencies include:

- PHPUnit `^12.3`
- PHPStan `^2.1`
- PHP CS Fixer `^3.87`
- Symfony BrowserKit, CSS Selector, Stopwatch, and Web Profiler Bundle `7.4.*`

## Autoloading

Runtime classes are autoloaded from `src/`:

```json
{
    "CSL\\": "src/"
}
```

Test classes are autoloaded from `tests/`:

```json
{
    "CSL\\Tests\\": "tests/"
}
```

After adding classes, refresh Composer autoloads when needed:

```bash
composer dump-autoload
```

## Installation

1. Clone the repository.
2. Run Composer:

   ```bash
   composer install
   ```

3. Copy the environment template and adjust values:

   ```bash
   cp .env.example .env
   ```

4. Configure `DATABASE_URL`, `REDIS_DSN`, `APP_SECRET`, and `DOCS_URI`.
5. Run the next Doctrine migration:

   ```bash
   composer db-migrate:next
   ```

6. Start the local Symfony server:

   ```bash
   symfony serve
   ```

7. Open `http://127.0.0.1:8000/`.

Symfony Profiler is available in dev and test environments at `/_profiler`.

## Configuration

Provide the following environment variables in `.env`, `.env.local`, or the host environment:

| Name | Required | Default/example | Description |
| ---- | -------- | --------------- | ----------- |
| `APP_ENV` | no | `dev` | Symfony environment name |
| `APP_SECRET` | yes | empty in `.env.example` | Symfony application secret |
| `APP_DEBUG` | no | `1` | Enables debug mode in dev |
| `DOCS_URI` | no | `/api/doc` | Base URI path for API docs |
| `DATABASE_URL` | yes | `mysql://_user_:_password_@_host_/sport` | Doctrine DB connection string |
| `REDIS_DSN` | yes | `redis://_host_/0` | Redis connection used by the Redis service |

Example `.env.example`:

```env
APP_ENV=dev
APP_SECRET=
APP_DEBUG=1

REDIS_DSN=redis://_host_/0

DATABASE_URL="mysql://_user_:_password_@_host_/sport"

DOCS_URI='/api/doc'
```

For tests, use `.env.test.example` as the starting point.

## Registered Bundles

The application registers these bundles in `config/bundles.php`:

- `Symfony\Bundle\FrameworkBundle\FrameworkBundle`
- `RedisService\Symfony\RedisServiceBundle`
- `Doctrine\Bundle\DoctrineBundle\DoctrineBundle`
- `Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle`
- `Nelmio\ApiDocBundle\NelmioApiDocBundle`
- `Symfony\Bundle\TwigBundle\TwigBundle`
- `Twig\Extra\TwigExtraBundle`
- `Symfony\Bundle\WebProfilerBundle\WebProfilerBundle` for `dev` and `test`
- `Symfony\Bundle\MonologBundle\MonologBundle`

## Logging

This project uses Symfony Monolog and provides a custom logger module under `src/Module/LoggerBundle`.

### Monolog Defaults

Configured in `config/packages/monolog.yaml`:

- dev: stream to `%kernel.logs_dir%/dev.log` at `debug`, plus console handler
- test: `fingers_crossed` buffering to `%kernel.logs_dir%/test.log`
- prod: `fingers_crossed` with nested stream to `php://stderr` using JSON formatter
- deprecations: sent to `php://stderr` in JSON

Example prod excerpt:

```yaml
monolog:
    handlers:
        main:
            type: fingers_crossed
            action_level: error
            handler: nested
            excluded_http_codes: [404, 405]
        nested:
            type: stream
            path: php://stderr
            level: debug
            formatter: monolog.formatter.json
        deprecation:
            type: stream
            channels: [deprecation]
            path: php://stderr
            formatter: monolog.formatter.json
```

### Custom Logger Module

Environment-specific logger configuration lives in:

- `config/packages/dev/logger.yaml`
- `config/packages/test/logger.yaml`
- `config/packages/prod/logger.yaml`

The logger module provides:

- `CslLoggerFactory`
- `CslStreamHandler`
- `CslGelfHandlerTcp`
- handler registry and factory services
- structured log formatters
- info, critical, and imported-event logger helpers

Example stream handler configuration:

```yaml
parameters:
    handlers:
        StreamHandler:
            host: "php://stdout"
            level: 100
            format: '{"timestamp": ":timestamp:", "level": ":level:", "messageTemplate": "{@Type}, EventId: {@EventId} {@Metrics}", "additional_data": { "requestUid": "", "requestBodyStringified": "", "requestQuery": "", "method": "", "ip": "", "other": "", "responseBodyStringified": "", "message": "", "errorMessage": "", "errorFile": "", "errorLine": "", "stackTrace": ""}}'
    app_name: "common-service-template-api"
```

To send logs to Graylog, configure `GelfHandlerTcp` with a host, port, source, level, and formatter.

## Redis Service

This repository depends on `uzunov-labs/redis-service`.

- Configure `REDIS_DSN`.
- Keep `config/packages/redis_service.yaml` loaded.
- Ensure Composer can access the configured VCS repository for the Redis package.

## Doctrine

- Configure `DATABASE_URL`.
- Generate a migration:

  ```bash
  composer db-migrate:generate
  ```

- Apply the next migration:

  ```bash
  composer db-migrate:next
  ```

## API Documentation

Nelmio API Doc Bundle is registered and configured in:

- `config/packages/nelmio_api_doc.yaml`
- `config/routes/nelmio_api_doc.yaml`

The docs URI is controlled by `DOCS_URI`, with `/api/doc` as the example value.

## Composer Scripts

Composer defines the following project scripts:

| Script | Command |
| ------ | ------- |
| `composer db-migrate:next` | `php bin/console doctrine:migrations:migrate next` |
| `composer db-migrate:generate` | `php bin/console doctrine:migrations:generate` |
| `composer phpstan` | `vendor/bin/phpstan analyse` |
| `composer cs-fix` | `vendor/bin/php-cs-fixer fix` |
| `composer code-fix` | Runs `cs-fix` and `phpstan` |
| `composer test` | `vendor/bin/phpunit --testdox` |

Composer also runs Symfony Flex auto-scripts after install and update:

- `cache:clear`
- `assets:install %PUBLIC_DIR%`

## Development Checks

Run the main local checks before opening or merging changes:

```bash
composer validate --no-check-publish
composer phpstan
composer test
vendor/bin/php-cs-fixer fix --dry-run --diff
```

## Security Notes

- Validate input using Symfony Validator and typed DTOs.
- Avoid logging PII; standardize log context keys and redact sensitive values.
- Configure trusted proxies and trusted hosts in applications that deploy behind load balancers.
