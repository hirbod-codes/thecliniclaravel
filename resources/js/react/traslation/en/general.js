import { addWordTo } from '../translate.js';

let general = {};

general = addWordTo(general, 'ok');
general = addWordTo(general, 'back');
general = addWordTo(general, 'reset');
general = addWordTo(general, 'submit');
general = addWordTo(general, 'welcome');
general = addWordTo(general, 'log in', null, 'log-in');
general = addWordTo(general, 'sign up', null, 'sign-up');
general = addWordTo(general, 'log out', null, 'log-out');
general = addWordTo(general, 'verify');
general = addWordTo(general, 'account', 'accounts');
general = addWordTo(general, 'security code', 'security codes', 'security-code');

general = addWordTo(general, 'first name', 'first names', 'firstname');
general = addWordTo(general, 'last name', 'last names', 'lastname');
general = addWordTo(general, 'user name', 'user names', 'username');
general = addWordTo(general, 'email', 'emails');
general = addWordTo(general, 'email', 'emails');
general = addWordTo(general, 'password', 'passwords');
general = addWordTo(general, 'confirm password', null, 'confirm-password');
general = addWordTo(general, 'phone number', 'phone numbers', 'phonenumber');
general = addWordTo(general, 'gender', 'genders');
general = addWordTo(general, 'avatar', 'avatars');
general = addWordTo(general, 'age', 'ages');
general = addWordTo(general, 'state', 'states');
general = addWordTo(general, 'city', 'cities');
general = addWordTo(general, 'address', 'addresses');
general = addWordTo(general, 'order', 'orders');
general = addWordTo(general, 'visit', 'visits');
general = addWordTo(general, 'setting', 'settings');

general = addWordTo(
    general,
    general.email.single.allLowerCase + ' ' + general.address.single.allLowerCase,
    general.email.single.allLowerCase + ' ' + general.address.plural.allLowerCase,
    'email-address'
);

export { general };
