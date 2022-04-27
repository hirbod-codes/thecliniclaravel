function register() {
    if (!isRequiredInputsFilled()) {
        return;
    }

    var data = {
        'firstname': getElmValue('firstname'),
        'lastname': getElmValue('lastname'),
        'username': getElmValue('username'),
        'phonenumber': getElmValue('phonenumber'),
        'password': getElmValue('password'),
        'password_confirmation': getElmValue('password_confirmation'),
        'age': getElmValue('age'),
        'gender': getElmValue('gender'),
        'state': getElmValue('state'),
        'city': getElmValue('city'),
        'address': getElmValue('address'),
        'avatar': getElmValue('avatar'),
    };

    if (getElmValue('email')) {
        data['email'] = getElmValue('email');
    }

    if (getElmValue('address')) {
        data['address'] = getElmValue('address');
    }

    postData('/register', data)
        .then((response) => {
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

function isRequiredInputsFilled() {
    var elms = document.getElementById('box').children;

    var first = true;
    var filled = true;
    for (const elm in elms) {
        if (Object.hasOwnProperty.call(elms, elm)) {
            const element = elms[elm];

            if (element.nodeName !== 'INPUT' || element.nodeName !== 'TEXTAREA') {
                continue;
            }

            if (element.hasAttribute('required') === true && isElmEmpty(element.id)) {
                if (first === true) {
                    first = false;
                    getElm('box').scroll(0, element.offsetTop - element.offsetHeight - 5);
                }
                element.setAttribute('style', 'border: 1px solid red;');
                filled = false;
            }
        }
    }

    if (getElmValue('password') !== getElmValue('password_confirmation')) {
        filled = false;
    }

    return filled;
}

function firstname(val) {
    getElm('firstname').removeAttribute('style');
}

function lastname(val) {
    getElm('lastname').removeAttribute('style');
}

function username(val) {
    getElm('username').removeAttribute('style');
}

function phonenumber(val) {
    getElm('phonenumber').removeAttribute('style');

    if (val.match(/^(09){1}[0-9]{9}$/) === null) {
        getElm('phonenumber').setAttribute('style', 'border: 1px solid red');
    }
}

function phonenumberVerification(val) {
    getElm('phonenumber_verification').removeAttribute('style');

    if (val.match(/^(09){1}[0-9]{9}$/) === null) {
        getElm('phonenumber_verification').setAttribute('style', 'border: 1px solid red');
    }
}

function password(val) {
    getElm('password').removeAttribute('style');
}

function password_confirmation(val) {
    getElm('password_confirmation').removeAttribute('style');
}

function gender(val) {
    getElm('gender').removeAttribute('style');
}

function age(val) {
    getElm('age').removeAttribute('style');
}

function state(val) {
    getElm('state').removeAttribute('style');
}

function city(val) {
    getElm('city').removeAttribute('style');
}

function avatar(val) {
    getElm('avatar-label').children[0].innerText = 'Select Image: ' + getElm('avatar').files[0].name;
}
