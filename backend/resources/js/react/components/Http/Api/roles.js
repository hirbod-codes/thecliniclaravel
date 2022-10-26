import { fetchData } from "../fetch";

async function get_dataType(roleName, token) {
    return await fetchData('get', '/dataType?roleName=' + roleName, {}, { 'X-CSRF-TOKEN': token });
}

export {
    get_dataType
}
