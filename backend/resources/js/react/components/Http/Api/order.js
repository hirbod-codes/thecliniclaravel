import { fetchData } from "../fetch";

async function get_orders_laser(
    roleName = null,
    priceOtherwiseTime = null,
    username = null,
    lastOrderId = null,
    count = null,
    operator = null,
    price = null,
    timeConsumption = null,
    token) {

    let url = '/orders/laser?';

    if (username !== null) {
        url += 'username=' + username;
        url += '&';
    } else {
        url += 'roleName=' + roleName;
        url += '&';

        url += 'count=' + count;
        url += '&';

        if (lastOrderId !== null) {
            url += 'lastOrderId=' + lastOrderId;
            url += '&';
        }
    }

    if (priceOtherwiseTime !== null) {
        if (operator !== null && (price !== null || timeConsumption !== null)) {
            url += 'priceOtherwiseTime=' + priceOtherwiseTime + '&operator=' + operator + '&price=' + price + '&timeConsumption=' + timeConsumption;
        }
    }

    return await fetchData('get', url, {}, { 'X-CSRF-TOKEN': token });
}

async function get_orders_regular(
    roleName,
    priceOtherwiseTime,
    username,
    lastOrderId,
    count,
    operator,
    price,
    timeConsumption,
    token) {

    let url = '/orders/regular?';

    if (username !== null) {
        url += 'username=' + username;
        url += '&';
    } else {
        url += 'roleName=' + roleName;
        url += '&';

        url += 'count=' + count;
        url += '&';

        if (lastOrderId !== null) {
            url += 'lastOrderId=' + lastOrderId;
            url += '&';
        }
    }

    if (priceOtherwiseTime !== null) {
        if (operator !== null && (price !== null || timeConsumption !== null)) {
            url += 'priceOtherwiseTime=' + priceOtherwiseTime + '&operator=' + operator + '&price=' + price + '&timeConsumption=' + timeConsumption;
        }
    }

    return await fetchData('get', url, {}, { 'X-CSRF-TOKEN': token });
}

async function get_ordersCount(businessName, roleName, token) {
    return await fetchData('get', '/ordersCount?roleName=' + roleName + '&businessName=' + businessName, {}, { 'X-CSRF-TOKEN': token });
}

async function post_order(
    accountId,
    businessName,
    packages,
    parts,
    price,
    timeConsumption,
    token) {
    let data = {
        accountId: accountId,
        businessName: businessName,
        packages: packages,
        parts: parts,
        price: price,
        timeConsumption: timeConsumption,
    };

    return await fetchData('post', '/order', data, { 'X-CSRF-TOKEN': token });
}

async function delete_order(businessName, id, token) {
    return await fetchData('delete', '/order/' + businessName + '/' + id, {}, { 'X-CSRF-TOKEN': token });
}

async function get_parts(businessName, gender, token) {
    return await fetchData('get', '/' + businessName + '/parts?gender=' + gender, {}, { 'X-CSRF-TOKEN': token });
}

async function get_packages(businessName, gender, token) {
    return await fetchData('get', '/' + businessName + '/packages?gender=' + gender, {}, { 'X-CSRF-TOKEN': token });
}

async function get_laser_time_calculation(businessName, parts, packages, gender, token) {
    let data = { parts: parts, packages: packages, gender: gender };
    return await fetchData('post', '/' + businessName + '/time-calculation', data, { 'X-CSRF-TOKEN': token });
}

async function get_laser_price_calculation(businessName, parts, packages, gender, token) {
    let data = { parts: parts, packages: packages, gender: gender };
    return await fetchData('post', '/' + businessName + '/time-calculation', data, { 'X-CSRF-TOKEN': token });
}

export {
    get_orders_laser,
    get_orders_regular,
    get_ordersCount,
    post_order,
    delete_order,
    get_parts,
    get_packages,
    get_laser_time_calculation,
    get_laser_price_calculation,
}
