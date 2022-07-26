function fetchData(method, url, data = {}, headers = {}, excludeHeaders = []) {
    switch (method.toLowerCase()) {
        case 'get':
            return getData(url, headers, excludeHeaders);

        case 'post':
            return postData(url, data, headers, excludeHeaders);

        case 'put':
            return putData(url, data, headers, excludeHeaders);

        case 'delete':
            return deleteData(url, data, headers, excludeHeaders);

        default:
            throw new Error('Unknown fetch method!');
    }
}

function getData(url, headers = {}, excludeHeaders = []) {
    return new Promise(async (resolve) => {
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

        const response = await fetch(url, init);

        resolve({ response: response, value: await getResponseValue(response) });
    });
}

function postData(url, data = {}, headers = {}, excludeHeaders = []) {
    return new Promise(async (resolve) => {
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

        excludeHeaders.forEach((v, i, array) => {
            if (hs.has(v)) {
                hs.delete(v);
            }
        });

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

        const response = await fetch(url, init);

        resolve({ response: response, value: await getResponseValue(response) });
    });
}

function putData(url, data = {}, headers = {}, excludeHeaders = []) {
    return new Promise(async (resolve) => {
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

        excludeHeaders.forEach((v, i, array) => {
            if (hs.has(v)) {
                hs.delete(v);
            }
        });

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

        const response = await fetch(url, init);

        resolve({ response: response, value: await getResponseValue(response) });
    });
}

function deleteData(url, data = {}, headers = {}, excludeHeaders = []) {
    return new Promise(async (resolve) => {
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

        excludeHeaders.forEach((v, i, array) => {
            if (hs.has(v)) {
                hs.delete(v);
            }
        });

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

        const response = await fetch(url, init);

        resolve({ response: response, value: await getResponseValue(response) });
    });
}

function getResponseValue(res) {
    if (res.headers.get('content-type') === 'application/json') {
        return res.json();
    } else {
        return res.text();
    }
}

function backendURL() {
    return 'http://localhost:80';
}

export { getResponseValue, fetchData, getData, postData, putData, deleteData, backendURL };

