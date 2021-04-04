<?php
define('PORT', "8090");

class Chat
{
    public function sendHeaders($headersText, $newSocket, $host, $port)
    {
        $headers = [];
        $tmpLine = preg_split("/\r\n/", $headersText);
        foreach ($tmpLine as $str) {
            $str = rtrim($str);
            if (preg_match("/\A(\S+): (.*)\z/", $str, $matches)) {
                $headers[$matches[1]] = $matches[2];
            }
        }
        $key = $headers['Sec-WebSocket-Key'];
        $sKey = base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));

        $strHeadr = "HTTP/1.1 101 Switching Protocols \r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "WebSocket-Origin: $host\r\n" .
            "WebSocket-Location: ws://$host:$port/websocket/server.php\r\n" .
            "Sec-WebSocket-Accept:$sKey\r\n\r\n";

        socket_write($newSocket, $strHeadr, strlen($strHeadr));
    }

    public function newConnectionACK($client_ip_address)
    {
        $message = "New client " . $client_ip_address . " connected";
        $messageArray = [
            "message" => $message,
            "type" => "newConnectionACK"
        ];
        $ask = $this->seal(json_encode($messageArray));
        return $ask;
    }

    public function seal($socketData)
    {
        $b1 = 0x81;
        $length = strlen($socketData);
        $header = "";
        if ($length <= 125) {
            $header = pack('CC', $b1, $length);
        } elseif ($length > 125 && $length < 65535) {
            $header = pack('CCn', $b1, 126, $length);
        } elseif ($length > 65535) {
            $header = pack('CCNN', $b1, 127, $length);
        }
        return $header . $socketData;
    }

    public function send($message, $clientSocketArray)
    {
        $messageLength = strlen($message);
        foreach ($clientSocketArray as $clientSocket){
            @socket_write($clientSocket, $message, $messageLength);
        }
    }
}