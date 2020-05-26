$(document).ready(function () {
    const server = new WebSocket("ws://0.0.0.0:8089");
    // only set the send button if it exists
    let sendButton = $('#js-send-button').length ? $('#js-send-button') : false;

    /**
     * Connection established
     * Sends IDENT message to WSS and enables send button
     * @param e * Connection event
     */
    server.onopen= e => {
        console.log('Connected.');
        server.send("IDENT " + selfId);
        if(sendButton) {
            sendButton.removeAttr("disabled");
        }

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
    if (sendButton) {
        sendButton.on("click", function() {
            // Get message and check for validity
            let messageBox = $('#js-message-box');
            let message = messageBox.val().trim();
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
            messageBox.val("");

            // Create a new outbound message box
            createNewMessage(message, false);
        });
    }


    /**
     * Creates a new message box in the message history
     * @param message * The message content
     * @param isInbound * Whether the message is outbound (sent) or inbound (received)
     */
    function createNewMessage(message, isInbound) {
        let className = isInbound ? "msg-inbound bg-light" : "msg-outbound bg-primary";

        $('#js-history-box').append(
            "<div class='" + className + "'>" +
            "<p>" + message + "</p>" +
            "</div>"
        );
    }

    /**
     * Shows an error message
     * @param message
     */
    function showError(message) {
        // Set the error message
        $('#js-error-text').html(message);

        // disable the send button if it exists
        if (sendButton) {
            sendButton.attr("disabled", "true");
        }

        // Check if error box is hidden, if yes "unhide" it
        let errorBox = $('#js-error-box');
        let hidden = errorBox.attr("hidden");
        if (typeof hidden !== typeof undefined && hidden !== false) { // browser compatibility checks
            errorBox.removeAttr("hidden");
        }

    }

    /**
     * Shows an info message
     * @param message
     */
    function showInfo(message) {
        // Set the info message
        $('#js-info-text').html(message);

        // Check if info box is hidden, if yes "unhide" it
        let infoBox = $('#js-info-box');
        let hidden = infoBox.attr("hidden");
        if (typeof hidden !== typeof undefined && hidden !== false) { // browser compatibility checks
            infoBox.removeAttr("hidden");
        }
    }
});