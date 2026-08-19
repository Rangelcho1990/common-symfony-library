# Common Symfony Library - Architecture

## Overview

Common Symfony Library (CSL) is a Symfony 7.4 project-style library that provides reusable application building blocks under the `CSL\` namespace. The codebase uses PHP 8.4+, strict types, Symfony dependency injection, Doctrine ORM, event subscribers, a custom Monolog-based logger module, Redis integration, and Nelmio API documentation.

Composer classifies the repository as a `project` with a proprietary license. Runtime code is loaded from `src/`, and test code is loaded from `tests/`.

## Project Structure

```text
common-symfony-library/
├── bin/                         # Console and PHPUnit entry points
├── config/                      # Symfony, bundle, route, and service configuration
│   ├── packages/                # Package configuration and env-specific logger config
│   ├── routes/                  # Supplemental route configuration
│   ├── services/                # Focused service definitions for logger factories
│   ├── bundles.php              # Registered Symfony bundles
│   ├── routes.yaml              # Main route imports and API docs route
│   └── services.yaml            # Service defaults and CSL namespace registration
├── migrations/                  # Doctrine migration classes
├── public/                      # Web entry point and public assets
├── src/
│   ├── Controller/              # Shared controller base classes
│   ├── Endpoints/               # Attribute-routed endpoint controllers and transformers
│   ├── Entity/                  # Doctrine entities
│   ├── Events/                  # Kernel event subscribers and subscriber DTOs
│   ├── Exceptions/              # Shared exception hierarchy
│   ├── Module/                  # Reusable modules: logger, error handler, traits
│   ├── Repository/              # Doctrine repositories
│   ├── Service/                 # Application services
│   └── Kernel.php               # Symfony kernel
├── templates/                   # Twig templates
├── tests/                       # PHPUnit unit and functional tests
└── var/                         # Runtime cache and logs
```

## Runtime Architecture

### HTTP Entry Points

The public HTTP entry point is `public/index.php`, which boots the Symfony runtime and `CSL\Kernel`.

Routes are configured in `config/routes.yaml`:

- `app.swagger_ui` exposes Nelmio Swagger UI at `/api/doc`.
- Controllers under `src/Endpoints/` are loaded as PHP attribute routes in the `CSL\Endpoints` namespace.

The current example endpoint is `CSL\Endpoints\Examples\ExampleList\Controller\ExampleController`, which exposes `GET /example` and returns Redis connection status through `RedisService\Core\Container\RedisContainer`.

### Controller Layer

Controllers extend `CSL\Controller\CslAbstractController`, a thin project base class over Symfony's `AbstractController`.

Endpoint-specific code lives under `src/Endpoints/`. The existing example endpoint also includes `ExampleTransformer`, which demonstrates a response transformer shape for future endpoint response handling.

### Event Layer

Kernel event subscribers live under `src/Events/` and are autoconfigured through Symfony service discovery.

- `CslRequestClientSubscriber` listens on `KernelEvents::REQUEST` with priority `300`. It creates a request UID with UUIDv7, stores a communication client ID on the request, and starts a timer through `ClientCommunicatorInterface`.
- `CslResponseInternalSubscriber` listens on `KernelEvents::RESPONSE` with priority `100`. It can transform successful main responses and skips responses that were already marked as CSL error responses.
- `CslResponseClientSubscriber` listens on `KernelEvents::RESPONSE` with priority `50`. It logs request and response data, including communication timing.
- `CslErrorSubscriber` listens on `KernelEvents::EXCEPTION`. It logs exception details as critical events, marks the request as handled, and returns a JSON error response.
- `CslAbstractSubscriber` centralizes shared subscriber state, request-data helpers, request attribute keys, and logger access.

`CslEventsSubscriberDTO` provides subscribers with the parameter bag, validator, and CSL logger factory.

### Domain and Persistence Layer

Doctrine entities live in `src/Entity/` and are mapped through PHP attributes. Doctrine is configured in `config/packages/doctrine.yaml` with automatic mapping for the `CSL\Entity` namespace.

The current example model is `CSL\Entity\Example`:

- Mapped to the `examples` table.
- Uses an integer generated primary key.
- Stores a required `name` string with length `100`.
- Uses fluent setters.

Repositories live in `src/Repository/`:

- `CslAbstractRepository` extends Doctrine `EntityRepository` and uses PHPDoc generics for typed concrete repositories.
- `ExampleRepository` binds the base repository to `CSL\Entity\Example`.

Database schema changes are stored in `migrations/` and are executed through Doctrine Migrations.

### Service Layer

Reusable services live under `src/Service/`.

`ClientCommunicator` implements `ClientCommunicatorInterface` and tracks request communication timing by client ID. Event subscribers use it to record start time, stop time, and duration in milliseconds.

### Exception Layer

Shared exceptions live under `src/Exceptions/`.

`CslAbstractException` standardizes default messages, codes, and array serialization for API-oriented errors. Concrete exception classes include:

- `BadRequestException`
- `UnauthorizedException`
- `NotImplementedException`
- `ParameterNotFoundException`
- `ServiceUnavailableException`

## Logger Module

The custom logger module lives under `src/Module/LoggerBundle/` and builds on Monolog.

### Logger Composition

`CslLoggerFactory` creates a Monolog logger from configured handler parameters and registers Monolog's error handler through `CSL\Module\ErrorHandler\AbstractErrorHandler`.

The resulting logger is wrapped by `CslLogger`, which exposes event-focused logger helpers:

- `CslLoggerCriticalEvents`
- `CslLoggerInfoEvents`
- `CslLoggerImportedEvents`

### Handler Construction

Handlers are configured through the `handlers` parameter in environment-specific logger config files:

- `config/packages/dev/logger.yaml`
- `config/packages/test/logger.yaml`
- `config/packages/prod/logger.yaml`

Handler creation flows through:

1. `LoggerConfigurationDTO`
2. `HandlerFactory`
3. `HandlerRegistry`
4. A concrete handler builder

Supported handler builders:

- `CslStreamHandler`, exposed as the public alias `CslStreamHandler`
- `CslGelfHandlerTcp`, exposed as the public alias `CslGelfHandlerTcp`

`CslStreamHandler` writes structured JSON logs to a stream such as `php://stdout`. `CslGelfHandlerTcp` publishes logs to Graylog through GELF TCP and can ignore connection errors when configured.

