// Add your JavaScript code here
const messageContainer = document.querySelector('#message-container');
const messageInput = document.querySelector('#message-input');
const sendBtn = document.querySelector('#send-btn');

sendBtn.addEventListener('click', sendMessage);

function renderSpecialThings( message ) {

    // Convert code sample to HTML entities
    message = message.replace(/[\u00A0-\u9999<>&]/g, function(i) {
        return '&#'+i.charCodeAt(0)+';';
    });

    // Replace line breaks in message with html breaks
    message = message.replace(/\n/g, '<br>');

    // Restore linebreaks in code samples - also catching the case where --CODESTART-- is streamed already but the end is not yet
    message = message.replace(/--CODESTART--((?:(?!--CODESTART--)[\s\S])*?)(?:--CODEEND--|$)/g, function(match, group) {
        return match.replace(/<br>/g, "\n");
    });

    // Replace closing and opening statements in PHP examples
    message = message.replace(/--CODESTART--/g, '<pre><code>');
    message = message.replace(/--CODEEND--/g, '</code></pre>');

    return message;
}

function sendMessage() {
    const message = messageInput.value.trim();

    if (!message) {
        return;
    }

    //messageInput.value = '';

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'server.php');
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 3 && xhr.status === 200) {
            const response = xhr.responseText;
            messageContainer.innerHTML = renderSpecialThings( response );
            messageContainer.scrollTop = messageContainer.scrollHeight;
        }
        hljs.highlightAll();
    };
    xhr.send('message=' + encodeURIComponent(message));
}