<?php

namespace socket;

require 'Utility.php';

/**
 * Class WebsocketServer
 * The websocket server that handles chatting on the apache server. Runs in separate docker container.
 * @package socket
 */
class WebsocketServer
{
    // Class Instances
    private static $instance;
    private $socket;            // Master socket
    private $clients;           // List of active clients
    private $clientRegistry;    // Dictionary of sockets with user_id identifier


    /**
     * Creates a new class instance from a static context
     */
    public static function run()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
    }

    public function __construct()
    {
        Utility::log("Creating Websocket...");
        $this->create();
        Utility::log("Waiting for connections...");
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
            socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
            socket_bind($this->socket, Utility::getIniFile()['HOST'], Utility::getIniFile()['PORT']);
        }
        catch (\Exception $exception)
        {
            echo("\n\n[FATAL] Websocket failed to construct {$exception}");
            exit(1);
        }
    }

    /**
     * Work/Listen loop of the master socket
     */
    public function listen()
    {
        socket_listen($this->socket);

        $this->clients = array($this->socket);  // Array of all active sockets
        $this->clientRegistry = array();        // Lookup array for sockets with a user id

        while (true) {
            $read = $this->clients; // Array of all sockets with news

            // Select all sockets that have news and write them to $read
            if (socket_select($read, $write = array(), $except = array(), 0) < 1)
                continue;

            Utility::log(count($read) . " socket(s) have a new message.");
            // NEW CONNECTION
            if (in_array($this->socket, $read)) {
                // Try to accept the request
                $this->accept();

                // Regardless of success, remove from the master socket from news array
                $key = array_search($this->socket, $read);
                unset($read[$key]);
            }

            // NEW MESSAGES
            if (count($read)) {
                // Receive message from every client with news
                foreach ($read as $count => $socket) {
                    // Get the new message
                    Utility::log("Socket #" . $count . " has news");
                    $message = $this->receive($socket);

                    // Client has new message
                    if (!empty($message)) {
                        // Message is not empty, unmask and handle
                        if(!$this->handleOpcode($message, $socket)) continue;
                        $message = Utility::unmask($message);
                        Utility::trace("New message from client #$count: $message");
                        $this->handleMessage($message, $socket);
                    } else {
                        // Message is empty, remove the client
                        $this->close($socket);
                        Utility::log("Client disconnected.");
                    }
                }
            }
            // End of message polling
            sleep(Utility::getIniFile()['TIMEOUT']);
        }

        // Close the master sockets
        socket_close($this->socket);
    }

    /**
     * Tries to accept an incoming connection request
     */
    private function accept() {
        // Accept new connection
        $newClient = socket_accept($this->socket);
        socket_getpeername($newClient, $ip);
        Utility::log("New connection request from {$ip}. Trying to accept...");

        // Receive the request header
        $request = $this->receive($newClient, 10000);
        Utility::trace("Incoming:\n{$request}");

        // Check if origin is allowed
        preg_match('/Origin: (.*)\r\n/', $request, $matches);
        if(Utility::getIniFile()['ALLOWED_ORIGIN'] == $matches[1]) {
            // Origin is allowed, send response
            $response = Utility::createResponse($request);
            Utility::trace("Outgoing:\n{$response}");
            if($this->send($newClient, $response, false)) {
                // Response successful
                Utility::log("Accepted connection from {$matches[1]}\n");
                // Add the new client to the array of active clients
                $this->clients[] = $newClient;
                Utility::log((count($this->clients) - 1) . " clients connected");
            }
        } else {
            // If not, refuse it
            Utility::log("Refused connection from {$matches[1]}");
            socket_close($newClient);
        }
    }

    /**
     * Read data from a given socket
     * @param $socket * socket to read from
     * @param null $length * Length to read (if null, default READ_SIZE will be read)
     * @return false|string * failure | read message
     */
    private function receive($socket, $length = null) {
        if(empty($length)) {
            $length = Utility::getIniFile()['READ_SIZE'];
        }
        return socket_read($socket, $length);
    }

    /**
     * Masks data and writes it to a given socket
     * @param $socket * socket to write to
     * @param $message * message to write (unmasked)
     * @param bool $isMasked * true if message should be masked, false if not (handshake)
     * @return bool * true on >0 Bytes written, false if write failed or 0 byte written
     */
    private function send($socket, $message, $isMasked = true) {
        if($isMasked) {
            $message = Utility::mask($message);
        }

        $result = socket_write($socket, $message, strlen($message));

        if($result === false) {
            // Writing failed, log error
            Utility::log("ERROR: " . socket_strerror(socket_last_error()));
            return false;
        } else if ($result === 0) {
            // Writing succeeded but 0 bytes were written
            Utility::log("WARNING: 0 Bytes were sent");
            return false;
        }
        // Writing succeeded
        Utility::trace($result . " Bytes were sent.");
        return true;
    }

    /**
     * Closes an active connection and cleans up the persistent arrays
     * @param $socket
     */
    private function close($socket) {
        // Close the socket and remove it form the clients array
        socket_close($socket);
        $key = array_search($socket, $this->clients);
        unset($this->clients[$key]);

        // Check if socket is registered, if so delete
        $key = array_search($socket, $this->clientRegistry);
        if(array_key_exists($key, $this->clientRegistry)) {
            unset($this->clientRegistry[$key]);
        }
    }

    /**
     * Analyzes the opcode of a sent messages and handles it
     * @param $bytes * message
     * @param $socket * socket that sent the message
     * @return bool * true if handled and socket alive, false if socket dead
     */
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
        Utility::trace("Opcode is 0x{$opcode} ({$opcodes[$opcode]})");

        // Handle the different opcodes
        switch($opcode) {
            // TODO: Handle PING and continuous frames
            case "8":
                Utility::log("Remote client closed the connection.");
                $this->close($socket);
                return false;
            default:
                return true;
        }
    }

    /**
     * Handles a message by its content
     * @param $message * message
     * @param $socket * socket that sent the message
     */
    private function handleMessage($message, $socket) {
        if(strpos($message, "IDENT") === 0) {
            // Message is an identification message, register the client
            Utility::trace("IDENT message received, trying to register...");
            $id = substr($message, 6);

            // Add the socket to the client registry for lookup
            $this->clientRegistry[$id] = $socket;
            Utility::log("Registered new client with ID {$id}");
            Utility::log(count($this->clientRegistry) . " clients are registered.");
        }
        else {
            // Message is not IDENT, handle it as a routable chat message
            Utility::trace("Chat message received, trying to route...");
            $messageObject = json_decode($message);

            // Do a lookup in the client registry for the "to" value of the received json message
            Utility::trace("Checking registry for ID {$messageObject->to}...");
            $recipient = $this->clientRegistry[$messageObject->to];
            Utility::trace("Recipient for ID: " . $recipient);
            if(!empty($recipient)) {
                // Lookup success, send message to socket
                $newMessage = json_encode(array(
                    "from" => $messageObject->from,
                    "to" => $messageObject->to,
                    "message" => $messageObject->message
                ));
                $this->send($recipient, $newMessage);
                Utility::trace("Message sent to Socket (ID {$messageObject->to}): {$newMessage}");
                Utility::log("Message to Socket with ID {[}$messageObject->to} was successfully routed.");

            } else {
                // Lookup failed, ID is not in registry
                Utility::log("Socket with ID {$messageObject->to} is not connected. Aborted routing.");
            }
        }
    }

}

WebsocketServer::run();
