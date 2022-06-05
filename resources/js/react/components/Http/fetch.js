async function getJsonData(url, headers = {}) {
    let hs = {};
    hs.cors = 'no-cors';
    hs.Accept = 'application/json';
    hs['Content-Type'] = 'application/json';
    hs.Connection = 'keep-alive';

    for (const key in headers) {
        if (Object.hasOwnProperty.call(headers, key)) {
            const header = headers[key];

            hs[key] = header;
        }
    }
    headers = hs;

    const response = await fetch(url, {
        method: 'GET',
        headers: headers,
        redirect: 'follow',
    });

    return response.json();
}

async function postJsonData(url, data = {}, headers = {}, returnRedirectedUrl = false) {
    let hs = {};
    hs.cors = 'no-cors';
    hs.Accept = 'application/json';
    hs['Content-Type'] = 'application/json';
    hs.Connection = 'keep-alive';

    for (const key in headers) {
        if (Object.hasOwnProperty.call(headers, key)) {
            const header = headers[key];

            hs[key] = header;
        }
    }
    headers = hs;

    const response = await fetch(url, {
        method: 'POST',
        headers: headers,
        redirect: 'follow',
        body: JSON.stringify(data)
    })

    if (response.redirected && returnRedirectedUrl) {
        return response.url;
    }

    return response.json();
}

async function putJsonData(url, data = {}, headers = {}) {
    let hs = {};
    hs.cors = 'no-cors';
    hs.Accept = 'application/json';
    hs['Content-Type'] = 'application/json';
    hs.Connection = 'keep-alive';

    for (const key in headers) {
        if (Object.hasOwnProperty.call(headers, key)) {
            const header = headers[key];

            hs[key] = header;
        }
    }
    headers = hs;

    const response = await fetch(url, {
        method: 'PUT',
        headers: headers,
        redirect: 'follow',
        body: JSON.stringify(data)
    })

    return response.json();
}

function backendURL() {
    return 'http://localhost:80';
}

export { getJsonData, postJsonData, putJsonData, backendURL };

