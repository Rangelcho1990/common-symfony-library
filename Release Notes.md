# Release Notes

## Unreleased

### Issue #20 — Map exceptions to safe HTTP error responses

- Symfony HTTP exceptions retain their status and the `WWW-Authenticate`, `Allow`, and `Retry-After` headers. CSL exceptions use their intended 400–599 status codes; unexpected failures and invalid/default CSL codes return 500.
- Error responses now use `JsonResponse` with the existing `message` and `code` fields and tolerate invalid UTF-8. Server errors return generic status text to prevent disclosure of infrastructure details; exception messages, codes, file, line, and traces remain in server logs.
- Compatibility: clients must handle meaningful error statuses instead of expecting every failure to be 500, and must not depend on raw server-error messages. Known 4xx messages remain client-visible and must contain safe text. Other exception headers are not forwarded.
- Coverage: `tests/Unit/Events/CslErrorSubscriberTest.php` checks HTTP and CSL status mapping, unexpected failures, diagnostic logging, header filtering, malformed UTF-8, and request lifecycle behavior. Validation of the implementation passed with 99 PHPUnit tests and 507 assertions, PHPStan level 10, and changed-file formatting checks.

### Issue #24 — Make logging formats explicit

- Stream logging retains the canonical CSL JSON schema; GELF retains its dedicated message formatter.
- Removed the unused `format` configuration requirement from the DTO, factory types, and YAML examples. Remove this setting from existing configurations.
- `LoggerConfigurationDTO::getFormat()` and inherited `LineFormatter` configuration APIs are no longer available. Construct `CslLogFormatter` without arguments; batch output remains newline-delimited JSON.

### Issue #23 — Correct examples migration rollback and database portability

- Rollback drops only `examples`, preserving unrelated `users` tables and data.
- Replaced MySQL-specific creation SQL with Doctrine-generated platform SQL and `addPrimaryKeyConstraint()`, and corrected the migration description.
- Fresh migrations seed `User A` with ID `1` and `User B` with ID `2`; the next generated ID is `3` with default identity settings.
- Added MySQL/PostgreSQL connection examples and included migrations in PHPStan analysis.
- Compatibility: this updates the existing migration. Databases where it already ran are not automatically reseeded or altered. New tables use a signed integer ID matching the entity mapping and platform database defaults instead of the previous explicit MySQL engine/collation settings.
- Coverage: `tests/Functional/Migrations/ExamplesMigrationTest.php` executes two up/down cycles, verifies seed rows and subsequent ID generation, and checks that unrelated user data survives. It requires a disposable `_csl_test` database without a pre-existing `users` table.
- Validation: `composer test` passed with 81 tests and 291 assertions; PHPStan and changed-file formatting checks passed. Live PostgreSQL verification remains pending because `pdo_pgsql` is unavailable locally.

## Change 21 — Isolate functional tests from non-test databases

Functional tests now use a dedicated database so schema setup cannot erase the normal development database when `.env.test` is missing.

### What changed

- Test-only Doctrine configuration appends `_csl_test` to the database name from `DATABASE_URL`.
- Added `TestDatabaseGuard` to reject schema operations outside the test environment or against unsupported connections and database names without the required suffix.
- Repository tests rebuild mapped tables once per run and roll back test data after each test.
- Updated the environment template, README, and architecture documentation with test database setup and cleanup behavior.

### Setup and compatibility

Tests reuse the existing MySQL/PostgreSQL driver; no additional PHP extension is required. Create the disposable test database once before running the suite:

```bash
php bin/console doctrine:database:create --env=test --if-not-exists
composer test
```

The database user needs schema creation/drop permissions on the test database. Concurrent suite runs require distinct base database names in `DATABASE_URL`.

### Tests

- `tests/Unit/Support/TestDatabaseGuardTest.php` covers suffix application and rejection of unsafe connections.
- `tests/Functional/Repository/ExampleRepositoryTest.php` covers repository queries and test data cleanup.
- Verified with `composer test`: 80 tests, 265 assertions passed.

## Change 26 — Release communication timers after request logging

