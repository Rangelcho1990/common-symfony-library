<?php

declare(strict_types=1);

namespace CSL\Module\Traits;

use CSL\Module\LoggerBundle\DTO\CslLogRequestDataDTO;
use CSL\Module\LoggerBundle\DTO\CslLogTraceDataDTO;
use Symfony\Component\HttpFoundation\Request;

trait RequestDataTrait
{
    /**
     * @return array<mixed>
     */
    public function getRequestQueryData(Request $request): array
    {
        try {
            return $request->query->all();
        } catch (\Exception $e) {
            $cslLogRequestDataDTO = new CslLogRequestDataDTO();
            $cslLogRequestDataDTO->prepareLogRequestData(
                [],
                $request->getRequestUri(),
                $request->getMethod(),
                null,
                $request->getClientIps(),
            );

            $cslLogTraceDataDTO = new CslLogTraceDataDTO();
            $cslLogTraceDataDTO->prepareLogTraceData(
                'Warning',
                null,
                null,
                $e->getMessage(),
                __FILE__,
                __LINE__,
                $e->getTrace(),
                $e->getCode()
            );

            $this->cslLogger->getCriticalEvents()->logWarning($cslLogRequestDataDTO, $cslLogTraceDataDTO);
            unset($cslLogRequestDataDTO, $cslLogTraceDataDTO);

            return [];
        }
    }

    /**
     * @return array<mixed>
     */
    public function getRequestRawData(Request $request): array
    {
        try {
            return $request->getPayload()->all();
        } catch (\Exception $e) {
            $cslLogRequestDataDTO = new CslLogRequestDataDTO();
            $cslLogRequestDataDTO->prepareLogRequestData(
                [],
                $request->getRequestUri(),
                $request->getMethod(),
                null,
                $request->getClientIps(),
            );

            $cslLogTraceDataDTO = new CslLogTraceDataDTO();
            $cslLogTraceDataDTO->prepareLogTraceData(
                'Warning',
                null,
                null,
                $e->getMessage(),
                __FILE__,
                __LINE__,
                $e->getTrace(),
                $e->getCode()
            );

            $this->cslLogger->getCriticalEvents()->logWarning($cslLogRequestDataDTO, $cslLogTraceDataDTO);
            unset($cslLogRequestDataDTO, $cslLogTraceDataDTO);

            return [];
        }
    }

    public function getQueryAsString(Request $request, string $method): string
    {
        try {
            if ('GET' === $method) {
                return $request->getQueryString() ?? '';
            }

            return $request->getContent();
        } catch (\Exception $e) {
            $cslLogRequestDataDTO = new CslLogRequestDataDTO();
            $cslLogRequestDataDTO->prepareLogRequestData(
                [],
                $request->getRequestUri(),
                $request->getMethod(),
                null,
                $request->getClientIps(),
            );

            $cslLogTraceDataDTO = new CslLogTraceDataDTO();
            $cslLogTraceDataDTO->prepareLogTraceData(
                'Warning',
                null,
                null,
                $e->getMessage(),
                __FILE__,
                __LINE__,
                $e->getTrace(),
                $e->getCode()
            );

            $this->cslLogger->getCriticalEvents()->logWarning($cslLogRequestDataDTO, $cslLogTraceDataDTO);
            unset($cslLogRequestDataDTO, $cslLogTraceDataDTO);

            return '';
        }
    }
}
