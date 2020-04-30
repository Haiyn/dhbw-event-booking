<?php

class WebsocketServer
{
    // Class Instances
    private static $instance;
    private $socket;

    // Configuration
    private static $HOST                = "0.0.0.0";
    private static $PORT                = 8089;
    private static $ALLOWED_ORIGIN     = "http://localhost:8080";      // Defines the origins that can connect
    private static $READ_SIZE           = 2048;                        // The size of every message buffer
    private static $TRACE_ENABLED       = true;                        // echo trace messages or not
    private static $TIMEOUT             = 10;                          // Timeout of each listen iteration in s

    /**
     * Creates a new class instance from a static context
     * @return WebsocketServer
     */
    public static function run()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;

    }

    public function __construct()
    {
        $this->log("Creating Websocket...");
        $this->create();
        $this->initialize();
        $this->log("Waiting for connections...");
        $this->listen();
    }

    /**
     * Creates master socket
     */
    private function create() {
        try
        {
            self::$instance = $this;
            $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        }
        catch (Exception $exception)
        {
            echo("\n\n[FATAL] Websocket failed to construct {$exception}");
            exit(1);
        }
    }

    /**
     * Sets options and with default configuration and binds master socket to host:port
     */
    public function initialize()
    {
        try
        {
            socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
            socket_bind($this->socket, self::$HOST, self::$PORT);
        }
        catch(Exception $exception)
        {
            echo("\n\n[FATAL] Websocket failed to initialize: {$exception}");
            exit(1);
        }
    }

    /**
     * Work/Listen loop of the master socket
     */
    public function listen()
    {
        socket_listen($this->socket);

        $clients = array($this->socket);  // Array of all active sockets

        while (true) {
            $read = $clients; // Array of all sockets with news

            // Select all sockets that have news and write them to $read
            if (socket_select($read, $write = array(), $except = array(), 0) < 1)
                continue;

            $this->log(count($read) . " socket(s) have a new message.");

            // NEW CONNECTION
            if (in_array($this->socket, $read)) {
                // Accept new connection
                $newClient = socket_accept($this->socket);
                socket_getpeername($newClient, $ip);
                $this->log("New connection request from {$ip}. Trying to accept...");

                // Receive the request header
                $request = $this->receive($newClient, 10000);
                $this->trace("Incoming:\n{$request}");

                // Check if origin is allowed
                preg_match('/Origin: (.*)\r\n/', $request, $matches);
                if(self::$ALLOWED_ORIGIN == $matches[1]) {
                    // Origin is allowed, send response
                    $response = $this->createResponse($request);
                    $this->trace("Outgoing:\n{$response}");
                    if($this->send($newClient, $response)) {
                        // Response successful
                        $this->log("Accepted connection from {$matches[1]}\n");
                        // Add the new client to the array of active clients
                        $clients[] = $newClient;
                        $this->log((count($clients) - 1) . " clients connected");

                        // Remove from the master socket from news array
                        $key = array_search($this->socket, $read);
                        unset($read[$key]);
                    }
                } else {
                    // If not, refuse it
                    $this->log("Refused connection from {$matches[1]}");
                    socket_close($newClient);
                }
            }

            // NEW MESSAGES
            if (count($read)) {
                // Receive message from every client with news
                foreach ($read as $count => $socket) {
                    // Get the new message
                    $this->log("Socket #" . $count . " has news");
                    $message = $this->receive($socket);

                    // Client has new message
                    if (!empty($message)) {
                        // Unmask the message
                        if(!$this->handleOpcode($message, $socket)) continue;
                        $message = $this->unmask($message);
                        $this->trace("New message from client #$count: $message");

                        // Broadcast unmasked message to all active clients
                        foreach ($clients as $recipient) {
                            // Ignore master socket and current socket
                            if ($recipient == $this->socket || $recipient == $socket) continue;

                            // Send message to the recipient socket
                            $this->trace("Sending message to socket");
                            print_r($recipient);
                            $this->send($recipient, $this->mask($message."\n"));
                        }
                        continue;
                    } else {
                        // Message is empty, remove the client
                        $this->trace("Socket is sending malformed data: ---{$message}--- Is it disconnected? Removing...");
                        socket_close($socket);
                        $key = array_search($socket, $clients);
                        unset($clients[$key]);
                        $this->log("Client disconnected.");
                    }
                }
            }
            // End of message polling
            sleep(self::$TIMEOUT);
        }

        // Close the master sockets
        socket_close($this->socket);
    }

    /**
     * Read data from a given socket
     * @param $socket * socket to read from
     * @param null $length * Length to read (if null, default READ_SIZE will be read)
     * @return false|string * failure | read message
     */
    private function receive($socket, $length = null) {
        if(empty($length)) {
            $length = self::$READ_SIZE;
        }
        return socket_read($socket, $length);
    }

    /**
     * Writes data to a given socket
     * @param $socket * socket to write to
     * @param $message * message to write
     * @return bool * success on >0 Bytes written, failure if write failed or 0 byte written
     */
    private function send($socket, $message) {
        $result = socket_write($socket, $message, strlen($message));
        if($result === false) {
            // Writing failed, log error
            $this->log("ERROR: " . socket_strerror(socket_last_error()));
            return false;
        } else if ($result === 0) {
            // Writing succeeded but 0 bytes were written
            $this->log("WARNING: 0 Bytes were sent");
            return false;
        }
        // Writing succeeded
        $this->trace($result . " Bytes were sent.");
        return true;
    }

    private function handleOpcode($bytes, $socket) {

        // Get the first byte to check for opcode
        $firstByte = sprintf('%08b', ord($bytes[0]));
        $opcode = dechex(bindec(substr($firstByte, 4, 4)));

        //https://tools.ietf.org/html/rfc6455#section-11.8
        $opcodes = [
            "0" => "Continuation",
            "1" => "Text",
            "2" => "Binary Data",
            "8" => "Connection Close",
            "9" => "Ping",
            "A" => "Pong"
        ];
        $this->trace("Opcode is 0x{$opcode} ({$opcodes[$opcode]})");

        switch($opcodes[$opcode]) {
            case "8":
                $this->log("Remote client closed the connection.");
                socket_close($socket);
                return false;
            default:
                return true;
        }
    }

    /**
     * Checks a message for masking and unmasks if needed
     * @param $message * message to check
     * @return false|string * failure | unmasked data
     */
    private function unmask($bytes) {
        $decodedData = '';

        // Get the data length if mask bit is set
        $dataLength = ord($bytes[1]) & 127;
        $this->trace("Message length is " . $dataLength);

        // Get the masking key for different payload lengths
        // See https://developer.mozilla.org/en-US/docs/Web/API/WebSockets_API/Writing_WebSocket_servers
        if ($dataLength === 126) {
            // Extended payload length  (16): 24 Bits, 3 Bytes
            $mask = substr($bytes, 4, 4);
            $codedData = substr($bytes, 8);
        }
        elseif ($dataLength === 127) {
            // Extended payload length continued (64): 84 Bits, 14 Bytes (8+16+64)
            $mask = substr($bytes, 10, 4);
            $codedData = substr($bytes, 14);
        }
        else {
            // Payload length (8): 8 Bits, 1 Byte
            $mask = substr($bytes, 2, 4);
            $codedData = substr($bytes, 6);
        }

        // Decode the data with the mask
        for ($i = 0; $i < strlen($codedData); $i++) {
            $decodedData .= $codedData[$i] ^ $mask[$i % 4];
        }


        return $decodedData;
    }

    function mask($message)
    {
        $codedData = '';

        // Set data length and mask
        $b1 = 0x80 | (0x1 & 0x0f);
        $dataLength = strlen($message);

        // Mask message for different payload lengths
        // See https://developer.mozilla.org/en-US/docs/Web/API/WebSockets_API/Writing_WebSocket_servers
        if($dataLength <= 125) {
            $codedData = pack('CC', $b1, $dataLength);
        }
        elseif($dataLength > 125 && $dataLength < 65536) {
            $codedData = pack('CCn', $b1, 126, $dataLength);
        }

        elseif($dataLength >= 65536) {
            $codedData = pack('CCNN', $b1, 127, $dataLength);
        }

        return $codedData.$message;
    }

    /**
     * Creates a response header for accepting a message
     * @param $input * Request header
     * @return string * Response header
     */
    function createResponse($input) {
        // Get the sent key and generate accept key
        preg_match('/Sec-WebSocket-Key: (.*)\r\n/', $input, $matches);
        $rawKey = sha1($matches[1].'258EAFA5-E914-47DA-95CA-C5AB0DC85B11',true);
        $acceptKey = base64_encode($rawKey);

        // Construct and return response header
        return $output = "HTTP/1.1 101 Switching Protocols\r\n"
            . "Upgrade: websocket\r\n"
            . "Connection: Upgrade\r\n"
            . "Sec-WebSocket-Version: 13\r\n"
            . "Sec-WebSocket-Accept: $acceptKey\r\n\r\n";
    }

    /**
     * Logs a message
     * @param $message
     */
    private function log($message) {
        echo("\n[LOG] {$message}");
    }

    /**
     * Logs a very verbose message if TRACE_ENABLED true
     * @param $message
     */
    private function trace($message) {
        if(self::$TRACE_ENABLED) {
            echo("\n[TRACE] {$message}");
        }
    }


}

WebsocketServer::run();
