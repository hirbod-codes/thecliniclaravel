import themes from '../themes/themes.js';
// import { enUS as x_enUS, faIR as x_faIR } from '@mui/x-data-grid';
import { enUS, faIR } from '@mui/material/locale';

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

export { resolveLocalization, resolveTheme, themes };
