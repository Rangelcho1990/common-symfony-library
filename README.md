# Common Symfony Library

## What is this?
A reusable Symfony 7 library providing foundational building blocks:
- Application structure and conventions
- Doctrine ORM integration and examples
- Custom logging module built on Monolog (GELF/Graylog support)
- Example DTOs, Exceptions, Repositories, and Endpoints
- Nelmio API documentation integration

## Technical Requirements
- PHP >= 8.2
- Symfony 7.3
- Doctrine ORM 3.5
- Composer

## Installation
1. Clone the repository
2. Run `composer install`
3. Copy `.env.example` to `.env` and adjust values
4. Create DB and run migrations: `composer db-migrate:next`
5. Start the server: `symfony serve`
6. Open `http://127.0.0.1:8000/`

Symfony Profiler (dev): `https://127.0.0.1:8000/_profiler/`

## Configuration
Provide the following environment variables in your `.env` (or system env):

| Name | Required | Default | Description |
| ---- | -------- | ------- | ----------- |
| `APP_ENV` | no | `dev` | Environment name |
| `APP_DEBUG` | no | `1` | Enable debug mode in dev |
| `DOCS_URI` | no | `/api/doc` | Base URI path for API docs |
| `DATABASE_URL` | yes | — | Doctrine DB connection string |
| `REDIS_DSN` | no | `redis://127.0.0.1:6379` | Redis connection used by the Redis service |

Example `.env.example`:

```env
APP_ENV=dev
APP_DEBUG=1

DOCS_URI=/api/docs

DATABASE_URL="mysql://symfony:symfony@127.0.0.1:5432/symfony?serverVersion=16&charset=utf8"

# Redis
REDIS_DSN=redis://127.0.0.1:6379
```

## Logging
This library uses Symfony Monolog by default and provides a custom logger module.

### Monolog defaults
Configured in `config/packages/monolog.yaml`:
- dev: stream to `%kernel.logs_dir%/dev.log` at `debug`, plus console handler
- test: `fingers_crossed` buffering to `%kernel.logs_dir%/test.log`
- prod: `fingers_crossed` with nested stream to `php://stderr` using JSON formatter; deprecations also sent to `php://stderr` in JSON

Example (prod excerpt):

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

### Custom logger module
`CSL\Module\LoggerBundle` exposes a factory and configurable handlers via `config/packages/logger.yaml`.

Parameters (example):

```yaml
parameters:
    handlers:
        StreamHandler:
            host: "php://stdout"
            level: 100
            format: '{"timestamp": ":timestamp:", "level": ":level:", "messageTemplate": "{@Type}, EventId: {@EventId} {@Metrics}", "additional_data": { "requestUid": "", "requestBodyStringified": "", "requestQuery": "", "method": "", "ip": "", "other": "", "responseBodyStringified": "", "message": "", "errorMessage": "", "errorFile": "", "errorLine": "", "stackTrace": ""}}'
#       GelfHandlerTcp:
#           host: "127.0.0.1"
#           port: 12201
#           source: "GelfHandlerTcp"
#           level: 100
#           format: '{"timestamp": ":timestamp:", "level": ":level:", "messageTemplate": "{@Type}, EventId: {@EventId} {@Metrics}", "additional_data": { "requestUid": "", "requestBodyStringified": "", "requestQuery": "", "method": "", "ip": "", "other": "", "responseBodyStringified": "", "message": "", "errorMessage": "", "errorFile": "", "errorLine": "", "stackTrace": ""}}'
    app_name: "common-service-template-api"
```

Notes:
- Default stream target is `php://stdout` with structured JSON lines.
- If you need GELF/Graylog, uncomment `GelfHandlerTcp` and provide host/port.

## Redis Service
This library depends on `uzunov-labs/redis-service`.
- Ensure Redis DSN is configured via `REDIS_DSN`.
- Confirm the Redis service package config is loaded. If you vendor or copy the example config, name the file `config/packages/redis_service.yaml`.

## Doctrine
- Configure `DATABASE_URL` in `.env`
- Generate migrations: `composer db-migrate:generate`
- Apply migrations: `composer db-migrate:next`

## API Documentation (Nelmio)
- Docs path: `${DOCS_URI}` (default: `/api/doc`)
- Ensure routes are enabled under `config/routes/nelmio_api_doc.yaml`

## Development Scripts
- Static analysis: `composer phpstan`
- Coding standards: `composer cs-fix`
- Test suite: `composer test`

### Suggested CI Checks
Run in CI (GitHub Actions or similar):
1. `composer validate --no-check-publish`
2. `composer install --no-interaction --no-progress --prefer-dist`
3. `composer phpstan`
4. `composer test`
5. `vendor/bin/php-cs-fixer fix --dry-run --diff`

## Security Notes
- Validate input using Symfony Validator; prefer typed DTOs.
- Avoid logging PII; standardize log context keys and redact sensitive data.
- Configure `trusted_proxies` and `trusted_hosts` in host apps when deploying behind load balancers.

## Examples in Repository
- Example Entity and Repository under `src/Entity` and `src/Repository`
- Example Endpoint(s) under `src/Endpoints`

