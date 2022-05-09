function send() {
    disableButton(document.getElementById('button'));

    if (document.getElementById('email').value != '') {
        var data = {
            'email': document.getElementById('email').value
        };
    } else {
        if (document.getElementById('phonenumber').value != '') {
            var data = {
                'phonenumber': document.getElementById('phonenumber').value
            };
        }
    }

    postData('/backend/forgot-password', data).then((response) => {
        if (!response.ok || response.status !== 200) {
            return getBody(response);
        }

        if (!document.getElementById('code_box')) {
            addCodeInput(document.getElementById('parent_box'));
        }

        return getBody(response);
    }).then(data => {
        if (typeof data === 'string') {
            var text = data;
        } else {
            var text = "";
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

        showMessage(text, document.getElementById('box'));

        setTimeout(() => {
            document.getElementById('button').innerText = 'Resend';
            enableButton(document.getElementById('button'));
        }, 10000);

        return text;
    });

}

function sendCode() {
    if (document.getElementById('password_confirmation').value !== document.getElementById('password').value) {
        showMessage("The 'password' and 'confirm password' fields have different values.",
            document.getElementById('code_box')
        );
        return;
    }

    var data = {
        "code": document.getElementById('code').value,
        "password": document.getElementById('password').value,
        "password_confirmation": document.getElementById('password_confirmation').value,
    };
    if (document.getElementById('email').value != '') {
        data['email'] = document.getElementById('email').value;
    } else {
        if (document.getElementById('phonenumber').value != '') {
            data['phonenumber'] = document.getElementById('phonenumber').value;
        } else {
            document.getElementById('phonenumber').setAttribute('style', 'border: 1px solid red;');
            document.getElementById('email').setAttribute('style', 'border: 1px solid red;');
            return;
        }
    }

    putData('/backend/reset-password', data).then((response) => {
        if (!response.ok || response.status !== 200) {
            return getBody(response);
        }

        if (response.redirected) {
            window.location.replace(response.url);
        }

        return getBody(response);
    }).then(data => {
        if (typeof data === 'string') {
            var text = data;
        } else {
            var text = "";
            if (Object.hasOwnProperty.call(data, 'errors')) {
                for (const attribute in data.errors) {
                    if (Object.hasOwnProperty.call(data.errors, attribute)) {
                        var messages = data.errors[attribute];

                        for (let i = 0; i < messages.length; i++) {
                            const message = messages[i];

                            text += message + "\n";
                        }
                    }
                }
            } else {
                text = data.message;
            }
        }

        if (Object.hasOwnProperty.call(data, 'redirecturl')) {
            alert(text);
            window.location.replace(data.redirecturl);
        } else {
            alert(text);
            window.location.replace('/');
        }

        return text;
    });
}

function emailInput(val) {
    document.getElementById("phonenumber").removeAttribute('style');
    document.getElementById("email").removeAttribute('style');
    document.getElementById("phonenumber").value = '';
}

function phonenumberInput(val) {
    document.getElementById("email").removeAttribute('style');
    document.getElementById("phonenumber").removeAttribute('style');
    document.getElementById("email").value = '';
}

function addCodeInput(parentElm) {
    var div = createAppendElm('div', {
        "class": "box",
        "id": "code_box"
    }, parentElm);

    createAppendElm('p', {
        "class": "message"
    }, div).innerText = 'Please enter your 6-digit code here.';

    createAppendElm('input', {
        "class": "input",
        "id": "code",
        "type": "text",
        "name": "code",
        "title": "code",
        "placeholder": "123456",
    }, div);

    createAppendElm('input', {
        "class": "input",
        "id": "password",
        "type": "password",
        "name": "password",
        "title": "password",
        "placeholder": "Password",
    }, div);

    createAppendElm('input', {
        "class": "input",
        "id": "password_confirmation",
        "type": "password",
        "name": "password_confirmation",
        "title": "password_confirmation",
        "placeholder": "Confirm Password",
    }, div);

    createAppendElm('button', {
        "class": "button",
        "id": "button",
        "type": "button",
        "onclick": "sendCode()",
    }, div).innerText = 'Send';
}
