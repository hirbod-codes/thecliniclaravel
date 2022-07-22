import { DateTime } from "luxon";

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

function getFormatedDateAccordingToLocale(locale, timeIdentifier = '') {
    let date = getDateObject(timeIdentifier);
    return getDateTimeFormatObject(locale).format(date);
}

function getFormatedDateAccordingToLocaleInEnglish(locale, timeIdentifier = '') {
    let date = getDateObject(timeIdentifier);
    return getDateTimeFormatObjectInEnglish(locale).format(date);
}

function getDateTimeFormatObject(locale) {
    let formatter = null;
    if (locale === 'fa') {
        formatter = getDateTimeFormatObjectForTehran();
    } else {
        formatter = getDateTimeFormatObjectForUTC();
    }

    return formatter;
}

// For controlled input components
function getDateTimeFormatObjectInEnglish(locale) {
    let formatter = null;
    if (locale === 'fa') {
        formatter = getDateTimeFormatObjectForTehranInEnglish();
    } else {
        formatter = getDateTimeFormatObjectForUTC();
    }

    return formatter;
}

function getDateTimeFormatObjectForTehran() {
    return new Intl.DateTimeFormat('fa-IR', {
        calendar: 'persian',
        numberingSystem: 'arabic',
        year: 'numeric',
        month: 'numeric',
        day: 'numeric',
        weekday: 'long',

        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',

        timeZone: 'Asia/Tehran'
    });
}

function getDateTimeFormatObjectForTehranInEnglish() {
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'numeric',
        day: 'numeric',
        weekday: 'long',

        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',

        timeZone: 'Asia/Tehran'
    });
}

function getDateTimeFormatObjectForUS() {
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'numeric',
        day: 'numeric',
        weekday: 'long',

        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        timeZone: 'UTC',
        timeZoneName: 'shortGeneric'
    }).formatToParts();
}

function getDateTimeFormatObjectForUTC() {
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'numeric',
        day: 'numeric',
        weekday: 'long',

        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        timeZone: 'UTC',
        timeZoneName: 'shortGeneric'
    });
}

function getDateObject(timeIdentifier) {
    let date = new Date();
    if (typeof timeIdentifier === 'number') {
        date.setMilliseconds(timeIdentifier);
    } else {
        if (typeof timeIdentifier === 'string') {
            date = new Date(timeIdentifier);
        } else {
            if (typeof (timeIdentifier) === 'object') {
                date = timeIdentifier;
            }
        }
    }

    return date;
}

function resolveTimeZone(locale) {
    switch (locale) {
        case 'en':
            return 'UTC'

        case 'fa':
            return 'Asia/Tehran'

        default:
            return null;
    }
}

