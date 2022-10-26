import { addWordTo } from "../../../translate";

let order = {};

order = addWordTo(order, 'سفارش', 'سفارشات', 'order');
order = addWordTo(order, 'ناحیه', 'ناحیه ها', 'part');
order = addWordTo(order, 'پکیج', 'پکیج ها', 'package');
order = addWordTo(order, 'انتخاب ناحیه', 'انتخاب ناحیه ها', 'select-part');
order = addWordTo(order, 'انتخاب پکیج', 'انتخاب پکیج ها', 'select-package');
order = addWordTo(order, 'ناحیه منتخب', 'ناحیه های منتخب', 'selected-part');
order = addWordTo(order, 'پکیج منتخب', 'پکیج های منتخب', 'selected-package');

order.timeResult = 'کل زمان مورد نیاز: ';
order.priceResult = 'قیمت: ';
order.priceWithoutDiscountResult = 'قیمت بدون تخفیف: ';
order.successMessage = 'سفارش شما با موفقیت ایجاد شد و شما به صفحه نوبت دهی منتقل می شوید برای انتخاب زمان نوبت';
order.redirectMessage = 'سفارش شما با موفقیت ایجاد شد و شما به صفحه نوبت دهی منتقل می شوید برای انتخاب زمان نوبت';
order.submitOrder = 'ارسال سفارش';
order.anotherUserButton = 'یک کاربر دیگر را به عنوان صاحب این سفارش تایین کنید';
order['regular-order-submition-warning'] = 'آیا مطمین هستید که می خواهید یک سفارش معمولی بدهید؟';
order['your-orders'] = 'سفارشات شما';
order['others-orders'] = 'سفارشات دیگران';
order['laser-orders'] = 'سفارشات لیزر';
order['regular-orders'] = 'سفارشات معمولی';
order['create-self-regular-order'] = 'یک سفارش معمولی برای خود ایجاد کنید';
order['another-user-required'] = 'باید یک کاربر دیگر برای این اقدام انتخاب کنید';

order.columns = {};

order.columns.packages = order.package.plural.ucFirstLetterFirstWord;
order.columns.parts = order.part.plural.ucFirstLetterFirstWord;
order.columns.needed_time = 'زمان مورد نیاز';
order.columns.price = 'قیمت';
order.columns.price_with_discount = 'قیمت بدون تخفیف';

order['total-price'] = 'قیمت کل';
order['total-priceWithoutDiscount'] = 'قیمت کل بدون تخفیف';
order['total-neededTime'] = 'کل زمان مورد نیاز';

export { order };
