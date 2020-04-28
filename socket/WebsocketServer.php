<?php

class WebsocketServer
{
    // Class Instances
    private static $instance;
    private static $socket;
    private static $clients;

    // Configuration
    private static $HOST                = "0.0.0.0";
    private static $PORT                = 8089;
    private static $ALLOWED_ORIGINS     = [ "http://localhost:8080" ];      // Defines the origins that can connect
    private static $READ_SIZE           = 2048;                             // The size of every message buffer
    private static $TRACE_ENABLED       = true;                             // echo trace messages or not
    private static $TIMEOUT             = 60;                               // Max timeout in s before client is stale

    public static function run()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;

    }

    public function __construct()
    {
        echo "[DEBUG] Creating Websocket...";
        $this->create();
        echo "\n[DEBUG] Websocket created.\n[DEBUG] Initializing...";
        $this->initialize();
        echo "\n[DEBUG] Initialized.\n[DEBUG] Waiting for connections...";
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
            echo("\n\n[FATAL] Websocket failed to construct {$exception}");
            exit(1);
        }
    }

    public function initialize()
    {
        try
        {
            socket_set_option(self::$socket, SOL_SOCKET, SO_REUSEADDR, 1);
            socket_bind(self::$socket, self::$HOST, self::$PORT);
        }
        catch(Exception $exception)
        {
            echo("\n\n[FATAL] Websocket failed to initialize: {$exception}");
            exit(1);
        }
    }

    public function listen()
    {
        socket_listen(self::$socket);

        $clients = [];  // Array of all connected sockets
        $ticks = 0;     // Counter for stale client watchdog

        while (true) {
            // Accept new connections
            if ($newClient = socket_accept(self::$socket)) {
                if (is_resource($newClient)) {
                    // Try to accept the new client
                    if($this->accept($newClient)) {
                        // Add the new client to the array of active clients
                        array_push($clients, $newClient);
                    }
                }
            }

            // Poll every active client in clients for a new message
            if (count($clients)) {
                foreach ($clients as $count => $socket) {
                    // Check if client has sent a new message
                    $message = $this->receive($socket);
                    if ($message) {
                        if(self::$TRACE_ENABLED) {
                            echo "\n[TRACE] New message from client #$count: $message";
                        }
                        // If client has sent new message, broadcast it to all clients
                        foreach ($clients as $recipient) {
                            if ($socket != $recipient) {
                                // TODO: Unmask message before sending
                                if(!$this->send($recipient, $message)) {
                                    echo "\n[WARN] A client refused a broadcast. Is it stale?";
                                }
                            }
                        }

                    } else {
                        // If client has not sent a new message, check if the client is stale
                        // TODO: Fix watchdog
                        if ($ticks > self::$TIMEOUT) {
                            // Ping the client
                            if (!$this->send($socket, "PING")) {
                                echo "[TRACE] Pinged socket is not responding. Removing...";
                                // Close non-responsive connection
                                socket_close($clients[$count]);
                                // Remove from active connections array
                                unset($clients[$count]);
                            }
                            // Client socket got the ping, reset the stale timer
                            $ticks = 0;
                        }
                    }
                }
            }
            $ticks++;
        }

        // Close the master sockets
        socket_close(self::$socket);


    }
    function accept($client) {
        echo "\n[DEBUG] New connection request. Trying to accept...";
        // Receive the request header
        $request = $this->receive($client, 10000);
        if(self::$TRACE_ENABLED) {
            echo "\n[TRACE] {$request}\n";
        }

        // Check if the request came from an origin that is in the ini
        preg_match('/Origin: (.*)\r\n/', $request, $matches);
        foreach(self::$ALLOWED_ORIGINS as $origin)
        if($matches[1] == $origin) {
            // If origin is in allowed origins ini setting, accept the connection
            $response = $this->createResponse($request);
            if($this->send($client, $response)) {
                echo "\n[INFO] Accepted connection from {$matches[1]}\n";
                return true;
            }
            echo "\n[ERROR] Client refused response";
            return false;
        }

        // If not, refuse it
        echo "\n[WARN] Refused connection from {$matches[1]}\n";
        socket_close($client);
        return false;
    }

    private function receive($socket, $length = null) {
        if(empty($length)) {
            $length = self::$READ_SIZE;
        }
        return socket_read($socket, $length);
    }

    private function send($client, $message) {
        return socket_write($client, $message, strlen($message));
    }

    function createResponse($input) {
        preg_match('/Sec-WebSocket-Key: (.*)\r\n/', $input, $matches);
        $rawKey = sha1($matches[1].'258EAFA5-E914-47DA-95CA-C5AB0DC85B11',true);
        $acceptKey = base64_encode($rawKey);

        return $result = "HTTP/1.1 101 Switching Protocols\r\n"
            . "Upgrade: websocket\r\n"
            . "Connection: Upgrade\r\n"
            . "Sec-WebSocket-Version: 13\r\n"
            . "Sec-WebSocket-Accept: $acceptKey\r\n\r\n";
    }


}

WebsocketServer::run();
