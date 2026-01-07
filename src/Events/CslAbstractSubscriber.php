<?php

declare(strict_types=1);

namespace CSL\Events;

use CSL\DTO\Events\CslEventsSubscriberDTO;
use CSL\Module\LoggerBundle\CslLogger\CslLogger;
use CSL\Module\Traits\RequestDataTrait;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class CslAbstractSubscriber implements EventSubscriberInterface
{
    use RequestDataTrait;

    protected CslEventsSubscriberDTO $cslEventsSubscriberDTO;
    protected CslLogger $cslLogger;
    protected ?UuidInterface $requestUid = null;
    protected ?string $clientId = null;

    public function __construct(CslEventsSubscriberDTO $cslEventsSubscriberDTO)
    {
        $this->cslEventsSubscriberDTO = $cslEventsSubscriberDTO;
        $this->cslLogger = $cslEventsSubscriberDTO->getCslLogger();

        $this->requestUid = Uuid::uuid1();
        $this->clientId = 'Communication_'.$this->requestUid;
    }
}
