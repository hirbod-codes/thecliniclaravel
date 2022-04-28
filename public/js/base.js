async function postData(url, data = {}) {
    let token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let myRequest = new Request(url);

    let myHeaders = new Headers();
    myHeaders.append("Content-Type", "application/json");
    myHeaders.append("Accept", "application/json, text-plain, */*");
    myHeaders.append("X-Requested-With", "XMLHttpRequest");
    myHeaders.append("X-CSRF-TOKEN", token);

    let init = {
        method: 'POST',
        mode: 'cors',
        credentials: 'same-origin',
        headers: myHeaders,
        body: JSON.stringify(data)
    };

    const response = await fetch(myRequest, init);

    return response;
}

async function putData(url, data = {}) {
    let token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let myRequest = new Request(url);

    let myHeaders = new Headers();
    myHeaders.append("Content-Type", "application/json");
    myHeaders.append("Accept", "application/json, text-plain, */*");
    myHeaders.append("X-Requested-With", "XMLHttpRequest");
    myHeaders.append("X-CSRF-TOKEN", token);

    let init = {
        method: 'PUT',
        mode: 'cors',
        credentials: 'same-origin',
        headers: myHeaders,
        body: JSON.stringify(data)
    };

    const response = await fetch(myRequest, init);

    return response;
}

function getBody(response) {
    const contentType = response.headers.get("content-type");

    if (contentType && contentType.indexOf("application/json") !== -1) {
        return response.json();
    } else {
        return response.text();
    }
}

function showMessage(text, insertAfterElm, milliseconds = 10000) {
    var div = createAppendElm('div', {
        "class": "message",
    }, insertAfterElm);
    div.innerText = text;

    if (milliseconds !== 0) {
        setTimeout(() => div.remove(), milliseconds);
    }
}

function disableButton(elm) {
    elm.setAttribute('disabled', true);
    elm.setAttribute('style', 'color: red;');
}

function enableButton(elm) {
    elm.removeAttribute('disabled');
    elm.setAttribute('style', 'color: white;');
}

function createAppendElm(tagName, attributes, parentElm) {
    var elm = document.createElement(tagName);

    for (const attribute in attributes) {
        if (Object.hasOwnProperty.call(attributes, attribute)) {
            const value = attributes[attribute];

            elm.setAttribute(attribute, value);
        }
    }

    parentElm.appendChild(elm);

    return elm;
}

function getElmValue(id) {
    return getElm(id).value;
}

function getElm(id) {
    return document.getElementById(id);
}

function isElmEmpty(id) {
    return (getElmValue(id) === '' || getElmValue(id) === null);
}