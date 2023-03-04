<?php

declare(strict_types=1);

namespace App\Factory;

use AMQPChannel;
use AMQPConnection;
use AMQPExchange;
use AMQPQueue;
use Enqueue\Dsn\Dsn;
use Gelf\Publisher;
use Gelf\PublisherInterface;
use Gelf\Transport\AmqpTransport;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use const AMQP_DURABLE;
use const AMQP_EX_TYPE_FANOUT;

final class GelfPublisherFactory
{
    public function __construct(private readonly ParameterBagInterface $parameterBag)
    {
    }

    public function __invoke(): PublisherInterface
    {
        $dsn = Dsn::parseFirst($this->parameterBag->get('gelf_amqp_dsn'));

        $connection = new AMQPConnection(
            [
                'host' => $dsn->getHost(),
                'port' => $dsn->getPort(),
                'vhost' => $dsn->getPath() ?: '/',
                'login' => $dsn->getUser(),
                'password' => $dsn->getPassword(),
            ],
        );
        $connection->connect();
        $channel = new AMQPChannel($connection);

        $exchangeName = $dsn->getQueryBag()->getString('exchange');

        $exchange = new AMQPExchange($channel);
        $exchange->setName($exchangeName);
        $exchange->setType(AMQP_EX_TYPE_FANOUT);
        $exchange->setFlags(AMQP_DURABLE);
        $exchange->declareExchange();

        $queue = new AMQPQueue($channel);
        $queue->setName($dsn->getQueryBag()->getString('queue'));
        $queue->setFlags(AMQP_DURABLE);
        $queue->bind($exchangeName);
        $queue->declareQueue();

        $transport = new AmqpTransport($exchange, $queue);

        return new Publisher($transport);
    }
}
