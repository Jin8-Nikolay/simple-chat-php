function message(text) {
    jQuery('#chat_result').append(text);
}


jQuery(document).ready(function ($) {
    let socket = new WebSocket("ws://localhost:8090/websocket/server.php");
    socket.onopen = function () {
        message("<div>Соединение установленно</div>");
    };
    socket.onerror = function (error) {
        message("<div>Ошибка при соединении " + (error.message ? error.message : "") + "</div>");
    };
    socket.onclose = function () {
        message("<div>Соединение закрыто</div>");
    }
    socket.onmessage = function (event) {
        let data = JSON.parse(event.data);
        message("<div>" + data.type + " - " + data.message + "</div>");
    }
    $("#chat").on('submit', function () {
        let message = {
            chat_message: $("#chat-message").val(),
            chat_user: $("#chat-user").val(),
        };
        $("#chat-user").attr("type", "hidden");
        socket.send(JSON.stringify(message));

        return false;
    });
});