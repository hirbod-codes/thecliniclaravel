import { LocaleContext } from '../components/localeContext.js';
import { translations } from './translations.js';

function translate(address) {
    let previousStr, lastFragment, str, locale = '';
    let seekTranslation = (seekLocale) => {
        str = translations[seekLocale];

        address = address.replace('.', '/');
        address = address.replace('\\', '/');
        address.split('/').forEach((v, i) => {
            if (!v) {
                return;
            }

            lastFragment = v;
            previousStr = str;
            str = str[v];
        });

        if (typeof str !== 'string') {
            throw new Error('Address not found, address: ' + address);
        }

        return str;
    }

    try {
        locale = LocaleContext._currentValue.currentLocale.shortName;

        return seekTranslation(locale);

    } catch (error) {
        console.error('locale');
        console.error(locale);
        console.error('address');
        console.error(address);
        console.error('lastFragment');
        console.error(lastFragment);
        console.error('previousStr');
        console.error(previousStr);
        console.error('str');
        console.error(str);

        throw error;
    }
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

function addWordTo(object, single, plural = null, key = null) {
    if (!key) {
        key = single;
    }

    key = key
        .trim()
        .split(' ')
        .filter((v, i) => {
            return v;
        })
        .join('-');

    object[key] = {};

    object[key].single = {
        allLowerCase: single,
        ucFirstLetterFirstWord: ucFirstLetterFirstWord(single),
        ucFirstLetterAllWords: ucFirstLetterAllWords(single)
    };

    if (plural) {
        object[key].plural = {
            allLowerCase: plural,
            ucFirstLetterFirstWord: ucFirstLetterFirstWord(plural),
            ucFirstLetterAllWords: ucFirstLetterAllWords(plural)
        };
    }

    return object;
}

export { translate, ucFirstLetterFirstWord, ucFirstLetterAllWords, addWordTo };
