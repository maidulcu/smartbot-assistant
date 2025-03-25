(function ($) {
    $(document).ready(function () {
        const $toggleButton = $('#smartbot-toggle-button');
        const $chatContainer = $('#smartbot-chatbot-container');
        const $messages = $('#smartbot-messages');
        const $userInput = $('#smartbot-user-input');

        // Toggle chat visibility
        $toggleButton.on('click', function () {
            $chatContainer.toggleClass('smartbot-chatbot-hidden');
        });

        // Show welcome message on load
        if (SmartBotSettings.welcomeMessage) {
            appendMessage('bot', SmartBotSettings.welcomeMessage);
        }

        let lastSubmissionTime = 0;

        $userInput.on('keypress', function (e) {
            if (e.which === 13) {
                const userMessage = $userInput.val().trim();
                const now = Date.now();

                // Validate input length and spam interval
                if (!userMessage || userMessage.length < 3) {
                    appendMessage('bot', 'Please enter a more detailed question.');
                    return;
                }

                if (now - lastSubmissionTime < 1500) {
                    appendMessage('bot', 'Please wait a moment before asking another question.');
                    return;
                }

                lastSubmissionTime = now;

                console.log('SmartBot: Sending message →', userMessage);
                appendMessage('user', userMessage);
                $userInput.prop('disabled', true);
                $userInput.val('');

                const typingIndicator = $('<div class="smartbot-bot typing-indicator">...</div>');
                $messages.append(typingIndicator);
                $messages.scrollTop($messages[0].scrollHeight);

                $.ajax({
                    url: SmartBotSettings.ajaxUrl,
                    method: 'POST',
                    data: {
                        action: 'smartbot_handle_query',
                        query: userMessage,
                    },
                    success: function (response) {
                        console.log('SmartBot: AJAX Success', response);
                        typingIndicator.remove();
                        appendMessage('bot', response.data || 'Sorry, I could not understand that.');
                        $userInput.prop('disabled', false).focus();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        typingIndicator.remove();
                        console.error('SmartBot: AJAX Error', {
                            status: jqXHR.status,
                            statusText: jqXHR.statusText,
                            responseText: jqXHR.responseText,
                            errorThrown: errorThrown,
                            textStatus: textStatus
                        });
                        appendMessage('bot', 'There was an error processing your request.');
                        $userInput.prop('disabled', false).focus();
                    }
                });
            }
        });

        // Append message to chat
        function appendMessage(sender, text) {
            const msgClass = sender === 'user' ? 'smartbot-user' : 'smartbot-bot';
            $messages.append(`<div class="${msgClass}">${text}</div>`);
            $messages.scrollTop($messages[0].scrollHeight);
        }
    });
})(jQuery);
