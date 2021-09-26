<?php

namespace App\MessageHandler;

use App\Message\RecevieQueueMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ReceiveQueueMessageHandler implements MessageHandlerInterface
{
    /**
     * @var MessageHandlerInterface
     */
    private $messageBus;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(RecevieQueueMessage $queueMessage)
    {
        $getData = $queueMessage->getMessage();
        $data = implode(',',$getData);
        $this->logger->info($data);
    }

}