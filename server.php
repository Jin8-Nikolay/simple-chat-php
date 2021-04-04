<?php

require_once "classes/Chat.php";

$chat = new Chat();

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($socket, 0, PORT);

socket_listen($socket);

$clientSocketArray = [$socket];

while (true) {

    $newSocketArray = $clientSocketArray;
    $null = [];
    socket_select($newSocketArray, $null, $null, 0, 10);

    if (in_array($socket, $newSocketArray)) {
        $newSocket = socket_accept($socket);
        $clientSocketArray[] = $newSocket;

        $header = socket_read($newSocket, 1024);
        $chat->sendHeaders($header, $newSocket, '"localhost/websocket"', PORT);

        socket_getpeername($newSocket, $client_ip_address);
        $connectionACK = $chat->newConnectionACK($client_ip_address);
        $chat->send($connectionACK, $clientSocketArray);

    }
}

socket_close($socket);