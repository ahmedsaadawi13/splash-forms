// FILE: /public/assets/js/app.js

document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    const copyButtons = document.querySelectorAll('[data-copy]');
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const text = this.getAttribute('data-copy');
            navigator.clipboard.writeText(text).then(() => {
                const original = this.textContent;
                this.textContent = 'Copied!';
                setTimeout(() => {
                    this.textContent = original;
                }, 2000);
            });
        });
    });
});

function ajaxRequest(url, method, data, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/json');

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    callback(null, response);
                } catch (e) {
                    callback(null, xhr.responseText);
                }
            } else {
                callback(new Error('Request failed: ' + xhr.status));
            }
        }
    };

    if (data) {
        xhr.send(JSON.stringify(data));
    } else {
        xhr.send();
    }
}

function showMessage(message, type) {
    const messagesDiv = document.getElementById('form-messages') || createMessagesDiv();
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    messagesDiv.appendChild(alert);

    setTimeout(() => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 300);
    }, 5000);
}

function createMessagesDiv() {
    const div = document.createElement('div');
    div.id = 'form-messages';
    document.querySelector('.main-content .container').prepend(div);
    return div;
}
