<?php

declare(strict_types=1);

namespace CSL\Events;

use CSL\Exceptions\CslAbstractException;
use CSL\Module\LoggerBundle\DTO\CslLogRequestDataDTO;
use CSL\Module\LoggerBundle\DTO\CslLogTraceDataDTO;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class CslErrorSubscriber extends CslAbstractSubscriber
{
    public function onKernelException(ExceptionEvent $event): void
    {
        if ($this->isDocsRequest($event->getRequest())) {
            return;
        }

        if ($event->isMainRequest()) {
            $event->getRequest()->attributes->set(self::CSL_ERROR_HANDLED, true);
        }

        $cslLogRequestDataDTO = new CslLogRequestDataDTO();
        $cslLogRequestDataDTO->prepareLogRequestData(
            $event->getRequest()->request->all(),
            $event->getRequest()->getRequestUri(),
            $event->getRequest()->getMethod(),
            $this->getRequestUid($event),
            $event->getRequest()->getClientIps(),
        );

        $exception = $event->getThrowable();
        $cslLogTraceDataDTO = new CslLogTraceDataDTO();
        $cslLogTraceDataDTO->prepareLogTraceData(
            'Error',
            null,
            null,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTrace(),
            $exception->getCode()
        );

        $this->cslLogger->getCriticalEvents()->logError($cslLogRequestDataDTO, $cslLogTraceDataDTO);
        unset($cslLogRequestDataDTO, $cslLogTraceDataDTO);

        $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        $headers = [];
        if ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();
            // Preserve error protocol semantics without forwarding cookies, redirects,
            // diagnostic headers, or headers that conflict with our JSON body.
            foreach ($exception->getHeaders() as $name => $value) {
                if (in_array(strtolower($name), ['www-authenticate', 'allow', 'retry-after'], true)) {
                    $headers[$name] = $value;
                }
            }
        } elseif ($exception instanceof CslAbstractException && $exception->getCode() >= 400 && $exception->getCode() <= 599) {
            $status = $exception->getCode();
        }

        $message = Response::$statusTexts[$status] ?? 'Internal Server Error';
        if ($status >= 400 && $status < 500 && '' !== $exception->getMessage()) {
            $message = $exception->getMessage();
        }

        $response = new JsonResponse(null, $status, $headers);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_INVALID_UTF8_SUBSTITUTE);
        $response->setData(['message' => $message, 'code' => $status]);
        $event->setResponse($response);
    }

    /**
     * @codeCoverageIgnore
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException'],
        ];
    }

    private function getRequestUid(ExceptionEvent $event): UuidInterface
    {
        $requestUid = $event->getRequest()->attributes->get(self::REQUEST_UID);
        if ($requestUid instanceof UuidInterface) {
            return $requestUid;
        }

        $requestUid = Uuid::uuid7();
        $event->getRequest()->attributes->set(self::REQUEST_UID, $requestUid);

        return $requestUid;
    }
}
