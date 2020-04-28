$(document).ready(function () {
    const server = new WebSocket("ws://0.0.0.0:8089");

    server.onopen= function(e)
    {
        console.log('connect');
    }

    server.onerror= function(e)
    {
        console.log('connect');
    }
});