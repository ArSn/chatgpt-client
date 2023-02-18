// Add your JavaScript code here
const messageContainer = document.querySelector('#message-container');
const messageInput = document.querySelector('#message-input');
const sendBtn = document.querySelector('#send-btn');

sendBtn.addEventListener('click', sendMessage);

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
            messageContainer.innerHTML = response;
            messageContainer.scrollTop = messageContainer.scrollHeight;
        }
    };
    xhr.send('message=' + encodeURIComponent(message));
}