async function getJsonData(url, headers = {}, excludeHeaders = []) {
    let hs = new Headers();
    hs.append('cors', 'no-cors');
    hs.append('Accept', 'application/json');
    hs.append('Connection', 'keep-alive');
    hs.append('Content-Type', 'application/json');

    for (const key in headers) {
        if (Object.hasOwnProperty.call(headers, key)) {
            const header = headers[key];

            if (hs.has(key)) {
                hs.delete(key);
            }

            hs.append(key, header);
        }
    }

    excludeHeaders.forEach((v, i, array) => {
        if (hs.has(v)) {
            hs.delete(v);
        }
    });

    let init = {
        method: 'GET',
        headers: hs,
        redirect: 'follow'
    };

    const response = await fetch(url, init)

    return response;
}

async function postJsonData(url, data = {}, headers = {}) {
    let hs = new Headers();
    hs.append('cors', 'no-cors');
    hs.append('Accept', 'application/json');
    hs.append('Connection', 'keep-alive');

    for (const key in headers) {
        if (Object.hasOwnProperty.call(headers, key)) {
            const header = headers[key];

            if (hs.has(key)) {
                hs.delete(key);
            }

            hs.append(key, header);
        }
    }

    let init = {
        method: 'POST',
        headers: hs,
        redirect: 'follow'
    };

    if (data.constructor.name === 'FormData') {
        init.body = data;
    } else {
        init.body = JSON.stringify(data);

        if (hs.has('Content-Type')) {
            hs.delete('Content-Type');
        }
        hs.append('Content-Type', 'application/json');
    }

    const response = await fetch(url, init)

    return response;
}

async function putJsonData(url, data = {}, headers = {}) {
    let hs = new Headers();
    hs.append('cors', 'no-cors');
    hs.append('Accept', 'application/json');
    hs.append('Connection', 'keep-alive');

    for (const key in headers) {
        if (Object.hasOwnProperty.call(headers, key)) {
            const header = headers[key];

            if (hs.has(key)) {
                hs.delete(key);
            }

            hs.append(key, header);
        }
    }

    let init = {
        method: 'PUT',
        headers: hs,
        redirect: 'follow'
    };

    if (data.constructor.name === 'FormData') {
        init.body = data;
    } else {
        init.body = JSON.stringify(data);

        if (hs.has('Content-Type')) {
            hs.delete('Content-Type');
        }
        hs.append('Content-Type', 'application/json');
    }

    const response = await fetch(url, init)

    return response;
}

async function deleteJsonData(url, data = {}, headers = {}) {
    let hs = new Headers();
    hs.append('cors', 'no-cors');
    hs.append('Accept', 'application/json');
    hs.append('Connection', 'keep-alive');

    for (const key in headers) {
        if (Object.hasOwnProperty.call(headers, key)) {
            const header = headers[key];

            if (hs.has(key)) {
                hs.delete(key);
            }

            hs.append(key, header);
        }
    }

    let init = {
        method: 'DELETE',
        headers: hs,
        redirect: 'follow'
    };

    if (data.constructor.name === 'FormData') {
        init.body = data;
    } else {
        init.body = JSON.stringify(data);

        if (hs.has('Content-Type')) {
            hs.delete('Content-Type');
        }
        hs.append('Content-Type', 'application/json');
    }

    const response = await fetch(url, init)

    return response;
}

function loadXHR(url) {
    return new Promise(function (resolve, reject) {
        try {
            var xhr = new XMLHttpRequest();

            xhr.open("GET", url);
            xhr.responseType = "blob";

            xhr.onload = function () {
                if (xhr.status === 200) {
                    resolve(xhr.response)
                }
                else {
                    reject("Loading error:" + xhr.statusText)
                }
            };
            xhr.onerror = function () {
                reject("Network error.")
            };
            xhr.send();
        }
        catch (err) { reject(err.message) }
    });
}

function backendURL() {
    return 'http://localhost:80';
}

export { getJsonData, postJsonData, putJsonData, deleteJsonData, loadXHR, backendURL };

