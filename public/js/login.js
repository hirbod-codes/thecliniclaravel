function emailInput(val) {
    document.getElementById("username").value = '';
}

function usernameInput(val) {
    document.getElementById("email").value = '';
}

function submit() {
    let data = {
        'password': document.getElementById('password').value
    };

    if (document.getElementById('username').value != '') {
        data['username'] = document.getElementById('username').value;
    } else {
        if (document.getElementById('email').value != '') {
            data['email'] = document.getElementById('email').value;
        }
    }

    postData('/backend/login', data)
        .then(response => {
            if (response.redirected) {
                window.location.replace(response.url);
            }

            return getBody(response);
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

            showMessage(text, getElm('box'));

            setTimeout(() => {
                getElm('button').innerText = 'Resend';
                enableButton(getElm('button'));
            }, 10000);

            return text;
        });
}
