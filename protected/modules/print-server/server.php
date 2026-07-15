<?php
function dump($data=''){
	echo '<pre>';
	print_r($data);
	echo '</pre>';
}

require 'project/vendor/autoload.php';
require_once 'project/CposThermalPrinter.php';


use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class PrintServer implements MessageComponentInterface {
    public function onOpen(ConnectionInterface $conn) {
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {

        try {          
            $data = json_decode($msg,true);        
            $job_id = isset($data['job_id'])?$data['job_id']:'';             
            echo '> Printing JOB ID : '.$job_id ."\n"; 
            $settings = $data['settings'];            
            CposThermalPrinter::setDebug(false);
            CposThermalPrinter::setSettings($settings);
            CposThermalPrinter::Print($data);
            echo '> Successful printing JOB ID : '.$job_id ."\n";
        } catch (Exception $e) {                        
            echo '> ERROR . ', $e->getMessage(), "\n\n";
        }
    }

    public function onClose(ConnectionInterface $conn) {
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

$server_port = 49160;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new PrintServer()
        )
    ),
    $server_port // The port your WebSocket server will listen on
);

echo "WebSocket server started on port $server_port\n";
$server->run();