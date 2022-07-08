import { addWordTo } from "../../../translate";

let order = {};

order = addWordTo(order, 'part', 'parts');
order = addWordTo(order, 'package', 'packages');
order = addWordTo(order, 'selected part', 'selected parts', 'selected-part');
order = addWordTo(order, 'selected package', 'selected packages', 'selected-package');

order.timeResult = 'Total needed time: ';
order.priceResult = 'Price: ';
order.priceWithoutDiscountResult = 'Price without discount: ';
order.successMessage = 'Your order successfully created, you\'re being redirected to visits page in order to choose a visit time.';
order.redirectMessage = 'Your order successfully created, you\'re being redirected to visits page in order to choose a visit time.';
order.submitOrder = 'Submit Order';
order.anotherUserButton = 'Set another user as this order\'s owner';
order['regular-order-submition-warning'] = 'Are you sure you want to submit a regular order?';

export { order };
