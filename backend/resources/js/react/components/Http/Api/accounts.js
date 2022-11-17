import { fetchData } from "../fetch";

async function post_account_admin({
    roleName,
    phonenumber,
    firstname,
    lastname,
    username,
    password,
    password_confirmation,
    gender,
    token }) {
    let data = {
        userAttributes: {
            phonenumber: phonenumber,
            firstname: firstname,
            lastname: lastname,
            username: username,
            password: password,
            password_confirmation: password_confirmation,
            gender: gender
        }
    };

    return await fetchData('post', '/account/admin/' + roleName, data, { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' });
}

async function post_account_doctor({
    roleName,
    phonenumber,
    firstname,
    lastname,
    username,
    password,
    password_confirmation,
    gender,
    token }) {
    let data = {
        userAttributes: {
            phonenumber: phonenumber,
            firstname: firstname,
            lastname: lastname,
            username: username,
            password: password,
            password_confirmation: password_confirmation,
            gender: gender
        }
    };

    return await fetchData('post', '/account/doctor/' + roleName, data, { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' });
}

async function post_account_secretary({
    roleName,
    phonenumber,
    firstname,
    lastname,
    username,
    password,
    password_confirmation,
    gender,
    token }) {
    let data = {
        userAttributes: {
            phonenumber: phonenumber,
            firstname: firstname,
            lastname: lastname,
            username: username,
            password: password,
            password_confirmation: password_confirmation,
            gender: gender
        }
    };

    return await fetchData('post', '/account/secretary/' + roleName, data, { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' });
}

async function post_account_operator({
    roleName,
    phonenumber,
    firstname,
    lastname,
    username,
    password,
    password_confirmation,
    gender,
    token }) {
    let data = {
        userAttributes: {
            phonenumber: phonenumber,
            firstname: firstname,
            lastname: lastname,
            username: username,
            password: password,
            password_confirmation: password_confirmation,
            gender: gender
        }
    };

    return await fetchData('post', '/account/operator/' + roleName, data, { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' });
}

async function post_account_patient({
    roleName,
    phonenumber,
    firstname,
    lastname,
    username,
    password,
    password_confirmation,
    gender,
    age,
    state,
    city,
    address,
    token}) {
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
        userAccountAttributes: {
            age: age,
            state: state,
            city: city,
            address: address
        }
    };

    return await fetchData('post', '/account/patient/' + roleName, data, { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' });
}

async function get_accounts(roleName, count, lastAccountId = null, token) {
    let r = await fetchData('get', '/accounts?roleName=' + roleName + '&count=' + count + (lastAccountId === null ? '' : '&lastAccountId=' + lastAccountId), {}, { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' });
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
    return await fetchData('get', '/account', {}, { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' });
}

async function get_account_by(placeholder, token) {
    return await fetchData('get', '/account/' + placeholder, {}, { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' });
}

async function delete_account(accountId, token) {
    return await fetchData('delete', '/account/' + accountId, {}, { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' });
}

async function put_account(accountId, userAttributes = null, userAccountAttributes = null, token) {
    let data = {};

    if (userAttributes !== null) { data.userAttributes = userAttributes; }

    if (userAccountAttributes !== null) { data.userAccountAttributes = userAccountAttributes; }

    return await fetchData('put', '/account/' + accountId, data, { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' });
}

async function put_avatar(accountId, avatar, token) {
    let data = new FormData();
    data.append('avatar', avatar);

    return await fetchData('post', '/avatar/' + accountId, data, { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' });
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
    put_avatar,
    get_account_by,
    get_accounts,
    get_accountsCount,
    delete_account,
    put_account
}
