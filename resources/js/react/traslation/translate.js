import { translations } from './translations.js';

function translate(address, locale) {
    let str = translations[locale];

    address = address.replace('.', '/');
    address = address.replace('\\', '/');

    address.split('/').forEach((v, i) => {
        if (!v) {
            return;
        }

        str = str[v];
    });

    if (typeof str !== 'string') {
        console.error(str);
        throw new Error('Address not found, address: ' + address);
    }

    return str;
}

function ucFirstLetterFirstWord(str) {
    return str
        .trim()
        .split(' ')
        .filter((v, i) => {
            return v;
        })
        .map((v, i) => {
            if (i === 0) {
                return v[0].toUpperCase() + v.slice(1);
            } else {
                return v;
            }
        })
        .join(' ');
}

function ucFirstLetterAllWords(str) {
    return str
        .trim()
        .split(' ')
        .filter((v, i) => {
            return v;
        })
        .map((v, i) => {
            return v[0].toUpperCase() + v.slice(1);
        })
        .join(' ');
}

export { translate, ucFirstLetterFirstWord, ucFirstLetterAllWords };
