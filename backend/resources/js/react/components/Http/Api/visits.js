import { fetchData } from "../fetch";

async function get_visits_by_timestamp(
    businessName,
    roleName,
    sortByTimestamp,
    timestamp,
    operator,
    count,
    lastVisitTimestamp,
    token,
) {
    return await fetchData('get', '/visits?businessName=' + businessName + '&roleName=' + roleName + '&timestamp=' + timestamp + '&sortByTimestamp=' + sortByTimestamp + '&operator=' + operator + '&count=' + count + ((lastVisitTimestamp && lastVisitTimestamp !== 0) ? ('&lastVisitTimestamp=' + lastVisitTimestamp) : ''), {}, { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' });
}

async function get_visits_by_order(
    businessName,
    sortByTimestamp,
    orderId,
    token,
) {
    return await fetchData('get', '/visits?businessName=' + businessName + '&sortByTimestamp=' + sortByTimestamp + '&' + businessName + 'OrderId=' + orderId, {}, { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' });
}

async function get_visits_by_user(
    businessName,
    sortByTimestamp,
    accountId,
    token,
) {
    return await fetchData('get', '/visits?businessName=' + businessName + '&sortByTimestamp=' + sortByTimestamp + '&accountId=' + accountId, {}, { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' });
}

async function get_visitsCount(businessName, roleName, token) {
    return await fetchData('get', '/visitsCount?businessName=' + businessName + '&roleName=' + roleName, {}, { 'X-CSRF-TOKEN': token });
}

async function post_visit_laser(laserOrderId, weeklyTimePatterns, token) {
    let data = { laserOrderId: laserOrderId };

    if (weeklyTimePatterns !== null) {
        data.weeklyTimePatterns = weeklyTimePatterns;
    }

    return await fetchData('post', '/visit/laser', data, { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' });
}

async function post_visit_regular(regularOrderId, weeklyTimePatterns, token) {
    let data = { regularOrderId: regularOrderId };

    if (weeklyTimePatterns !== null) {
        data.weeklyTimePatterns = weeklyTimePatterns;
    }

    return await fetchData('post', '/visit/regular', data, { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' });
}

async function post_visit_laser_check(laserOrderId, weeklyTimePatterns = null, token) {
    let data = { laserOrderId: laserOrderId };

    if (weeklyTimePatterns !== null) {
        data.weeklyTimePatterns = weeklyTimePatterns;
    }

    return await fetchData('post', '/visit/laser/check', data, { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' });
}

async function post_visit_regular_check(regularOrderId, weeklyTimePatterns = null, token) {
    let data = { regularOrderId: regularOrderId };

    if (weeklyTimePatterns !== null) {
        data.weeklyTimePatterns = weeklyTimePatterns;
    }

    return await fetchData('post', '/visit/regular/check', data, { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' });
}


async function delete_visit_laser(visitId, token) {
    return await fetchData('delete', '/visit/laser/' + visitId, {}, { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' });
}

async function delete_visit_regular(visitId, token) {
    return await fetchData('delete', '/visit/regular/' + visitId, {}, { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' });
}

export {
    get_visits_by_timestamp,
    get_visits_by_order,
    get_visits_by_user,
    get_visitsCount,
    post_visit_laser,
    post_visit_regular,
    post_visit_laser_check,
    post_visit_regular_check,
    delete_visit_laser,
    delete_visit_regular,
}
