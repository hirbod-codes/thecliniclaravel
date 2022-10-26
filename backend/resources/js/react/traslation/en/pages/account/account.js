import { addWordTo } from "../../../translate";

let account = {};

account = addWordTo(account, 'account', 'accounts');
account['your-account'] = 'Your Account';
account['others-accounts'] = 'Others Accounts';
account['delete-account'] = 'Delete this account';
account['update-your-password'] = 'Update your password.';
account['update-your-phone'] = 'Update your cell phone.';
account['choose-btw-verification-methods'] = 'Please Choose one of the following methods to verify you:';

export { account };
