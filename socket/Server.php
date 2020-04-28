<?php

function createInitResponse($input) {
    preg_match('/Sec-WebSocket-Key: (.*)\r\n/', $input, $matches);
    $rawKey = sha1($matches[1].'258EAFA5-E914-47DA-95CA-C5AB0DC85B11',true);
    $acceptKey = base64_encode($rawKey);

    return $result = "HTTP/1.1 101 Switching Protocols\r\n"
        . "Upgrade: websocket\r\n"
        . "Connection: Upgrade\r\n"
        . "Sec-WebSocket-Version: 13\r\n"
        . "Sec-WebSocket-Accept: $acceptKey\r\n\r\n";
}

function packResponse($input) {
    $fin_opcode = chr(129); //1 000 0001
    $len = chr(strlen($input)); //since we are lower then 127, the first bit will be 0 => so we have no masking
    return $fin_opcode.$len.$input;
}

function unPackMessage($message) {
    //message is handle as String => 1 char = 1 Byte
    //first byte is just the flags
    //second byte is the length
    $length = ord($message[1] & 127); //127 does have 7 bit - so we cut the 8th bit to be sure)
    $mask = substr($message,2,4);
    $data = substr($message,6);

    $result = "";
    for($i = 0; $i < strlen($data); $i++){
        $result .= $data[$i] ^ $mask[$i%4];
    }

    return $result;
}

$addr = "0.0.0.0";
$port = 8089;

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($socket, $addr, $port);
socket_listen($socket);

$client = socket_accept($socket);

$message = socket_read($client,10000);

$response = createInitResponse($message);
var_dump($response);
socket_write($client, $response, strlen($response));

while(true) {
    sleep(1);
    $message = "";
    socket_recv($client,$message,10000,MSG_DONTWAIT);
    if($message != "") {
        $action = unPackMessage($message);
        if($action == "wait") {
            sleep(5);
        }
    }

    $content = time();
    $response = packResponse($content);
    var_dump($response);
    socket_write($client,$response);
}