// weekDaysPeriods => {Monday: [{start:'00:00', end:'00:00'}, ...], ...}
function convertWeekDays(weekDaysPeriods, fromTimezone, toTimezone) {
    if (typeof (weekDaysPeriods) !== 'object') {
        return null;
    }

    let weekDays = [];
    for (const k in weekDaysPeriods) {
        if (!Object.hasOwnProperty.call(weekDaysPeriods, k)) {
            continue;
        }

        const weekDayPeriods = weekDaysPeriods[k];
        if (Array.isArray(weekDayPeriods) !== true) {
            continue;
        } else {
            if (weekDayPeriods.length === 0) {
                continue;
            }
        }

        let weekDay = null;
        let timePeriods = [];

        weekDayPeriods
            .forEach((v, i) => {
                weekDay = null;
                let startDate = DateTime.fromFormat(v.start, "yyyy-MM-dd HH:mm:ss", { zone: fromTimezone, setZone: true });
                weekDay = startDate.weekdayLong;
                let startDateConverted = null;
                if (toTimezone !== fromTimezone) {
                    startDateConverted = startDate.setZone(toTimezone);
                } else {
                    startDateConverted = DateTime.fromISO(startDate.toISO(), { zone: fromTimezone });
                }

                let endDate = DateTime.fromFormat(v.end, "yyyy-MM-dd HH:mm:ss", { zone: fromTimezone, setZone: true });
                let endDateConverted = null;
                if (toTimezone !== fromTimezone) {
                    endDateConverted = endDate.setZone(toTimezone);
                } else {
                    endDateConverted = DateTime.fromISO(endDate.toISO(), { zone: fromTimezone });
                }

                if (startDateConverted.day === startDate.day && endDateConverted.day === startDate.day) {
                    let found = false;
                    weekDays.forEach((v, j) => {
                        if (v.weekDay === startDate.weekdayLong) {
                            found = true;
                            weekDays[j].timePeriods.push({ start: startDateConverted.toFormat('yyyy-MM-dd HH:mm:ss'), end: endDateConverted.toFormat('yyyy-MM-dd HH:mm:ss') });
                        }
                    });
                    if (!found) {
                        timePeriods.push({ start: startDateConverted.toFormat('yyyy-MM-dd HH:mm:ss'), end: endDateConverted.toFormat('yyyy-MM-dd HH:mm:ss') });
                    }
                } else {
                    if ((startDateConverted.day < startDate.day && endDateConverted.day < startDate.day) || (startDateConverted.day > startDate.day && endDateConverted.day > startDate.day)) {
                        let found = false;
                        weekDays.forEach((v, j) => {
                            if (v.weekDay === startDateConverted.weekdayLong) {
                                found = true;
                                if (startDateConverted.day < startDate.day && endDateConverted.day < startDate.day) {
                                    if (weekDays[j].timePeriods[weekDays[j].timePeriods.length - 1].end === (startDateConverted.toFormat('yyyy-MM-dd HH:mm:ss'))) {
                                        weekDays[j].timePeriods[weekDays[j].timePeriods.length - 1].end = endDateConverted.toFormat('yyyy-MM-dd HH:mm:ss');
                                    } else {
                                        weekDays[j].timePeriods.push({ start: startDateConverted.toFormat('yyyy-MM-dd HH:mm:ss'), end: endDateConverted.toFormat('yyyy-MM-dd HH:mm:ss') });
                                    }
                                } else {
                                    if (weekDays[j].timePeriods[0].start === (endDateConverted.toFormat('yyyy-MM-dd HH:mm:ss'))) {
                                        weekDays[j].timePeriods[0].start = startDateConverted.toFormat('yyyy-MM-dd HH:mm:ss');
                                    } else {
                                        weekDays[j].timePeriods.unshift({ start: startDateConverted.toFormat('yyyy-MM-dd HH:mm:ss'), end: endDateConverted.toFormat('yyyy-MM-dd HH:mm:ss') });
                                    }
                                }
                            }
                        });
                        if (!found) {
                            weekDays.push({ weekDay: startDateConverted.weekdayLong, timePeriods: [{ start: startDateConverted.toFormat('yyyy-MM-dd HH:mm:ss'), end: endDateConverted.toFormat('yyyy-MM-dd HH:mm:ss') }] });
                        }
                    } else {
                        if (startDateConverted.day < startDate.day) {
                            let found = false;
                            weekDays.forEach((v, j) => {
                                if (v.weekDay === startDate.weekdayLong) {
                                    found = true;
                                    weekDays[j].timePeriods.unshift({ start: endDateConverted.toFormat('yyyy-MM-dd') + ' 00:00:00', end: endDateConverted.toFormat('yyyy-MM-dd HH:mm:ss') });
                                }
                            });
                            if (!found) {
                                timePeriods.unshift({ start: endDateConverted.toFormat('yyyy-MM-dd') + ' 00:00:00', end: endDateConverted.toFormat('yyyy-MM-dd HH:mm:ss') });
                            }

                            found = false;
                            weekDays.forEach((v, j) => {
                                if (v.weekDay === startDateConverted.weekdayLong) {
                                    found = true;
                                    if (weekDays[j].timePeriods[weekDays[j].timePeriods.length - 1].end === (startDateConverted.toFormat('yyyy-MM-dd HH:mm:ss'))) {
                                        weekDays[j].timePeriods[weekDays[j].timePeriods.length - 1].end = startDateConverted.toFormat('yyyy-MM-dd') + ' 23:59:59';
                                    } else {
                                        weekDays[j].timePeriods.push({ start: startDateConverted.toFormat('yyyy-MM-dd HH:mm:ss'), end: startDateConverted.toFormat('yyyy-MM-dd') + ' 23:59:59' });
                                    }
                                }
                            });
                            if (!found) {
                                weekDays.push({ weekDay: startDateConverted.weekdayLong, timePeriods: [{ start: startDateConverted.toFormat('yyyy-MM-dd HH:mm:ss'), end: startDateConverted.toFormat('yyyy-MM-dd') + ' 23:59:59' }] });
                            }
                        } else {
                            let found = false;
                            weekDays.forEach((v, j) => {
                                if (v.weekDay === startDate.weekdayLong) {
                                    found = true;
                                    weekDays[j].timePeriods.push({ start: startDateConverted.toFormat('yyyy-MM-dd HH:mm:ss'), end: startDateConverted.toFormat('yyyy-MM-dd') + ' 23:59:59' });
                                }
                            });
                            if (!found) {
                                timePeriods.push({ start: startDateConverted.toFormat('yyyy-MM-dd HH:mm:ss'), end: startDateConverted.toFormat('yyyy-MM-dd') + ' 23:59:59' });
                            }


                            found = false;
                            weekDays.forEach((v, j) => {
                                if (v.weekDay === endDateConverted.weekdayLong) {
                                    found = true;
                                    if (weekDays[j].timePeriods[0].start === (endDateConverted.toFormat('yyyy-MM-dd HH:mm:ss'))) {
                                        weekDays[j].timePeriods[0].start = endDateConverted.toFormat('yyyy-MM-dd') + ' 00:00:00';
                                    } else {
                                        weekDays[j].timePeriods.unshift({ start: endDateConverted.toFormat('yyyy-MM-dd') + ' 00:00:00', end: endDateConverted.toFormat('yyyy-MM-dd HH:mm:ss') });
                                    }
                                }
                            });
                            if (!found) {
                                weekDays.unshift({ weekDay: endDateConverted.weekdayLong, timePeriods: [{ start: endDateConverted.toFormat('yyyy-MM-dd') + ' 00:00:00', end: endDateConverted.toFormat('yyyy-MM-dd HH:mm:ss') }] });
                            }
                        }
                    }
                }
            });

        if (timePeriods.length === 0) {
            continue;
        }

        weekDays.push({ weekDay: weekDay, timePeriods: timePeriods });
    }

    if (weekDays.length === 0) {
        return null;
    }

    return weekDays;
}

export {
    resolveTimeZone,
    convertWeekDays,
    getFormatedDateAccordingToLocaleInEnglish,
    getDateTimeFormatObjectInEnglish,
    getDateTimeFormatObjectForTehranInEnglish,
    getDateTimeFormatObjectForTehran,
    getDateTimeFormatObjectForUS,
    getDateTimeFormatObjectForUTC,
    getFormatedDateAccordingToLocale,
    getDateTimeFormatObject,
    getDateObject,
    iterateRecursively,
    updateState,
    doesExist
};
