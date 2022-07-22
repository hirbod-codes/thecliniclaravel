import { addWordTo } from "../../../translate";
import { general } from "../../general";

let order = {};

order = addWordTo(order, 'order', 'orders');
order = addWordTo(order, 'part', 'parts');
order = addWordTo(order, 'package', 'packages');
order = addWordTo(order, 'select part', 'select parts', 'select-part');
order = addWordTo(order, 'select package', 'select packages', 'select-package');
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
order['your-orders'] = 'Your Orders';
order['others-orders'] = 'Others Orders';
order['laser-orders'] = 'Laser Orders';
order['regular-orders'] = 'Regular Orders';
order['create-self-regular-order'] = 'Create a regular order for yourself';
order['another-user-required'] = 'You need to specify another user for this action.';

order.columns = {};

order.columns.packages = order.package.plural.ucFirstLetterFirstWord;
order.columns.parts = order.part.plural.ucFirstLetterFirstWord;
order.columns.neededTime = 'Needed time';
order.columns.price = 'Price';
order.columns.priceWithDiscount = order.columns.price + ' with discount';

order['total-price'] = general.total.single.ucFirstLetterFirstWord + ' ' + order.columns.price;
order['total-priceWithoutDiscount'] = general.total.single.ucFirstLetterFirstWord + ' ' + order.columns.priceWithDiscount;
order['total-neededTime'] = general.total.single.ucFirstLetterFirstWord + ' ' + order.columns.neededTime;

export { order };
