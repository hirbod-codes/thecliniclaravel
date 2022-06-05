import React from "react";

let locales = {
    en: {
        longName: 'English',
        shortName: 'en',
        direction: 'ltr'
    },
    fa: {
        longName: 'Persian',
        shortName: 'fa',
        direction: 'rtl'
    },
};

let LocaleContext = React.createContext(locales.en);

export { locales, LocaleContext };