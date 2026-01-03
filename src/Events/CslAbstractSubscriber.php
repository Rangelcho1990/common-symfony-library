<?php

declare(strict_types=1);

namespace CSL\Events;

use CSL\DTO\Events\CslEventsSubscriberDTO;
use CSL\Module\LoggerBundle\CslLogger\CslLogger;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class CslAbstractSubscriber implements EventSubscriberInterface
{
    protected CslEventsSubscriberDTO $cslEventsSubscriberDTO;
    protected CslLogger $cslLogger;
    protected ?UuidInterface $requestUid = null;

    public function __construct(CslEventsSubscriberDTO $cslEventsSubscriberDTO)
    {
        $this->cslEventsSubscriberDTO = $cslEventsSubscriberDTO;
        $this->cslLogger = $cslEventsSubscriberDTO->getCslLogger();

        $this->requestUid = Uuid::uuid1();
    }
}