Completed communication timers are now removed from the shared `ClientCommunicator` service so long-running workers do not retain request timing data indefinitely.

### What changed

- Added `stopAndTakeCommunicationTime()` to atomically finish, return, and remove a client timer.
- Added `clearTimer()` for lifecycle cleanup when no response timing is consumed.
- Removed the obsolete non-consuming `getCommunicationTime()` API.
- `CslResponseClientSubscriber` now consumes completed timers through the atomic operation.
- `CslRequestClientSubscriber` clears any remaining main-request timer on `kernel.finish_request`, covering exceptions and skipped response logging.
- Timer cleanup remains isolated by client ID, including when subrequests finish.

### Tests

Covered in:

- `tests/Unit/Events/CslRequestClientSubscriberTest.php`
- `tests/Unit/Events/CslResponseClientSubscriberTest.php`
- `tests/Unit/Service/ClientCommunicator/ClientCommunicatorTest.php`

## Change 17 — Preserve invalid log-level exceptions in CslStreamHandler

`CslStreamHandler` now preserves specific configuration exceptions instead of converting every handler-construction failure into a generic `RuntimeException`.

### What changed

- Removed the broad `catch (\Exception)` wrapper from `CslStreamHandler::getHandler()`.
- Invalid Monolog levels now propagate as `InvalidArgumentException`, matching `CslGelfHandlerTcp` behavior.
- Removed the unused `ParameterNotFoundException` import and the incorrect `@throws ParameterNotFoundException` annotation.
- Added regression coverage through the public `CslStreamHandler::getHandler()` path using invalid level `350`.

### Why

Callers can distinguish invalid logger configuration from other handler-construction failures, and the stream and GELF handlers now expose consistent invalid-level behavior.

### Tests

Covered in:

- `tests/Unit/Module/LoggerBundle/Handler/CslStreamHandlerTest.php`

## Change 3 — Request time tracking in the logger

This change adds request communication timing to structured logs and keeps a single request UUID for the whole request lifecycle.

### What changed

- `CslRequestClientSubscriber` starts a `ClientCommunicator` timer on main requests and stores `requestUid` (UUIDv7) plus a communication `clientId`.
- `CslResponseClientSubscriber` stops the timer and logs `communicationTime` (`startTime`, `endTime`, `durationMs`) with the info event.
- `CslErrorSubscriber` reuses the same request UUID, marks only main requests with `_csl_error_handled`, and returns a JSON error response.
- Request attribute keys are shared constants on `CslAbstractSubscriber`.

### Why

Logs can be correlated by `requestUid`, and each request records how long client communication took.

### Tests

Covered in:

- `tests/Unit/Events/CslErrorSubscriberTest.php`
- `tests/Unit/Events/CslRequestClientSubscriberTest.php`
- `tests/Unit/Events/CslResponseClientSubscriberTest.php`
- `tests/Unit/Service/ClientCommunicator/ClientCommunicatorTest.php`

## Change 9 — API docs URL not working

Kernel subscribers were rewriting and logging Nelmio Swagger UI, Swagger JSON, and Symfony profiler/toolbar responses. That broke `/api/doc` and related framework pages.

### What changed

- `CslAbstractSubscriber` now skips docs and profiler traffic through `isDocsRequest()`, covering Swagger UI/JSON/YAML routes, `nelmio_api_doc.*`, `_wdt`, and `_profiler*`.
- `CslRequestClientSubscriber`, `CslResponseClientSubscriber`, `CslResponseInternalSubscriber`, and `CslErrorSubscriber` return early for those routes so docs HTML is not transformed into application JSON and is not treated as client communication.
- `ExampleController` uses `#[Route]` attributes plus OpenAPI `#[OA\Response]` / `#[OA\Tag]` metadata so Nelmio can document `GET /example`.
- Web profiler collection is enabled in the `dev` and `test` profiler config.

### Why

`/api/doc` and the Symfony toolbar can render without being overwritten by response transformers or exception handling.

### Tests

Covered in:

- `tests/Unit/Events/CslRequestClientSubscriberTest.php`
- `tests/Unit/Events/CslResponseInternalSubscriberTest.php`

