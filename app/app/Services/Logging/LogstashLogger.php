<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Services\Logging;

// importações
use Monolog\Formatter\LogstashFormatter;
use Monolog\Handler\SocketHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

// definindo a classe responsável por enviar os logs para o logstash
class LogstashLogger
{

    public function __invoke(array $config): LoggerInterface
    {

        $handler = new SocketHandler("udp://{$config['host']}:{$config['port']}");
        $handler->setFormatter(new LogstashFormatter(config(('app.name'))));

        return new Logger('logstash.main', [$handler]);
    }
}
