<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\LoggerFormatters;

use Gelf\Message;
use Monolog\Formatter\GelfMessageFormatter;
use Monolog\LogRecord;

class GelfHandlerFormatter extends GelfMessageFormatter implements GelfHandlerFormatterInterface
{
    public function format(LogRecord $record): Message
    {
        $recordData = $record->toArray();

        $data = $recordData['context'];
        $data['message'] = $recordData['message'];

        $text = json_encode($data);
        if (false === $text) {
            $text = '';
        }

        $messageText = substr($text, 0, $this->maxLength);
        $message = parent::format($record);
        $message->setShortMessage($messageText);
        $message->setFullMessage($messageText);
        unset($recordData, $data, $text, $messageText);

        return $message;
    }
}
