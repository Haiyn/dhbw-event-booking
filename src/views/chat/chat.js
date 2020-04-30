$(document).ready(function () {
    const server = new WebSocket("ws://0.0.0.0:8089");

    /**
     * Connection established
     * @param e * Connection event
     */
    server.onopen= e => {
        console.log('Connected.');
        document.getElementById("js-send-button").removeAttribute("disabled");
    };

    /**
     * Connection failed
     * @param e * Error event
     */
    server.onerror = e => {
        console.log('Websocket connection failed!');
        // disable the send button
        document.getElementById("js-send-button").setAttribute("disabled", null);

        // Show an error message
        document.getElementById("js-alert-text").innerHTML =
            "Could not connect to Server! Try reloading the page.";
        document.getElementById("js-alert-box").removeAttribute("hidden");
    };

    /**
     * Websocket server closed the connection
     * @param e
     */
    server.onclose = e => {
        console.log('Remote host closed the connection.');
        // disable the send button
        document.getElementById("js-send-button").setAttribute("disabled", null);

        // Show an error message
        document.getElementById("js-alert-text").innerHTML =
            "The chat service is currently not available. Try again later.";
        document.getElementById("js-alert-box").removeAttribute("hidden");
    };

    /**
     * Message received
     * @param e * Message event
     */
    server.onmessage = e => {
        // Create a new inbound message box
        createNewMessage(e.data, true);
        console.log(e.data);
    };

    /**
     * Send button pressed
     */
    $( "#js-send-button" ).on( "click", function() {
        // Get the message and send it to the ws
        let message = document.getElementById("js-message-box").value;
        console.log("Sending " + message);
        server.send(message);
        console.log("Sent.");

        // Create a new outbound message box
        createNewMessage(message, false);
    });

    /**
     * Creates a new message box in the message history
     * @param message * The message content
     * @param isInbound * Whether the message is outbound (sent) or inbound (received)
     */
    function createNewMessage(message, isInbound) {
        let newMessage = document.createElement("div");
        if(isInbound) {
            newMessage.className = "msg-inbound bg-light";
        } else {
            newMessage.className = "msg-outbound bg-primary";
        }
        let newParagraph = document.createElement("p");
        newParagraph.innerHTML = message;
        newMessage.appendChild(newParagraph);

        document.getElementById("js-history-box").appendChild(newMessage);
    }
});