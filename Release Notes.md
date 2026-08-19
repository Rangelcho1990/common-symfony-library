# Release Notes

## Unreleased

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
- Formatter tests now verify GELF message construction, truncation behavior, empty-message fallback when JSON encoding fails, and concrete `CslLogFormatter` instantiation.

## Compatibility Notes

- Namespaces changed for the event subscriber DTO and logger configuration DTO. Any external usage of the old namespaces should be updated:
  - `CSL\DTO\Events\CslEventsSubscriberDTO` -> `CSL\Events\DTO\CslEventsSubscriberDTO`
  - `CSL\DTO\Logger\LoggerConfigurationDTO` -> `CSL\Module\LoggerBundle\DTO\LoggerConfigurationDTO`
- The handler contract changed from `CslHandlerInterface` to `CslHandlerBuilderInterface`.
- `HandlerRegistryInterface` no longer exposes `hasHandler()`.

## Changed Files Overview

- 38 files changed against `master`.
- 1,124 lines added and 96 lines removed.
- Main code updates are concentrated in logger handlers, handler registry/factory contracts, event DTO namespaces, logger configuration DTO namespace, and log formatting.
- Test updates add broad unit coverage across logger, event, repository, entity, and service components.
