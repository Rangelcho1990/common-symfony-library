<?php

declare(strict_types=1);

namespace CSL\Events;

use CSL\Events\DTO\CslEventsSubscriberDTO;
use CSL\Module\LoggerBundle\CslLogger\CslLogger;
use CSL\Module\Traits\RequestDataTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class CslAbstractSubscriber implements EventSubscriberInterface
{
    use RequestDataTrait;

    protected const REQUEST_UID = 'requestUid';
    protected const CSL_ERROR_HANDLED = '_csl_error_handled';
    protected const CLIENT_ID = 'clientId';

    private const DOCS_ROUTES = [
        '_wdt' => true,
        '_wdt_stylesheet' => true,
        'app.swagger' => true,
        'app.swagger_ui' => true,
        'nelmio_api_doc.swagger_ui' => true,
        'nelmio_api_doc.swagger' => true,
        'nelmio_api_doc.swagger_json' => true,
        'nelmio_api_doc.swagger_yaml' => true,
        'nelmio_api_doc.controller.swagger_ui' => true,
    ];

    protected CslEventsSubscriberDTO $cslEventsSubscriberDTO;
    protected CslLogger $cslLogger;

    public function __construct(CslEventsSubscriberDTO $cslEventsSubscriberDTO)
    {
        $this->cslEventsSubscriberDTO = $cslEventsSubscriberDTO;
        $this->cslLogger = $cslEventsSubscriberDTO->getCslLogger();
    }

    protected function isDocsRequest(Request $request): bool
    {
        $route = $request->attributes->get('_route');

        return is_string($route)
            && (isset(self::DOCS_ROUTES[$route])
                || str_starts_with($route, '_profiler')
                || str_starts_with($route, 'nelmio_api_doc.'));
    }
}
