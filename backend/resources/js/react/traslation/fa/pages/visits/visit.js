import { addWordTo } from "../../../translate";

let visit = {};

visit = addWordTo(visit, 'نوبت', 'نوبت ها', 'visit');
visit['choose-one-order'] = 'لطفا یک سفارش را برای نوبت جدبد انتخاب کنید';
visit['laser-visit'] = 'نوبت های لیزر';
visit['regular-visit'] = 'نوبت های معمولی';
visit['your-visit'] = 'نوبت های شما';
visit['others-visit'] = 'نوبت های دیگران';
visit.title = 'نوبت';
visit.closest = 'نزدیکترین';
visit['weekly-search'] = 'جست و جوی هفتگی';
visit['visit-accuracy-warning'] = 'توجه داشته باشید که نوبت های نشان داده شده ممکن است از دست رس خارج شوند و برای اطمینان از دکمه بروزرسانی استفاده کنید';
visit['closest-visit-available'] = 'نزدیکترین نوبت موجود: ';
visit['weekly-visit-available'] = 'نزدیکترین نوبت هفتگی موجود: ';
visit['week-of-the-day'] = 'روز هفته';
visit['current-timezone'] = 'به وقت: ';

visit.columns = {
    weekly_time_patterns: 'دوره های زمانی هفتگی',
    date_time_period: 'دوره های زمانی',
    visit_timestamp: 'تاریخ نوبت',
    consuming_time: 'زمان نوبت',
};

export { visit };
