addPhonenumberVerification();

function phonenumberReverify(params) {
    addPhonenumberVerification(true);
}

function addPhonenumberVerification(closable = false) {
    body = document.body;

    var blocker = createAppendElm('div', {
        'id': 'blocker',
        'class': 'parent_box',
        'style': 'width:100%;height:100%;background-color:rgba(0, 0, 0, 0.8);',
    }, body);

    var box = createAppendElm('div', {
        'class': 'box',
        'id': 'box_verification',
        'style': 'background-color:rgb(12, 19, 28);box-shadow:0 0 26px 1px #555;',
    }, blocker);

    createAppendElm('div', {
        'class': 'message',
    }, box).innerText = getElm('phonenumber_verification_request_message').innerText;

    createAppendElm('input', {
        'class': 'input',
        'type': 'text',
        'id': 'phonenumber_verification',
        'placeholder': '09#########',
        'maxlength': '11',
        'oninput': 'phonenumberVerification(this.value)',
    }, box);

    createAppendElm('button', {
        'class': 'button',
        'type': 'button',
        'id': 'phonenumberVerificationButton',
        'onclick': 'phonenumberVerifySend()',
    }, box).innerText = getElm('Send').innerText;

    if (closable) {
        createAppendElm('button', {
            'class': 'button',
            'type': 'button',
            'id': 'phonenumberVerificationCloseButton',
            'onclick': 'phonenumberVerificationClose()',
            'style': 'margin:0.5em;',
        }, box).innerText = getElm('Close').innerText;
    }
}

function phonenumberVerificationClose() {
    if (getElm('blocker')) {
        getElm('blocker').remove();
    }
}

function addPhonenumberVerificationCodeInput() {
    var blocker = getElm('blocker');

    var box = createAppendElm('div', {
        'class': 'box',
        'id': 'box_verification_code',
        'style': 'background-color:rgb(12, 19, 28);box-shadow:0 0 46px 4px;',
    }, blocker);

    createAppendElm('div', {
        'class': 'message',
    }, box).innerText = getElm('phonenumber_verification_send_code_request_message').innerText;

    createAppendElm('input', {
        'class': 'input',
        'type': 'text',
        'id': 'code',
        'placeholder': '123456',
        'style': 'border:1px solid red',
        'oninput': 'code(this.value)',
        'maxlength': '6',
    }, box);

    createAppendElm('button', {
        'class': 'button',
        'type': 'button',
        'id': 'phonenumberVerificationCodeButton',
        'style': 'color: red;',
        'onclick': 'phonenumberVerifyCodeSend()',
    }, box).innerText = getElm('Send-Code').innerText;
}

function code(val) {
    getElm('code').removeAttribute('style');
    getElm('phonenumberVerificationCodeButton').removeAttribute('style');
}

function phonenumberVerifyCodeSend() {
    if (isElmEmpty('phonenumber_verification')) {
        getElm('code').setAttribute('style', 'border:1px solid red');
        getElm('phonenumberVerificationCodeButton').setAttribute('style', 'color: red;');
        disableButton(getElm('phonenumberVerificationCodeButton'));
        return;
    }
    getElm('code').removeAttribute('style');
    enableButton(getElm('phonenumberVerificationCodeButton'));

    var data = {
        'phonenumber': getElmValue('phonenumber_verification'),
        'code': getElmValue('code'),
    };

    postData('/register/verify-phoennumber-verification-code', data).then((response) => {
        if (response.ok && response.status === 200) {
            disableButton(getElm('phonenumberVerificationCodeButton'));
            phonenumberVerificationClose();
            getElm('phonenumber').value = data['phonenumber'];
            getElm('phonenumber').style.border = 0;
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

        showMessage(text, getElm('box_verification'), 20000);

        setTimeout(() => {
            getElm('phonenumberVerificationCodeButton').innerText = 'Resend Code';
            enableButton(getElm('phonenumberVerificationCodeButton'));
        }, 20000);

        return text;
    });
}

function phonenumberVerifySend() {
    var data = {
        'phonenumber': getElmValue('phonenumber_verification')
    };

    postData('/register/send-phoennumber-verification-code', data)
        .then((response) => {
            if (response.ok && response.status === 200) {
                disableButton(getElm('phonenumberVerificationButton'));

                if (!getElm('box_verification_code')) {
                    addPhonenumberVerificationCodeInput();
                }
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

                                text += message + "\n";
                            }
                        }
                    }
                }
            }

            showMessage(text, getElm('box_verification'));

            setTimeout(() => {
                getElm('phonenumberVerificationButton').innerText = 'Resend';
                enableButton(getElm('phonenumberVerificationButton'));
            }, 10000);

            return text;
        });
}
