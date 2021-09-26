<?php

namespace App\Messenger;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class ExternalJsonMessageSerializer implements SerializerInterface
{
    public function decode(array $encodedEnvelope): Envelope
    {
//        dd($encodedEnvelope);
        $body = $encodedEnvelope['body'];
        $headers = $encodedEnvelope['headers'];

        $data = unserialize($body);

        if (null === $data) {
            throw new MessageDecodingFailedException('Invalid JSON');
        }

        $stamps = [];
        if (isset($headers['stamps'])) {
            $stamps = unserialize($headers['stamps']);
        }

        $envelope = new Envelope(new CoreMailDataReceiver($data));
        $envelope->with(...$stamps);

        return $envelope;
    }

    public function encode(Envelope $envelope): array
    {
        // this is called if a message is redelivered for "retry"
        $message = $envelope->getMessage();

        // expand this logic later if you handle more than
        // just one message class
        if ($message instanceof CoreMailDataReceiver) {
            // recreate what the data originally looked like
            $data = $message->getMessage();
            $type = 'mail';
        } else {
            throw new \Exception('Unsupported message class');
        }

        $allStamps = [];
        foreach ($envelope->all() as $stamps) {
            $allStamps = array_merge($allStamps, $stamps);
        }

        return [
            'body' => serialize($data),
            'headers' => [
                // store stamps as a header - to be read in decode()
                'stamps' => serialize($allStamps),
                'type' => $type,
            ],
        ];
    }
}