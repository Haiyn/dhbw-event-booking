$(document).ready(function () {
    const server = new WebSocket("ws://0.0.0.0:8089");

    /**
     * Connection established
     * Sends IDENT message to WSS and enables send button
     * @param e * Connection event
     */
    server.onopen= e => {
        console.log('Connected.');
        server.send("IDENT " + selfId);
        document.getElementById("js-send-button").removeAttribute("disabled");
    };

    /**
     * Connection failed
     * @param e * Error event
     */
    server.onerror = e => {
        console.log('Websocket connection failed!');
        showError("Could not connect to Server! Try reloading the page.");
    };

    /**
     * Websocket server closed the connection
     * @param e * close event
     */
    server.onclose = e => {
        console.log('Remote host closed the connection. Reason: ' + e.code + " " + e.reason);
        showError("The chat service is currently not available. Try again later.");
    };

    /**
     * Message received
     * Check if its from the chat partner and show message or notification
     * @param e * Message event
     */
    server.onmessage = e => {
        if(e.data === "ERR_USER_NOT_CONNECTED") {
            showInfo("Uh-Oh, looks like your chat partner is not online right now! You can still <a href=\"mailto:" + partnerEmail + "\">email them</a>");
            return;
        }

        if(e.data === "ERR_MSG_NOT_DELIVERED") {
            showError("Sorry, something went wrong. Your message was not delivered.");
            return;
        }

        const message = JSON.parse(e.data);
        if(message.from === partnerId) {
            // Message is from chat partner, show it
            createNewMessage(message.message, true);
        } else {
            // Message is not from chat partner, show notification
            showInfo("You've received a message from someone else!\n" +
                "Follow <a href=\"/chat?user_id=" + message.from + "\">this link</a> to chat with them.");
        }
    };

    /**
     * Send button pressed
     * Sends a JSON payload to the server
     */
    $("#js-send-button").on("click", function() {
        // Get message and check for validity
        let message = document.getElementById("js-message-box").value.trim();
        if(!message) {
            return;
        }
        if (message.length > 2048) {
            showError("Your message is too long!");
            return;
        }

        // Wrap the message and send it
        const wsMessage = JSON.stringify({ from: selfId, to: partnerId, message: message });
        server.send(wsMessage);

        // Reset the input field
        document.getElementById("js-message-box").value = "";

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

    /**
     * Shows an error message
     * @param message
     */
    function showError(message) {
        // disable the send button
        document.getElementById("js-send-button").setAttribute("disabled", null);

        // Show an error message
        document.getElementById("js-alert-text").innerHTML = message;
        document.getElementById("js-alert-box").removeAttribute("hidden");
    }

    /**
     * Shows an info message
     * @param message
     */
    function showInfo(message) {
        document.getElementById("js-info-text").innerHTML = message;
        try {
            document.getElementById("js-info-box").removeAttribute("hidden");
        } catch (ex) {
            // Box already visible, ignore it
        }

    }
});