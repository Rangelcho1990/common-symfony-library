<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Events;

use CSL\Events\CslErrorSubscriber;
use CSL\Events\DTO\CslEventsSubscriberDTO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class CslErrorSubscriberTest extends TestCase
{
    public function testSameRequestGetsIdenticalRequestUidAcrossRepeatedExceptionHandlingCalls(): void
    {
        $subscriber = $this->createSubscriber();
        $kernel = $this->createStub(HttpKernelInterface::class);

        $request = new Request();

        $event1 = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            new \RuntimeException('boom 1')
        );

        $subscriber->onKernelException($event1);

        $uid1 = $request->attributes->get('requestUid');
        self::assertNotNull($uid1, 'requestUid should be set on first exception handling call');

        $event2 = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            new \RuntimeException('boom 2')
        );

        $subscriber->onKernelException($event2);

        $uid2 = $request->attributes->get('requestUid');
        self::assertNotNull($uid2, 'requestUid should still be set on second exception handling call');

        self::assertSame($uid1, $uid2, 'Same Request should keep the same requestUid instance across calls');
    }

    public function testSubRequestsDoNotGetCslErrorHandledSet(): void
    {
        $subscriber = $this->createSubscriber();
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = new Request();

        $event = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::SUB_REQUEST,
            new \RuntimeException('boom')
        );

        $subscriber->onKernelException($event);

        self::assertFalse(
            $request->attributes->getBoolean('_csl_error_handled'),
            '_csl_error_handled must not be set for sub-requests'
        );
    }

    public function testMainRequestDoesGetCslErrorHandledSet(): void
    {
        $subscriber = $this->createSubscriber();
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = new Request();

        $event = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            new \RuntimeException('boom')
        );

        $subscriber->onKernelException($event);

        self::assertTrue(
            $request->attributes->getBoolean('_csl_error_handled'),
            '_csl_error_handled must be set for main requests'
        );
    }

    public function testKernelExceptionSetsJsonResponseWith500StatusAndMessage(): void
    {
        $subscriber = $this->createSubscriber();
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = new Request();

        $event = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            new \RuntimeException('boom', 123)
        );

        $subscriber->onKernelException($event);

        $response = $event->getResponse();
        self::assertInstanceOf(Response::class, $response);
        self::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        self::assertSame('application/json', $response->headers->get('content-type'));

        $decoded = json_decode((string) $response->getContent(), true);
        self::assertIsArray($decoded);
        self::assertSame('Internal Server Error', $decoded['message'] ?? null);
        self::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $decoded['code'] ?? null);
    }

    /** @return iterable<string, array{\Throwable, int, string}> */
    public static function exceptionCases(): iterable
    {
        yield 'HTTP bad request' => [new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Invalid input'), 400, 'Invalid input'];
        yield 'HTTP unauthorized' => [new \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException('Bearer', 'Login required'), 401, 'Login required'];
        yield 'HTTP not found' => [new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(), 404, 'Not Found'];
        yield 'CSL bad request' => [new \CSL\Exceptions\BadRequestException(), 400, 'Bad Request'];
        yield 'CSL unauthorized' => [new \CSL\Exceptions\UnauthorizedException(), 401, 'Unauthorized'];
        yield 'CSL missing parameter' => [new \CSL\Exceptions\ParameterNotFoundException(), 404, 'Missing parameter'];
        yield 'CSL unavailable' => [new \CSL\Exceptions\ServiceUnavailableException('Database password'), 503, 'Service Unavailable'];
        yield 'CSL not implemented' => [new \CSL\Exceptions\NotImplementedException('Private implementation'), 501, 'Not Implemented'];
        yield 'CSL default code' => [new \CSL\Exceptions\CslAbstractException('Private details'), 500, 'Internal Server Error'];
        yield 'CSL invalid code' => [new \CSL\Exceptions\CslAbstractException('Private details', 200), 500, 'Internal Server Error'];
        yield 'CSL custom status' => [new \CSL\Exceptions\CslAbstractException('Conflict', 409), 409, 'Conflict'];
        yield 'HTTP server failure' => [new \Symfony\Component\HttpKernel\Exception\HttpException(500, 'SQL connection failed'), 500, 'Internal Server Error'];
        yield 'unexpected exception with HTTP-like code' => [new \RuntimeException('SQL connection failed', 404), 500, 'Internal Server Error'];
        yield 'unexpected error' => [new \Error('Private filesystem path'), 500, 'Internal Server Error'];
        yield 'invalid UTF-8' => [new \CSL\Exceptions\BadRequestException("Invalid \xB1"), 400, "Invalid \u{FFFD}"];
    }

    #[DataProvider('exceptionCases')]
    public function testExceptionMapping(\Throwable $exception, int $status, string $message): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with('Error', self::callback(
            static function (mixed $context) use ($exception): bool {
                self::assertIsArray($context);
                self::assertSame($exception->getMessage(), $context['message']);
                self::assertSame($exception->getCode(), $context['code']);
                self::assertSame($exception->getFile(), $context['file']);
                self::assertSame($exception->getLine(), $context['line']);
                self::assertSame($exception->getTrace(), $context['stackTrace']);

                return true;
            }
        ));
        $event = new ExceptionEvent($this->createStub(HttpKernelInterface::class), new Request(), HttpKernelInterface::MAIN_REQUEST, $exception);
        $this->createSubscriber($logger)->onKernelException($event);

        $response = $event->getResponse();
        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame($status, $response->getStatusCode());
        self::assertSame('application/json', $response->headers->get('Content-Type'));
        self::assertSame(['message' => $message, 'code' => $status], json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }

    public function testOnlySafeHttpExceptionHeadersArePreserved(): void
    {
        $exception = new \Symfony\Component\HttpKernel\Exception\HttpException(503, 'Private details', null, [
            'WWW-Authenticate' => ['Bearer', 'Basic realm="api"'],
            'Allow' => 'GET, POST',
            'rEtRy-AfTeR' => '120',
            'Content-Type' => 'text/html',
            'Content-Length' => '9999',
            'Set-Cookie' => 'secret=value',
            'Location' => 'https://example.com',
            'X-Debug' => 'Private details',
        ]);
        $event = new ExceptionEvent($this->createStub(HttpKernelInterface::class), new Request(), HttpKernelInterface::MAIN_REQUEST, $exception);
        $this->createSubscriber()->onKernelException($event);

        $response = $event->getResponse();
        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(503, $response->getStatusCode());
        self::assertSame(['Bearer', 'Basic realm="api"'], $response->headers->all('WWW-Authenticate'));
        self::assertSame('GET, POST', $response->headers->get('Allow'));
        self::assertSame('120', $response->headers->get('Retry-After'));
        self::assertSame('application/json', $response->headers->get('Content-Type'));
        foreach (['Content-Length', 'Set-Cookie', 'Location', 'X-Debug'] as $name) {
            self::assertFalse($response->headers->has($name));
        }
    }

    private function createSubscriber(?LoggerInterface $psrLogger = null): CslErrorSubscriber
    {
        $psrLogger ??= $this->createStub(LoggerInterface::class);

        $dto = $this->createStub(CslEventsSubscriberDTO::class);
        $dto->method('getCslLogger')->willReturn(new \CSL\Module\LoggerBundle\CslLogger\CslLogger($psrLogger));

        return new CslErrorSubscriber($dto);
    }
}
