<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use React\Http\Server;
use React\Socket\SocketServer as ReactSocket;

class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

$loop = Factory::create();
$webSock = new ReactSocket($loop, '0.0.0.0:8080');

$webServer = new HttpServer(
    new WsServer(
        new Chat()
    )
);

$server = new Server($webServer);
$server->listen($webSock);

echo "Chat server started\n";
$loop->run();