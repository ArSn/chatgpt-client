// Add your JavaScript code here
const messageContainer = document.querySelector('#message-container');
const messageInput = document.querySelector('#message-input');
messageInput.addEventListener('keyup', function(event) {
    if (event.key === 'Enter') {
        sendMessage();
    }
});

const sendBtn = document.querySelector('#send-btn');
sendBtn.addEventListener('click', sendMessage);

const resetBtn = document.querySelector('#reset-btn');
resetBtn.addEventListener('click', resetConversation);

function renderSpecialThings( message ) {

    // Trim away whitespace at the start of the message
    message = message.replace(/^\s+/, '');

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
    message = message.replace(/--CODESTART--\s+/g, '<pre><code>');
    message = message.replace(/--CODEEND--/g, '</code></pre>');

    return message;
}

function uniqueId() {
    const dateString = Date.now().toString(36);
    const randomness = Math.random().toString(36).substr(2);
    return dateString + randomness;
}

let current_session_id = uniqueId();

function sendMessage() {
    const message = messageInput.value.trim();

    if (!message) {
        return;
    }

    // Create question element
    const questionContainer = window.document.createElement('div');
    questionContainer.classList.add('question');
    questionContainer.innerText = message;
    messageContainer.append(questionContainer);

    // Create answer element
    const answerContainer = window.document.createElement('div');
    answerContainer.classList.add('answer');
    messageContainer.append(answerContainer);

    messageInput.value = '';
    messageContainer.scrollTop = messageContainer.scrollHeight;

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'server.php');
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function() {
        if ((xhr.readyState === 3 || xhr.readyState === 4)  && xhr.status === 200) {
            const response = xhr.responseText;
            answerContainer.innerHTML = renderSpecialThings( response );
            messageContainer.scrollTop = messageContainer.scrollHeight;
        }
        hljs.highlightAll();
    };
    xhr.send('sess=' + current_session_id + '&message=' + encodeURIComponent(message));
}

function resetConversation() {
    const confirmed = confirm('Are you sure you want to start a new conversation? All history will be lost!');
    if (!confirmed) {
        return;
    }

    current_session_id = uniqueId();
    messageContainer.innerText = '';
}