<?php

namespace socket;

/**
 * Class Utility
 * Various methods needed throughout different functions in the application.
 * Methods must be static
 * @package socket
 */
class Utility
{
    /**
     * Checks a message for masking and unmasks if needed
     * @param $bytes * message to check
     * @return false|string * failure | unmasked data
     */
    public static function unmask($bytes)
    {
        $decodedData = '';

        // Get the data length if mask bit is set
        $dataLength = ord($bytes[1]) & 127;
        self::trace("Message length is " . $dataLength);

        // Get the masking key for different payload lengths
        // See https://developer.mozilla.org/en-US/docs/Web/API/WebSockets_API/Writing_WebSocket_servers
        if ($dataLength === 126) {
            // Extended payload length  (16): 24 Bits, 3 Bytes
            $mask = substr($bytes, 4, 4);
            $codedData = substr($bytes, 8);
        } elseif ($dataLength === 127) {
            // Extended payload length continued (64): 84 Bits, 14 Bytes (8+16+64)
            $mask = substr($bytes, 10, 4);
            $codedData = substr($bytes, 14);
        } else {
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

    /**
     * Masks a message for sending to a client socket
     * @param $message * unmasked message
     * @return string * masked message
     */
    public static function mask($message)
    {
        $codedData = '';

        // Set data length and mask
        $b1 = 0x80 | (0x1 & 0x0f);
        $dataLength = strlen($message);

        // Mask message for different payload lengths
        // See https://developer.mozilla.org/en-US/docs/Web/API/WebSockets_API/Writing_WebSocket_servers
        if ($dataLength <= 125) {
            $codedData = pack('CC', $b1, $dataLength);
        } elseif ($dataLength > 125 && $dataLength < 65536) {
            $codedData = pack('CCn', $b1, 126, $dataLength);
        } elseif ($dataLength >= 65536) {
            $codedData = pack('CCNN', $b1, 127, $dataLength);
        }

        return $codedData . $message;
    }

    /**
     * Creates a response header for accepting a message
     * @param $input * Request header
     * @return string * Response header
     */
    public static function createResponse($input)
    {
        // Get the sent key and generate accept key
        preg_match('/Sec-WebSocket-Key: (.*)\r\n/', $input, $matches);
        $rawKey = sha1($matches[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true);
        $acceptKey = base64_encode($rawKey);

        // Construct and return response header
        return $output = "HTTP/1.1 101 Switching Protocols\r\n"
            . "Upgrade: websocket\r\n"
            . "Connection: Upgrade\r\n"
            . "Sec-WebSocket-Version: 13\r\n"
            . "Sec-WebSocket-Accept: $acceptKey\r\n\r\n";
    }

    /**
     * Log a non-fatal error
     * @param $message
     */
    public static function error($message)
    {
        echo("\n[ERROR] {$message}");
    }

    /**
     * Logs a message
     * @param $message
     */
    public static function log($message)
    {
        echo("\n[LOG] {$message}");
    }

    /**
     * Logs a very verbose message if TRACE_ENABLED true
     * @param $message
     */
    public static function trace($message)
    {
        if (filter_var(self::getIniFile()['TRACE_ENABLED'], FILTER_VALIDATE_BOOLEAN)) {
            echo("\n[TRACE] {$message}");
        }
    }

    /**
     * This function opens and returns the contents of the config.ini file
     * @param bool $process_sections * Get the ini contents as array with sections or without (default is without)
     * @return array|false * array of ini file contents or false on failure
     */
    public static function getIniFile($process_sections = false)
    {
        $data = parse_ini_file("./config.ini", $process_sections);
        if ($data === false) {
            echo("\n[WARN] Config file could not be found: Utility getIniFile returns false");
        }
        return $data;
    }
}
