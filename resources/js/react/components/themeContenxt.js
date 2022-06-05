import React from 'react';

import { createTheme } from '@mui/material/styles';
import themes from '../themes/themes.js';

let ThemeContext = React.createContext(createTheme(themes.light));

function resolveTheme(name) {
    return themes[name];
};

export { ThemeContext, resolveTheme, themes };