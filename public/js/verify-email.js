function sendEmailVeificationMessage() {
    createAppendElm('div', {
        'id': 'pending-message',
        'class': 'message'
    }, getElm('box')).innerText = getElm('sending...').innerText;
    getElm('send-button').style.border = '1px solid red';
    getElm('send-button').setAttribute('disabled', true);
    getElm('send-button').style.color = '#333';

    postData('/backend/email/verification-notification', {}).then((response) => {
        getElm('send-button').removeAttribute('disabled');
        getElm('send-button').style.color = '#bbbbbb';
        getElm('send-button').removeAttribute('disabled');
        getElm('pending-message').remove();

        if (response.ok && response.status === 200) {
            getElm('send-button').style.border = '1px solid green';

            if (response.redirected) {
                window.location.replace(response.url);
            }
        } else {
            return getBody(response);
        }
    }).then((data) => {
        if (typeof data === 'string') {
            var text = data;
        } else {
            var text = "";
            if (!Object.hasOwnProperty.call(data, 'errors')) {
                text = data.message;
            } else {
                for (const attribute in data.errors) {
                    if (Object.hasOwnProperty.call(data.errors, attribute)) {
                        var messages = data.errors[attribute];

                        for (let i = 0; i < messages.length; i++) {
                            const message = messages[i];

                            text += message + "\n\n";
                        }
                    }
                }
            }
        }

        showMessage(text, getElm('box'), 20000);
    });
}
