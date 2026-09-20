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

- `CslRequestClientSubscriber` listens on `KernelEvents::REQUEST` with priority `31` and `KernelEvents::FINISH_REQUEST` with priority `-100`. It creates a request UID with UUIDv7, stores a communication client ID on the request, starts a timer through `ClientCommunicatorInterface`, and clears any timer that remains when the main request finishes.
- `CslResponseInternalSubscriber` listens on `KernelEvents::RESPONSE` with priority `100`. It can transform successful main responses and skips responses that were already marked as CSL error responses.
- `CslResponseClientSubscriber` listens on `KernelEvents::RESPONSE` with priority `50`. It atomically stops, consumes, and removes the communication timer before logging request and response data.
- `CslErrorSubscriber` listens on `KernelEvents::EXCEPTION` with the default priority `0`. It logs exception details as critical events, marks the request as handled, and returns a JSON error response.
- `CslAbstractSubscriber` centralizes shared subscriber state, request-data helpers, request attribute keys, and logger access.

`CslEventsSubscriberDTO` provides subscribers with the parameter bag, validator, and shared `CslLoggerInterface` service.

#### Subscriber Priority Order

The intended CSL flow follows the request from the client to an internal service and the response back to the client. The stages below are in lifecycle order; priority numbers only control listener order **within the same Symfony event**.

| Stage | Subscriber | Responsibility in the intended flow | Current implementation | Priority |
| ---: | --- | --- | --- | --- |
| 1 | `CslRequestClientSubscriber` | Receive the request from the client and initialize request tracking. | Implemented on `kernel.request`. Initializes request/client IDs and starts timing for normal main requests; excludes documentation, profiler, and toolbar routes. | **31** — after Symfony routing at **32**, so `_route` is available for exclusions. |
| 2 | `CslRequestInternalSubscriber` (planned) | Transform the client request from stage 1 and send it to the internal service. | Not implemented. No event or priority has been registered. | **Not assigned. Proposed: 30** if registered on `kernel.request`, to run after stage 1 at **31**. |
| 3 | `CslResponseInternalSubscriber` | Receive the internal-service response from stage 2 and transform it into the required structure. | Currently runs on `kernel.response` and applies `ExampleTransformer` to eligible main responses. The stage-2 internal-service integration is not implemented yet. | **100** — before client response logging at **50**, so logging sees the transformed response. |
| 4 | `CslResponseClientSubscriber` | Complete processing of the response that will be returned to the client. | Implemented on `kernel.response`. Consumes the communication timer and logs request/response data. Symfony sends the resulting response to the client. | **50** — after internal transformation at **100**, so timing and logging include that stage. |

**Why these priorities?** Symfony runs higher numbers first within one event. The ordering constraints matter more than the exact numbers:

- **31 for stage 1:** Symfony's router runs at **32**. Choosing the next lower integer makes route-name exclusions possible while keeping tracking early in the request event. The previous **300** ran before routing and caused the documentation-timing bug. Other lower values could run after routing, but might also move initialization behind additional listeners.
- **30 proposed for stage 2:** this is the next lower integer after **31**, so tracking is initialized before transforming and forwarding the request. This is a documentation proposal only; the eventual implementation must also consider other application listeners and how the internal-service response enters Symfony's response lifecycle.
- **100 for stage 3 and 50 for stage 4:** these are the existing application priorities. Their useful property is **100 > 50**, which guarantees internal transformation before client response logging. There is no special Symfony meaning to these two numbers, and the gap of **50** does not represent elapsed time; it leaves room for other response listeners between them. No change to these priorities is needed for issue #27.

Priorities are not global stage numbers: request priority **31** executes before response priority **100** because Symfony dispatches the request event first. The proposed **30** does not mean stage 2 will run after response priority **50**.


Intended flow: **client → request tracking → internal request transformation and dispatch (planned) → internal response transformation → client response**.

The current normal flow is routing → request tracking → controller → internal response transformation → client response logging → timer cleanup. Stage 2 is a planned extension, not an existing internal-service call. Response priorities **100 → 50** do not run before request priority **31**: `kernel.request` and `kernel.response` are separate events dispatched at different lifecycle stages.

Supporting listeners sit outside the four main stages:

- Symfony `RouterListener::onKernelRequest()` runs at request priority **32**, before stage 1, to populate `_route` for route exclusions. Tracking starts after routing, so timing excludes route resolution.
- `CslErrorSubscriber::onKernelException()` runs at the default exception priority **0** when an exception is raised. It logs the exception and provides a JSON error response for non-excluded routes. Response processing can then continue; stage 3 skips CSL error responses.
- `CslRequestClientSubscriber::onKernelFinishRequest()` runs at finish-request priority **-100** and clears any remaining main-request timer.

An earlier listener can stop event propagation. Routing failures or early responses that bypass stage 1 have no timer; error/response logging tolerates missing IDs and timers. Subrequests do not initialize or clean up main-request timers.

Inspect registered listeners with `php bin/console debug:event-dispatcher kernel.request --env=dev` (replace the event name to inspect response, exception, or finish-request listeners).

#### Communication Timer Lifecycle

Communication timer ownership follows the complete kernel request lifecycle:

