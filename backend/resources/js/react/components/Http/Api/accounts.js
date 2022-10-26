import { fetchData } from "../fetch";

async function post_account_admin(
    roleName,
    phonenumber,
    firstname,
    lastname,
    username,
    password,
    password_confirmation,
    gender,
    avatar = null,
    token) {
    let data = {
        userAttributes: {
            phonenumber: phonenumber,
            firstname: firstname,
            lastname: lastname,
            username: username,
            password: password,
            password_confirmation: password_confirmation,
            gender: gender
        },
        userAccountAttributes: {}
    };

    if (avatar !== null) { data.avatar = avatar; }

    return await fetchData('post', '/account/admin/' + roleName, data, { 'X-CSRF-TOKEN': token });
}

async function post_account_doctor(
    roleName,
    phonenumber,
    firstname,
    lastname,
    username,
    password,
    password_confirmation,
    gender,
    avatar = null,
    token) {
    let data = {
        userAttributes: {
            phonenumber: phonenumber,
            firstname: firstname,
            lastname: lastname,
            username: username,
            password: password,
            password_confirmation: password_confirmation,
            gender: gender
        },
        userAccountAttributes: {}
    };

    if (avatar !== null) { data.avatar = avatar; }

    return await fetchData('post', '/account/doctor/' + roleName, data, { 'X-CSRF-TOKEN': token });
}

async function post_account_secretary(
    roleName,
    phonenumber,
    firstname,
    lastname,
    username,
    password,
    password_confirmation,
    gender,
    avatar = null,
    token) {
    let data = {
        userAttributes: {
            phonenumber: phonenumber,
            firstname: firstname,
            lastname: lastname,
            username: username,
            password: password,
            password_confirmation: password_confirmation,
            gender: gender
        },
        userAccountAttributes: {}
    };

    if (avatar !== null) { data.avatar = avatar; }

    return await fetchData('post', '/account/secretary/' + roleName, data, { 'X-CSRF-TOKEN': token });
}

async function post_account_operator(
    roleName,
    phonenumber,
    firstname,
    lastname,
    username,
    password,
    password_confirmation,
    gender,
    avatar = null,
    token) {
    let data = {
        userAttributes: {
            phonenumber: phonenumber,
            firstname: firstname,
            lastname: lastname,
            username: username,
            password: password,
            password_confirmation: password_confirmation,
            gender: gender
        },
        userAccountAttributes: {}
    };

    if (avatar !== null) { data.avatar = avatar; }

    return await fetchData('post', '/account/operator/' + roleName, data, { 'X-CSRF-TOKEN': token });
}

async function post_account_patient(
    roleName,
    phonenumber,
    firstname,
    lastname,
    username,
    password,
    password_confirmation,
    gender,
    avatar = null,
    token) {
    let data = {
        userAttributes: {
            phonenumber: phonenumber,
            firstname: firstname,
            lastname: lastname,
            username: username,
            password: password,
            password_confirmation: password_confirmation,
            gender: gender
        },
        userAccountAttributes: {}
    };

    if (avatar !== null) { data.avatar = avatar; }

    return await fetchData('post', '/account/patient/' + roleName, data, { 'X-CSRF-TOKEN': token });
}

async function get_accounts(roleName, count, lastAccountId = null, token) {
    let r = await fetchData('get', '/accounts?roleName=' + roleName + '&count=' + count + (lastAccountId === null ? '' : '&lastAccountId=' + lastAccountId), {}, { 'X-CSRF-TOKEN': token });
    let accounts = r.value;

    let results = [];
    for (const k in accounts) {
        if (Object.hasOwnProperty.call(accounts, k)) {
            let account = accounts[k];
            let user = account.user;

            delete account.user;

            results.push({ user: user, account: account });
        }
    }

    return { value: results, response: r.response };
}

async function get_account(token) {
    return await fetchData('get', '/account', {}, { 'X-CSRF-TOKEN': token });
}

async function delete_account(accountId, token) {
    return await fetchData('delete', '/account/' + accountId, {}, { 'X-CSRF-TOKEN': token });
}

async function put_account(accountId, userAttributes = null, userAccountAttributes = null, avatar = null, token) {
    let data = {};

    if (userAttributes !== null) { data.userAttributes = userAttributes; }

    if (userAccountAttributes !== null) { data.userAccountAttributes = userAccountAttributes; }

    if (avatar !== null) { data.avatar = avatar; }

    return await fetchData('put', '/account/' + accountId, data, { 'X-CSRF-TOKEN': token });
}

async function get_accountsCount(roleName, token) {
    return await fetchData('get', '/accountsCount?roleName=' + roleName, {}, { 'X-CSRF-TOKEN': token });
}

export {
    post_account_admin,
    post_account_doctor,
    post_account_secretary,
    post_account_operator,
    post_account_patient,
    get_account,
    get_accounts,
    get_accountsCount,
    delete_account,
    put_account
}