### Log Data Shape

Logger DTOs separate request metadata from trace metadata:

- `CslLogRequestDataDTO` stores request body, resource URI, method, request UID, and client IPs.
- `CslLogTraceDataDTO` stores timestamp, message template, additional data, response body, message, file, line, stack trace, and code.

`CslLogFormatter` serializes Monolog records to JSON lines with stable keys used by the subscriber logging flow.

## Configuration Architecture

### Bundles

`config/bundles.php` registers:

- Symfony FrameworkBundle
- RedisServiceBundle
- DoctrineBundle
- DoctrineMigrationsBundle
- NelmioApiDocBundle
- TwigBundle
- TwigExtraBundle
- WebProfilerBundle for `dev` and `test`
- MonologBundle

### Services

`config/services.yaml` imports focused logger service definitions and sets Symfony defaults:

- autowiring enabled
- autoconfiguration enabled
- `CSL\` registered from `src/`
- `src/Entity/` and `src/Kernel.php` excluded from generic service discovery

Logger-specific service definitions are split into:

- `config/services/handler_factory.yaml`
- `config/services/logger_factory.yaml`

### Environment Variables

The main runtime variables are:

- `APP_ENV`
- `APP_SECRET`
- `APP_DEBUG`
- `DATABASE_URL`
- `REDIS_DSN`
- `DOCS_URI`

`.env.example` provides the development template. `.env.test.example` provides the test template, including `KERNEL_CLASS=CSL\Kernel`.

### Doctrine

Doctrine DBAL reads `DATABASE_URL`. ORM mapping uses attributes from `src/Entity/` with the `CSL\Entity` prefix. Production config disables automatic proxy generation and uses Symfony cache pools for Doctrine query and result caches.

### Nelmio API Documentation

Nelmio is configured in `config/packages/nelmio_api_doc.yaml`.

The active Swagger UI route is declared in `config/routes.yaml` at `/api/doc`. `config/routes/nelmio_api_doc.yaml` contains commented route examples that can be used if the docs route is moved back into the route-specific config file.

### Redis

Redis integration is provided by `uzunov-labs/redis-service` and configured through `config/packages/redis_service.yaml`. The package is resolved from the configured VCS repository in `composer.json`.

## Technology Stack

### Runtime

- PHP `>=8.4`
- Symfony `7.4.*`
- Doctrine ORM `^3.5`
- Doctrine Migrations `^3.4`
- Monolog `^3.9`
- Symfony MonologBundle `^3.10`
- NelmioApiDocBundle `^5.6`
- Twig and Twig Extra Bundle
- Ramsey UUID Doctrine
- `uzunov-labs/redis-service` `1.0.2`
- GELF PHP for Graylog transport

### Development

- PHPUnit `^12.3`
- PHPStan `^2.1`
- PHP CS Fixer `^3.87`
- Symfony BrowserKit, CSS Selector, Stopwatch, and WebProfilerBundle

## Code Quality

### Static Analysis

`phpstan.dist.neon` runs PHPStan at level `10` across:

- `bin/`
- `config/`
- `public/`
- `src/`
- `tests/`

`config/reference.php` is excluded.

### Formatting

`.php-cs-fixer.dist.php` applies the Symfony rule set, enables risky rules, enforces `declare(strict_types=1)`, uses short arrays, and removes unused imports.

The versioned Git pre-commit hook in `.githooks/pre-commit` runs PHP CS Fixer, then PHPStan, and blocks the commit when either reports issues. After both pass, it prepends the staged file list under `Unreleased` in `Release Notes.md`. Install it with `composer hooks:install`.

### Tests

PHPUnit is configured by `phpunit.dist.xml` with:

- bootstrap file `tests/bootstrap.php`
- `APP_ENV=test`
- `KERNEL_CLASS=CSL\Kernel`
- source restrictions for `src/`
- deprecation, notice, and warning failures enabled

The test suite contains unit tests for entities, repositories, event subscribers, logger components, DTOs, handlers, formatters, and services, plus functional repository tests using `KernelTestCaseBase`.

## Development Commands

```bash
composer db-migrate:next
composer db-migrate:generate
composer phpstan
composer cs-fix
composer code-fix
composer hooks:install
composer test
```

Useful validation commands before merging:

```bash
composer validate --no-check-publish
composer phpstan
composer test
vendor/bin/php-cs-fixer fix --dry-run --diff
```

## Extension Points

### Add an Endpoint

1. Create a controller under `src/Endpoints/<Area>/<UseCase>/Controller/`.
2. Extend `CslAbstractController`.
3. Add Symfony route attributes to controller methods.
4. Add request/response transformers near the endpoint when endpoint-specific transformation is needed.
5. Add unit or functional tests for behavior that is more than a thin pass-through.

### Add an Entity

1. Create the entity under `src/Entity/` with Doctrine attributes.
2. Create a repository under `src/Repository/` extending `CslAbstractRepository`.
3. Generate a migration with `composer db-migrate:generate`.
4. Apply it with `composer db-migrate:next`.
5. Add repository and entity tests where behavior is project-specific.

### Add a Logger Handler

1. Create a handler builder in `src/Module/LoggerBundle/Handler/`.
2. Implement `CslHandlerBuilderInterface`, usually by extending `CslAbstractHandlerBuilder`.
3. Expose it as a service or alias that matches the handler name resolved by `LoggerConfigurationDTO`.
4. Add handler configuration under the `handlers` parameter in each required environment.
5. Cover handler creation in unit tests.

### Add a Subscriber

1. Create the subscriber under `src/Events/`.
2. Extend `CslAbstractSubscriber` when logger and request helpers are needed.
3. Implement `getSubscribedEvents()`.
4. Keep priorities explicit when ordering matters against existing request, response, and exception subscribers.
5. Add unit tests for main-request handling, skipped paths, and side effects on the request or response.

## Security and Operational Notes

- Keep secrets in environment variables, not committed configuration.
- Validate input with Symfony Validator and typed request DTOs where applicable.
- Avoid logging sensitive values in request bodies, response bodies, and trace context.
- Configure trusted proxies and trusted hosts in deploying applications when traffic passes through load balancers or reverse proxies.
- Use Doctrine parameterization and repositories for database access.
- Review logger handler configuration before enabling GELF in environments where Graylog is unavailable.
