import React from 'react';

import { createTheme } from '@mui/material/styles';
import themes from '../themes/themes.js';
// import { enUS as x_enUS, faIR as x_faIR } from '@mui/x-data-grid';
import { enUS, faIR } from '@mui/material/locale';

let ThemeContext = React.createContext(createTheme(themes['light-ltr']));

function resolveTheme(name) {
    return themes[name];
};

function resolveLocalization(locale) {
    switch (locale) {
        case 'fa':
            return faIR;

        case 'en':
            return enUS;

        default:
            throw new Error('Locale not found!!');
    }
}

export { ThemeContext, resolveLocalization, resolveTheme, themes };
