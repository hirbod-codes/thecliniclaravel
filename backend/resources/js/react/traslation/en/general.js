import { addWordTo } from '../translate.js';

let general = {};

general = addWordTo(general, 'light');
general = addWordTo(general, 'light-ltr');
general = addWordTo(general, 'light-rtl');
general = addWordTo(general, 'dark');
general = addWordTo(general, 'dark-ltr');
general = addWordTo(general, 'dark-rtl');

general = addWordTo(general, 'English', null, 'English');
general = addWordTo(general, 'فارسی', null, 'Persian');
general = addWordTo(general, 'English', null, 'en');
general = addWordTo(general, 'فارسی', null, 'fa');

general = addWordTo(general, 'ok');
general = addWordTo(general, 'back');
general = addWordTo(general, 'next');
general = addWordTo(general, 'reset');
general = addWordTo(general, 'submit');
general = addWordTo(general, 'send');
general = addWordTo(general, 'resend');
general = addWordTo(general, 'done');
general = addWordTo(general, 'total', 'totals');
general = addWordTo(general, 'result', 'results');
general = addWordTo(general, 'refresh');
general = addWordTo(general, 'successful');
general = addWordTo(general, 'failure');
general = addWordTo(general, 'create');
general = addWordTo(general, 'update');
general = addWordTo(general, 'show');
general = addWordTo(general, 'delete');
general = addWordTo(general, 'welcome');
general = addWordTo(general, 'diamond');
general = addWordTo(general, 'log in', null, 'log-in');
general = addWordTo(general, 'sign up', null, 'sign-up');
general = addWordTo(general, 'log out', null, 'log-out');
general = addWordTo(general, 'verify');
general = addWordTo(general, 'code', 'codes');
general = addWordTo(general, 'account', 'accounts');
general = addWordTo(general, 'security code', 'security codes', 'security-code');

general = addWordTo(general, 'first name', 'first names', 'firstname');
general = addWordTo(general, 'last name', 'last names', 'lastname');
general = addWordTo(general, 'user name', 'user names', 'username');
general = addWordTo(general, 'email', 'emails');
general = addWordTo(general, 'email', 'emails');
general = addWordTo(general, 'password', 'passwords');
general = addWordTo(general, 'confirm password', null, 'confirm-password');
general = addWordTo(general, 'confirm password', null, 'password_confirmation');
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

general = addWordTo(general, 'monday');
general = addWordTo(general, 'tuesday');
general = addWordTo(general, 'wednesday');
general = addWordTo(general, 'thursday');
general = addWordTo(general, 'friday');
general = addWordTo(general, 'saturday');
general = addWordTo(general, 'sunday');

general = addWordTo(general, general.email.single.allLowerCase + ' ' + general.address.single.allLowerCase, general.email.single.allLowerCase + ' ' + general.address.plural.allLowerCase, 'email-address');

general = addWordTo(general, 'rule', 'rules');
general = addWordTo(general, 'admin', 'admins');
general = addWordTo(general, 'doctor', 'doctors');
general = addWordTo(general, 'secretary', 'secretaries');
general = addWordTo(general, 'operator', 'operators');
general = addWordTo(general, 'patient', 'patients');

general = addWordTo(general, 'starting time', null, 'starting-time');
general = addWordTo(general, 'ending time', null, 'ending-time');

general = addWordTo(general, 'order dashboard', null, 'order-dashboard');
general = addWordTo(general, 'visit dashboard', null, 'visit-dashboard');

general = addWordTo(general, 'laser order', null, 'laser-order');
general = addWordTo(general, 'regular order', null, 'regular-order');

general.columns = {
    account: {}
};

general.columns.account = addWordTo(general.columns.account, 'firstname', 'firstnames');
general.columns.account = addWordTo(general.columns.account, 'lastname', 'lastnames');
general.columns.account = addWordTo(general.columns.account, 'username', 'usernames');
general.columns.account = addWordTo(general.columns.account, 'email verified at', 'emails verified at', 'email_verified_at');
general.columns.account = addWordTo(general.columns.account, 'phonenumber', 'phonenumbers');
general.columns.account = addWordTo(general.columns.account, 'phonenumber verified at', 'phonenumbers verified at', 'phonenumber_verified_at');
general.columns.account = addWordTo(general.columns.account, 'age');
general.columns.account = addWordTo(general.columns.account, 'state', 'states');
general.columns.account = addWordTo(general.columns.account, 'city', 'cities');
general.columns.account = addWordTo(general.columns.account, 'address', 'addresses');
general.columns.account = addWordTo(general.columns.account, 'gender');

general.columns = addWordTo(general.columns, 'action', 'actions');
general.columns = addWordTo(general.columns, 'id');
general.columns = addWordTo(general.columns, 'name');
general.columns = addWordTo(general.columns, 'order', 'orders');
general.columns = addWordTo(general.columns, 'visits');
general.columns = addWordTo(general.columns, 'Created At', null, 'created_at');
general.columns = addWordTo(general.columns, 'Updated At', null, 'updated_at');

export { general };
