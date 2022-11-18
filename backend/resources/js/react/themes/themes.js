import light from './light.js';
import dark from './dark.js';

let themes = {};

light.direction = 'ltr';
themes['light-ltr'] = light;

light.direction = 'rtl';
themes['light-rtl'] = light;

dark.direction = 'ltr';
themes['dark-ltr'] = dark;

dark.direction = 'rtl';
themes['dark-rtl'] = dark;

export default themes;