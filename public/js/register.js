addStates(getStates());
addGenders(getGenders());

function register() {
    if (!isRequiredInputsFilled()) {
        return;
    }

    const data = new FormData();
    data.append('firstname', getElmValue('firstname'));
    data.append('lastname', getElmValue('lastname'));
    data.append('username', getElmValue('username'));
    data.append('phonenumber', getElmValue('phonenumber'));
    data.append('password', getElmValue('password'));
    data.append('password_confirmation', getElmValue('password_confirmation'));
    data.append('age', getElmValue('age'));
    data.append('gender', getElmValue('gender'));
    data.append('state', getElmValue('state'));
    data.append('city', getElmValue('city'));
    data.append('address', getElmValue('address'));
    data.append('avatar', getElm('avatar').files[0]);

    if (getElmValue('email')) {
        data.append('email', getElmValue('email'));
    }

    if (getElmValue('address')) {
        data.append('address', getElmValue('address'));
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

            if (!element.hasAttribute('required')) {
                continue;
            }

            if (isElmEmpty(element.id)) {
                if (first === true) {
                    first = false;
                    getElm('box').scroll(0, element.offsetTop - element.offsetHeight - 5);
                }
                element.style.border = '1px solid red';
                filled = false;
            }
        }
    }

    if (getElmValue('password') !== getElmValue('password_confirmation')) {
        getElmValue('password').style.border = '1px solid red';
        getElmValue('password_confirmation').style.border = '1px solid red';
        filled = false;
    }

    return filled;
}

function firstname(val) {
    getElm('firstname').style.border = 0;
}

function lastname(val) {
    getElm('lastname').style.border = 0;
}

function username(val) {
    getElm('username').style.border = 0;
}

function phonenumber(val) {
    getElm('phonenumber').style.border = 0;

    if (val.match(/^(09){1}[0-9]{9}$/) === null) {
        getElm('phonenumber').setAttribute('style', 'border: 1px solid red');
    }
}

function phonenumberVerification(val) {
    getElm('phonenumber_verification').style.border = 0;

    if (val.match(/^(09){1}[0-9]{9}$/) === null) {
        getElm('phonenumber_verification').setAttribute('style', 'border: 1px solid red');
    }
}

function password(val) {
    getElm('password').style.border = 0;
}

function password_confirmation(val) {
    getElm('password_confirmation').style.border = 0;
}

function getGenders() {
    return getData('/api/genders').then((response) => {
        return getBody(response);
    });
}

function addGenders(gendersPromise) {
    gendersPromise.then((data) => {
        data.forEach(gender => {
            createAppendElm('option', {
                'value': gender
            }, getElm('gender')).innerText = gender;
        });
    });
}

function gender(val) {
    if (getElm('gender-label')) {
        getElm('gender-label').remove();
    }

    getElm('gender').style.border = 0;
}

function age(val) {
    getElm('age').style.border = 0;
}

function avatar(val) {
    getElm('avatar-label').children[0].innerText = 'Select Image: ' + getElm('avatar').files[0].name;
    Array.from(getElm('img').children).forEach((elm) => {
        elm.remove();
    });
    createAppendElm('img', {
        'src': URL.createObjectURL(getElm('avatar').files[0]),
        'style': 'object-fit:contain;width:100%;max-height:100%;'
    }, getElm('img'));
}

function getStates() {
    return getData('/api/states')
        .then((response) => {
            return getBody(response);
        });
}

function addStates(statesPromise) {
    statesPromise.then((states) => {
        for (let i = 0; i < states.length; i++) {
            const state = states[i];

            createAppendElm('option', {
                'value': state
            }, getElm('state')).innerText = state;
        }
    });
}

function getCities(val) {
    return getData('/api/cities?stateName=' + val).then((response) => {
        return getBody(response);
    });
}

function removeCities() {
    var city = document.getElementById('city');
    Array.from(city.children).forEach((elm) => { elm.remove(); });
}

function addCities(citiesPromise) {
    citiesPromise.then((cities) => {
        for (let i = 0; i < cities.length; i++) {
            const city = cities[i];

            createAppendElm('option', {
                'id': city,
                'value': city
            }, getElm('city')).innerText = city;
        }
    });
}

function enableCity() {
    getElm('city').removeAttribute('disabled');
}

function state(val) {
    if (getElm('state-label')) {
        getElm('state-label').remove();
    }

    getElm('state').style.border = 0;

    removeCities();
    addCities(getCities(val));
    enableCity();
}

function city(val) {
    if (getElm('city-label')) {
        getElm('city-label').remove();
    }

    getElm('city').style.border = 0;
}
