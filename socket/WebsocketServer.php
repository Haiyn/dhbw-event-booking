<?php

class WebsocketServer
{
    private static $instance;
    private static $socket;
    private static $clients;
    private static $addr = "0.0.0.0";
    private static $port = 8089;

    public static function run()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;

    }

    public function __construct()
    {
        $this->create();
        $this->initialize();
        $this->listen();
    }

    private function create() {
        try
        {
            self::$instance = $this;
            self::$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);


        }
        catch (Exception $exception)
        {
            echo("[FATAL] Websocket failed to construct {$exception}");
            exit(1);
        }
    }

    public function initialize()
    {
        try
        {
            socket_set_option(self::$socket, SOL_SOCKET, SO_REUSEADDR, 1);
            socket_bind(self::$socket, self::$addr, self::$port);
            socket_listen(self::$socket);
        }
        catch(Exception $exception)
        {
            echo("[FATAL] Websocket failed to initialize: {$exception}");
            exit(1);
        }
    }

    public function listen()
    {
        // Array of all connected sockets
        $clients = array(self::$socket);

        // Start listening loop
        while(true) {
            echo ("Waiting for connection...");

            $sockets_change = $clients;
            $ready = socket_select($sockets_change, $write = null, $expect = null, null);

            if($ready) {
                echo("New connection detected.");
            }

            // Iterate over every connected socket
            foreach($sockets_change as $socket)
            {
                if ($socket == self::$socket)
                {
                    // New client connection
                    $newClient = $this->accept();
                    array_push($clients, $newClient);
                }
                else
                {
                    // New message, receive and check contents
                    $message = $this->receive();
                    if(empty($message)) {
                        echo("Malformed message. Skipping...");
                        continue;
                    }

                    // Send the message to all clients
                    foreach($clients as $client)
                    {
                        $this->send($client, $message);
                    }

                }
            }
        }
    }

    private function accept() {
        // New connection, accept client and add to the clients array
        echo("Accepting new connection.");

        // handshake pls

        return socket_accept(self::$socket);

    }

    private function route() {
        $bytes = $this->receive();

        $messageObject = json_decode($bytes);

        if(!empty($messageObject->sender) && !empty($messageObject->recipient) && !empty($messageObject->message)) {
            return $bytes;
        }

        return null;
    }

    private function receive() {
        return socket_recv(self::$socket, $buffer, 2048, 0);
    }

    private function send($client, $message) {
        @socket_write($client, $message, strlen($message));
    }
}

WebsocketServer::run();
