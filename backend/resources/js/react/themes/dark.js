const dark = {
    palette: {
        mode: 'dark',
        primary: {
            main: '#fbc02d',
        },
        secondary: {
            main: '#fff59d',
        }
    },
};

const dark_ltr = {
    direction: 'ltr',
    ...dark
};

const dark_rtl = {
    direction: 'rtl',
    ...dark
};

export { dark_ltr, dark_rtl };
