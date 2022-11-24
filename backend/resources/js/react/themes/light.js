const light = {
    palette: {
        mode: 'light',
        primary: {
            main: '#fbc02d',
        },
        secondary: {
            main: '#fff59d',
        }
    }
};

const light_ltr = {
    direction: 'ltr',
    ...light
};

const light_rtl = {
    direction: 'rtl',
    ...light
};

export { light_ltr, light_rtl };
