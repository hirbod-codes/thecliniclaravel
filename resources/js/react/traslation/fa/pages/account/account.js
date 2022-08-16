import { addWordTo } from "../../../translate";

let account = {};

account = addWordTo(account, 'حساب', 'حساب ها', 'account');
account['your-account'] = 'حساب شما';
account['others-accounts'] = 'حساب دیگران';
account['delete-account'] = 'حذف این حساب';
account['update-your-password'] = 'بروزرسانی رمز عبور';
account['update-your-phone'] = 'بروزرسانی شماره موبایل';
account['choose-btw-verification-methods'] = 'لطفا یکی از روش های موجود را برای تایید اطلاعاتتان انتخاب کنید';

export { account };
