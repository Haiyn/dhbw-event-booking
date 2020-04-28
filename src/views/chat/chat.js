$(document).ready(function () {
    const server = new WebSocket("ws://0.0.0.0:8089");

    server.onopen= function(e)
    {
        console.log('Connected.');
    };

    server.onerror= function(e)
    {
        console.log('error');
    };

    $( "#js-send-button" ).on( "click", function() {
        server.send(document.getElementById("js-message-box").value);
    });

    window.onbeforeunload = function(){
        server.close(1001, "CLOSE_GOING_AWAY")
    }


});