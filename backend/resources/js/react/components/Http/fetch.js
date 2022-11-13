import { translate } from "../../traslation/translate";
import { LocaleContext } from "../localeContext";

function fetchData(method, url, data = {}, headers = {}, excludeHeaders = [], isApiRequest = false) {
    if (isApiRequest) {
        if(headers['Accept']===undefined){
            headers['Accept'] = 'application/json';
        }

        if(headers['Content-Type']===undefined){
            headers['Content-Type'] = 'application/json';
        }

        url = backendURL() + '/api/' + LocaleContext._currentValue.currentLocale.shortName + url;
    } else {
        url = backendURL() + url;
    }

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
            headers: hs
        };

        const response = await fetch(url, init);

        resolve({ response: response, value: await getResponseValue(response) });
    });
}

function postData(url, data = {}, headers = {}, excludeHeaders = []) {
    return new Promise(async (resolve) => {
        let hs = new Headers();

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
            headers: hs
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
            headers: hs
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
            headers: hs
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

async function getResponseValue(res) {
    let r = await getRawValue(res);
    let isJson = false;

    if (res.headers.get('content-type') === 'application/json') {
        isJson = true;
    }

    if (res.status === 422) {
        if (isJson) {
            if (r.errors !== undefined) {
                let messages = [];
                for (const k in r.errors) {
                    r.errors[k].forEach((v, i) => {
                        messages.push(v);
                    });
                }
                return messages;
            } else {
                if (r.message !== undefined) {
                    return r.message;
                } else {
                    return r;
                }
            }
        } else {

            return r;
        }
    }

    if (res.status === 200) {
        if (isJson && r.message !== undefined) {
            return r.message;
        } else {
            return r;
        }
    }

    if (res.status === 500) {
        return translate('generalSentences/server-error');
    }

    if (r.message !== undefined) {
        return r.message;
    } else {
        return translate('generalSentences/server-error');
    }

}

function getRawValue(res) {
    if (res.headers.get('content-type') === 'application/json') {
        return res.json();
    } else {
        return res.text();
    }
}

function backendURL() {
    return 'https://' + window.location.hostname;
}

export { getRawValue, getResponseValue, fetchData, getData, postData, putData, deleteData, backendURL };