## Summary

This branch expands PHPUnit coverage around the current Symfony library behavior, especially the logger bundle, event subscribers, repositories, entities, and client communication timing. It also includes small implementation refinements needed to make the tested behavior explicit and reliable.

## Highlights

- Added PHPUnit tests for logger event groups, logger factory behavior, handler builders, handler registry resolution, formatter output, DTO payload preparation, event subscribers, repositories, entities, and the client communicator service.
- Refined logger handler naming by replacing `CslHandlerInterface` with `CslHandlerBuilderInterface`, making the interface purpose clearer.
- Moved `CslEventsSubscriberDTO` into `CSL\Events\DTO` and `LoggerConfigurationDTO` into `CSL\Module\LoggerBundle\DTO` to align DTOs with their owning modules.
- Updated logger handler builders to expose logger configuration through `getLoggerConfiguration()` and validate Monolog log levels with `Level::tryFrom()`.
- Updated `CslStreamHandler` to preserve invalid log-level exceptions instead of wrapping them in a generic runtime exception.
- Updated `CslLogFormatter` to fall back to the record message when no context message is provided, and to use the numeric Monolog level value as the default code.
- Improved GELF TCP handler behavior by validating missing ports, using the shared logger configuration accessor, and preserving optional connection error handling.
- Updated PHP CS Fixer configuration to exclude `config/reference.php`.

## PHPUnit Coverage Added

- Logger bundle:
  - `CslLogger`
  - `CslLoggerCriticalEvents`
  - `CslLoggerImportedEvents`
  - `CslLoggerInfoEvents`
  - `CslLoggerFactory`
  - `CslLogRequestDataDTO`
  - `CslLogTraceDataDTO`
  - `CslAbstractHandlerBuilder`
  - `CslGelfHandlerTcp`
  - `CslStreamHandler`
  - `HandlerFactory`
  - `HandlerRegistry`
  - `CslLogFormatter`
  - `GelfHandlerFormatter`
- Event subscribers:
  - `CslErrorSubscriber`
  - `CslRequestClientSubscriber`
  - `CslResponseClientSubscriber`
  - `CslResponseInternalSubscriber`
- Domain and infrastructure:
  - `Example` entity
  - `CslAbstractRepository`
  - `ExampleRepository`
  - `ClientCommunicator`

## Behavior Notes

- Main request event handling is now covered for request UID reuse, client ID reuse, response logging, error response creation, and response transformation bypass rules.
- Client communication timing is covered for unknown clients, stop-without-start behavior, start time storage, stop time storage, duration calculation, and independent timers per client ID.
- Handler registry tests now verify cached registered handlers, lazy container lookup, and type validation when container services do not implement `CslHandlerBuilderInterface`.
- Stream handler tests now verify that invalid Monolog levels remain `InvalidArgumentException` instances through the public handler-construction path.
- Formatter tests now verify GELF message construction, truncation behavior, empty-message fallback when JSON encoding fails, and concrete `CslLogFormatter` instantiation.

## Compatibility Notes

- Namespaces changed for the event subscriber DTO and logger configuration DTO. Any external usage of the old namespaces should be updated:
  - `CSL\DTO\Events\CslEventsSubscriberDTO` -> `CSL\Events\DTO\CslEventsSubscriberDTO`
  - `CSL\DTO\Logger\LoggerConfigurationDTO` -> `CSL\Module\LoggerBundle\DTO\LoggerConfigurationDTO`
- The handler contract changed from `CslHandlerInterface` to `CslHandlerBuilderInterface`.
- `HandlerRegistryInterface` no longer exposes `hasHandler()`.
- `CslStreamHandler::getHandler()` now preserves `InvalidArgumentException` for invalid log levels; callers that previously caught the generic wrapper should handle the specific exception.

## Changed Files Overview

- 38 files changed against `master`.
- 1,124 lines added and 96 lines removed.
- Main code updates are concentrated in logger handlers, handler registry/factory contracts, event DTO namespaces, logger configuration DTO namespace, and log formatting.
- Test updates add broad unit coverage across logger, event, repository, entity, and service components.
