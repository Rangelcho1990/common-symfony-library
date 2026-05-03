<?php

declare(strict_types=1);

namespace CSL\Events;

use CSL\Events\DTO\CslEventsSubscriberDTO;
use CSL\Module\LoggerBundle\CslLogger\CslLogger;
use CSL\Module\Traits\RequestDataTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class CslAbstractSubscriber implements EventSubscriberInterface
{
    use RequestDataTrait;

    protected const REQUEST_UID = 'requestUid';
    protected const CSL_ERROR_HANDLED = '_csl_error_handled';
    protected const CLIENT_ID = 'clientId';

    protected CslEventsSubscriberDTO $cslEventsSubscriberDTO;
    protected CslLogger $cslLogger;

    public function __construct(CslEventsSubscriberDTO $cslEventsSubscriberDTO)
    {
        $this->cslEventsSubscriberDTO = $cslEventsSubscriberDTO;
        $this->cslLogger = $cslEventsSubscriberDTO->getCslLogger();
    }
}
