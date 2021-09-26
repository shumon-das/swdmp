<?php

namespace App\Message;

class RecevieQueueMessage
{
    /**
     * @var array
     */
    private $message;

    public function __construct(array $message)
    {
        $this->message = $message;
    }

    /**
     * @param array $message
     */
    public function setMessage(array $message): void
    {
        $this->message = $message;
    }

    /**
     * @return array
     */
    public function getMessage(): array
    {
        return $this->message;
    }

}