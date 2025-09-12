<?php

declare(strict_types=1);

namespace CSL\Events;

use CSL\DTO\Events\CslEventsSubscriberDTO;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class CslAbstractSubscriber implements EventSubscriberInterface
{
    protected CslEventsSubscriberDTO $cslEventsSubscriberDTO;
    protected ?UuidInterface $requestUid = null;

    public function __construct(CslEventsSubscriberDTO $cslEventsSubscriberDTO)
    {
        $this->cslEventsSubscriberDTO = $cslEventsSubscriberDTO;

        $this->requestUid = Uuid::uuid1();
    }
}
