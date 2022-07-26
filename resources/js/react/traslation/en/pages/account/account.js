import { addWordTo } from "../../../translate";

let account = {};

account = addWordTo(account, 'account', 'accounts');
account['your-account'] = 'Your Account';
account['others-accounts'] = 'Others Accounts';
account['delete-account'] = 'Delete this account';
account['update-your-password'] = 'Update your password.';
account['update-your-phone'] = 'Update your cell phone.';
account['choose-btw-verification-methods'] = 'Please Choose one of the following methods to verify you:';

account.columns = {};

account.columns = addWordTo(account.columns, 'firstname', 'firstnames');
account.columns = addWordTo(account.columns, 'lastname', 'lastnames');
account.columns = addWordTo(account.columns, 'username', 'usernames');
account.columns = addWordTo(account.columns, 'email verified at', 'emails verified at', 'emailVerifiedAt');
account.columns = addWordTo(account.columns, 'phonenumber', 'phonenumbers');
account.columns = addWordTo(account.columns, 'phonenumber verified at', 'phonenumbers verified at', 'phonenumberVerifiedAt');
account.columns = addWordTo(account.columns, 'age');
account.columns = addWordTo(account.columns, 'state', 'states');
account.columns = addWordTo(account.columns, 'city', 'cities');
account.columns = addWordTo(account.columns, 'address', 'addresses');

export { account };
