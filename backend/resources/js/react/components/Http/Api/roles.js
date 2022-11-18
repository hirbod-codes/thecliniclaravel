import { fetchData } from "../fetch";

async function get_dataType(roleName, token) {
    return await fetchData('get', '/dataType?roleName=' + roleName, {}, { 'X-CSRF-TOKEN': token });
}

async function get_role_name(accountId, token) {
    return await fetchData('get', '/role-name?accountId=' + accountId, {}, { 'X-CSRF-TOKEN': token });
}

export {
    get_dataType,
    get_role_name,
}
