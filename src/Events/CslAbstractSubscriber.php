<?php

declare(strict_types=1);

namespace CSL\Events;

use CSL\DTO\Events\CslEventsSubscriberDTO;
use CSL\DTO\Logger\CslLoggerDTO;
use CSL\Module\LoggerBundle\CslLogger;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class CslAbstractSubscriber implements EventSubscriberInterface
{
    protected CslEventsSubscriberDTO $cslEventsSubscriberDTO;
    protected CslLogger $cslLogger;
    protected ?UuidInterface $requestUid = null;

    public function __construct(CslEventsSubscriberDTO $cslEventsSubscriberDTO, CslLoggerDTO $cslLoggerDTO)
    {
        $this->cslEventsSubscriberDTO = $cslEventsSubscriberDTO;
        $this->cslLogger              = $cslLoggerDTO->getCslLogger();

        $this->requestUid = Uuid::uuid1();
    }
}
