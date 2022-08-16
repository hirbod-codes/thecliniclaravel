import { addWordTo } from '../translate.js';

let general = {};

general = addWordTo(general, 'روشن', null, 'light');
general = addWordTo(general, 'روشن', null, 'light-ltr');
general = addWordTo(general, 'روشن', null, 'light-rtl');
general = addWordTo(general, 'تاریک', null, 'dark');
general = addWordTo(general, 'تاریک', null, 'dark-ltr');
general = addWordTo(general, 'تاریک', null, 'dark-rtl');

general = addWordTo(general, 'English', null, 'English');
general = addWordTo(general, 'فارسی', null, 'Persian');

general = addWordTo(general, 'باشه', null, 'ok');
general = addWordTo(general, 'بازگشت', null, 'back');
general = addWordTo(general, 'بعدی', null, 'next');
general = addWordTo(general, 'بارگزاری مجدد', null, 'reset');
general = addWordTo(general, 'ارسال', null, 'submit');
general = addWordTo(general, 'ارسال', null, 'send');
general = addWordTo(general, 'ارسال مجدد', null, 'resend');
general = addWordTo(general, 'انجام شد', null, 'done');
general = addWordTo(general, 'مجموع', 'مجموعه همه', 'total');
general = addWordTo(general, 'نتیجه', 'نتایج', 'result');
general = addWordTo(general, 'بروزرسانی', null, 'refresh');
general = addWordTo(general, 'موفق', null, 'successful');
general = addWordTo(general, 'شکست', null, 'failure');
general = addWordTo(general, 'ایجاد', null, 'create');
general = addWordTo(general, 'بروزرسانی', null, 'update');
general = addWordTo(general, 'نمایش', null, 'show');
general = addWordTo(general, 'حذف', null, 'delete');
general = addWordTo(general, 'خوش آمدید', null, 'welcome');
general = addWordTo(general, 'ورود', null, 'log-in');
general = addWordTo(general, 'ثبت نام', null, 'sign-up');
general = addWordTo(general, 'خروج', null, 'log-out');
general = addWordTo(general, '', null, 'verify');
general = addWordTo(general, 'کد', 'کدها', 'code');
general = addWordTo(general, 'حساب', 'حساب ها', 'account');
general = addWordTo(general, 'کد امنیتی', 'کدهای امنیتی', 'security-code');

general = addWordTo(general, 'نام', 'نام ها', 'firstname');
general = addWordTo(general, 'نام خانوادگی', 'نام های خانوادگی', 'lastname');
general = addWordTo(general, 'نام کاربری', 'نام های کربری', 'username');
general = addWordTo(general, 'ایمیل', 'ایمیل ها', 'email');
general = addWordTo(general, 'رمز عبور', 'رمز های عبور', 'password');
general = addWordTo(general, 'تکرار رمز عبور', null, 'confirm-password');
general = addWordTo(general, 'تکرار رمز عبور', null, 'password_confirmation');
general = addWordTo(general, 'شماره موبایل', 'شمارهای موبایل', 'phonenumber');
general = addWordTo(general, 'جنسیت', 'جنسیت ها', 'gender');
general = addWordTo(general, 'آواتار', 'آواتار ها', 'avatar');
general = addWordTo(general, 'سن', 'سنین', 'age');
general = addWordTo(general, 'استان', 'استان ها', 'state');
general = addWordTo(general, 'سهر', 'سهر ها', 'city');
general = addWordTo(general, 'آدرس', 'آدرس ها', 'address');
general = addWordTo(general, 'سفارش', 'سفارشات', 'order');
general = addWordTo(general, 'نوبت', 'نوبت ها', 'visit');
general = addWordTo(general, 'تنظیمات', 'تنظیمات', 'setting');

general = addWordTo(general, 'دوشنبه');
general = addWordTo(general, 'سه شنبه');
general = addWordTo(general, 'چهارشنبه');
general = addWordTo(general, 'پنج شنبه');
general = addWordTo(general, 'جمعه');
general = addWordTo(general, 'شنبه');
general = addWordTo(general, 'یکشنبه');

general = addWordTo(general, 'آدرس ایمیل', 'آدرس های ایمیل', 'email-address');

general = addWordTo(general, 'نوع', 'نوع ها', 'rule');
general = addWordTo(general, 'ادمین', 'ادمین ها', 'admin');
general = addWordTo(general, 'دکتر', 'دکتر ها', 'doctor');
general = addWordTo(general, 'منشی', 'منشی ها', 'secretary');
general = addWordTo(general, 'اوپراتور', 'اوپراتور ها', 'operator');
general = addWordTo(general, 'بیمار', 'بیمار ها', 'patient');

general = addWordTo(general, 'زمان شروع', null, 'starting-time');
general = addWordTo(general, 'زمان پایان', null, 'ending-time');

general = addWordTo(general, 'پنل سفارشات', null, 'order-dashboard');
general = addWordTo(general, 'پنل نوبت ها', null, 'visit-dashboard');

general = addWordTo(general, 'سفارش لیزر', null, 'laser-order');
general = addWordTo(general, 'سفارش معمولی', null, 'regular-order');

general.columns = {
    account: {}
};

general.columns.account = addWordTo(general.columns.account, 'نام', 'نام ها', 'firstname');
general.columns.account = addWordTo(general.columns.account, 'نام خانوادگی', 'نام های خانوادگی', 'lastname');
general.columns.account = addWordTo(general.columns.account, 'نام کاربری', 'نام های کربری', 'username');
general.columns.account = addWordTo(general.columns.account, 'زمان تایید ایمیل', 'زمان تایید ایمیل ها', 'email_verified_at');
general.columns.account = addWordTo(general.columns.account, 'شماره موبایل', 'شمارهای موبایل', 'phonenumber');
general.columns.account = addWordTo(general.columns.account, 'زمان تایید شماره همراه', 'زمان تایید شماره های همراه', 'phonenumber_verified_at');
general.columns.account = addWordTo(general.columns.account, 'سن', 'سنین', 'age');
general.columns.account = addWordTo(general.columns.account, 'استان', 'استان ها', 'state');
general.columns.account = addWordTo(general.columns.account, 'سهر', 'سهر ها', 'city');
general.columns.account = addWordTo(general.columns.account, 'آدرس', 'آدرس ها', 'address');
general.columns.account = addWordTo(general.columns.account, 'جنسیت', 'جنسیت ها', 'gender');

general.columns = addWordTo(general.columns, 'شناسه', null, 'id');
general.columns = addWordTo(general.columns, 'نام', null, 'name');
general.columns = addWordTo(general.columns, 'نوبت ها', null, 'visits');
general.columns = addWordTo(general.columns, 'سفارش', 'سفارشات', 'order');
general.columns = addWordTo(general.columns, 'اقدام', 'اقدامات', 'action');
general.columns = addWordTo(general.columns, 'زمان ایجاد', null, 'created_at');
general.columns = addWordTo(general.columns, 'زمان بروز رسانی', null, 'updated_at');

export { general };