1. `kernel.request`: `CslRequestClientSubscriber` starts a timer after routing for a normal main request, excluding documentation, profiler, and toolbar routes.
2. `kernel.response`: `CslResponseClientSubscriber` calls `stopAndTakeCommunicationTime()` to finish, consume, and remove the timer used by structured response logging.
3. `kernel.finish_request`: `CslRequestClientSubscriber` calls `clearTimer()` as a fallback for any timer that was not consumed.

The finish-request cleanup belongs to `CslRequestClientSubscriber` because that subscriber creates the timer. Keeping timer creation and guaranteed cleanup together gives one component ownership of the resource lifecycle. The response subscriber owns only successful response-time consumption and logging.

Symfony dispatches `kernel.finish_request` after normal response handling and when an exception does not produce a response. The fallback therefore also covers exceptional requests, skipped response logging, and logging failures without retaining timer state in long-running workers.

Finish cleanup is restricted to main requests. A subrequest can inherit request attributes, including the communication client ID, so allowing subrequest cleanup could remove the still-active timer belonging to its parent request.

Registering `kernel.finish_request` on `CslResponseClientSubscriber` would be technically valid because it has the same communicator dependency. It is not used here because that would make a response-focused subscriber responsible for requests that may never reach `kernel.response`. If timer lifecycle management grows more complex, a dedicated communication-timer lifecycle subscriber would be the clearest separation.

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

The examples migration builds its table with Doctrine schema objects and queues
platform-generated creation SQL before inserting `User A` and `User B`. Fresh
databases assign IDs 1 and 2 with default identity settings. Rollback drops only
`examples`. MySQL and PostgreSQL connection examples are provided; changing this
existing migration does not reseed databases where it has already run.

### Service Layer

Reusable services live under `src/Service/`.

`ClientCommunicator` implements `ClientCommunicatorInterface` and tracks request communication timing by client ID. Event subscribers use it to record start time, stop time, and duration in milliseconds. Completed timing data is consumed through `stopAndTakeCommunicationTime()`, while `clearTimer()` removes unconsumed state.

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

`CslLoggerFactory` creates the dedicated `csl.logger` service from configured handler parameters. Dependency injection shares this logger and its `CslLoggerInterface` wrapper across subscribers. CSL does not replace Symfony's Monolog handlers or register process-global error handlers; Symfony retains responsibility for global error handling. The dedicated logger is reset with the kernel to support long-running workers.

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
- `CslLogTraceDataDTO` stores timestamp, message template, communication timing, response body, message, file, line, stack trace, and code.

`CslLogFormatter` serializes Monolog records to a canonical JSON schema with stable keys used by the subscriber logging flow, one record per line. Stream handlers use this formatter; GELF handlers use `GelfHandlerFormatter` and the GELF message schema. Neither handler supports configurable format templates.

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

In the test environment, DBAL appends `_csl_test` to the configured database name,
including when `.env.test` is absent. Tests use the existing MySQL or PostgreSQL
driver and a disposable database created with
`php bin/console doctrine:database:create --env=test --if-not-exists`.

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
- `migrations/`
- `public/`
- `src/`
- `tests/`

`config/reference.php` is excluded.

### Formatting

`.php-cs-fixer.dist.php` applies the Symfony rule set, enables risky rules, enforces `declare(strict_types=1)`, uses short arrays, and removes unused imports.

The versioned Git pre-commit hook in `.githooks/pre-commit` runs PHP CS Fixer, then PHPStan, then a headless Cursor Agent review, and blocks the commit when any of those steps reports issues. After they pass, it prepends the staged file list under `Unreleased` in `Release Notes.md`. Install it with `composer hooks:install`.

The agent review uses the Cursor Agent CLI (`agent` or `cursor-agent`) with the prompt in `.githooks/pre-commit-review-prompt.md`. It inspects the staged diff like a pull request review, checks that `ARCHITECTURE.md` still matches the change, writes a numbered review file under `.docs/`, and fails the commit on inaccurate architecture docs or P0/P1 findings. Skip only the agent step with `GIT_PRE_COMMIT_SKIP_AGENT=1`.

### Tests

PHPUnit is configured by `phpunit.dist.xml` with:

- bootstrap file `tests/bootstrap.php`
- `APP_ENV=test`
- `KERNEL_CLASS=CSL\Kernel`
- source restrictions for `src/`
- deprecation, notice, and warning failures enabled

The test suite contains unit tests for entities, repositories, event subscribers, logger components, DTOs, handlers, formatters, and services, plus functional repository tests using `KernelTestCaseBase`.

`ExampleRepositoryTest` checks its connection through `tests/Support/TestDatabaseGuard.php`
before schema operations. The guard requires the test environment, a supported
MySQL/PostgreSQL driver, and a nonempty base database name ending in `_csl_test`;
custom drivers and primary/replica configurations are rejected. The repository
test class rebuilds mapped tables once per process and rolls back a transaction
after each test. Concurrent suite runs require distinct base database names.

`ExamplesMigrationTest` uses the same database guard and runs two up/down cycles
through Doctrine's migration executor. It checks seeded rows, subsequent generated
IDs, and preservation of an unrelated `users` table. Migration metadata storage is
stubbed so application migration history is unchanged. The test requires no
pre-existing `users` table and removes `examples` during setup and cleanup.

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
