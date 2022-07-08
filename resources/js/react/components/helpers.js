function iterateRecursively(data, beforeHandling, handling, handled) {
    if (beforeHandling() === false) {
        return;
    }

    let i = 0;
    for (const k in data) {
        if (Object.hasOwnProperty.call(data, k)) {
            const v = data[k];

            if (handling(data, v, k, i) === false) {
                return;
            }
        }
        i++;
    }

    handled();
}

function updateState(obj, state) {
    return new Promise((resolve) => obj.setState(state, resolve));
}

function doesExist(value) {
    return !(value === null || value === undefined);
}

function getFormatedDateAccordingToLocale(locale, milliseconds) {
    let date = new Date();
    date.setMilliseconds(milliseconds);

    let dateString = '';
    if (locale === 'fa') {
        dateString = date.toLocaleString('fa-IR', { numberingSystem: 'arab', timezone: 'Asia/Tehran', calendar: 'persian' });
    } else {
        dateString = date.toLocaleString('en-US', { timezone: 'UTC' });
    }

    return dateString;
}

export { getFormatedDateAccordingToLocale, iterateRecursively, updateState, doesExist };
