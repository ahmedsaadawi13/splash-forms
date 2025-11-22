// FILE: /public/assets/js/public-form.js

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('public-form');

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm(this);
        });
    }
});

function submitForm(form) {
    const messagesDiv = document.getElementById('form-messages');
    messagesDiv.innerHTML = '';

    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    submitButton.disabled = true;
    submitButton.textContent = 'Submitting...';

    const formData = new FormData(form);
    const data = {};

    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }

    const xhr = new XMLHttpRequest();
    xhr.open('POST', form.action, true);
    xhr.setRequestHeader('Content-Type', 'application/json');

    xhr.onload = function() {
        submitButton.disabled = false;
        submitButton.textContent = originalText;

        try {
            const response = JSON.parse(xhr.responseText);

            if (xhr.status === 200 && response.success) {
                messagesDiv.innerHTML = `<div class="alert alert-success">${response.message}</div>`;
                form.reset();

                if (response.redirect_url) {
                    setTimeout(() => {
                        window.location.href = response.redirect_url;
                    }, 2000);
                }
            } else {
                let errorMessage = response.message || 'An error occurred';

                if (response.errors) {
                    for (let field in response.errors) {
                        const errorDiv = document.getElementById('error_' + field);
                        if (errorDiv) {
                            errorDiv.textContent = response.errors[field][0] || response.errors[field];
                            errorDiv.style.color = '#ef4444';
                            errorDiv.style.fontSize = '0.875rem';
                            errorDiv.style.marginTop = '0.25rem';
                        }
                    }
                }

                messagesDiv.innerHTML = `<div class="alert alert-error">${errorMessage}</div>`;
            }
        } catch (e) {
            messagesDiv.innerHTML = `<div class="alert alert-error">An error occurred. Please try again.</div>`;
        }
    };

    xhr.onerror = function() {
        submitButton.disabled = false;
        submitButton.textContent = originalText;
        messagesDiv.innerHTML = `<div class="alert alert-error">Network error. Please try again.</div>`;
    };

    xhr.send(JSON.stringify(data));
}
