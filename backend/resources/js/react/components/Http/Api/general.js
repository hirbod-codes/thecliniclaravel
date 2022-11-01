import { fetchData } from "../fetch";

async function get_work_schedule(token) {
    return await fetchData('get', '/work-schedule', {}, { 'X-CSRF-TOKEN': token });
}

async function get_genders(token) {
    return await fetchData('get', '/genders', {}, { 'X-CSRF-TOKEN': token });
}

async function get_states(token) {
    return await fetchData('get', '/states', {}, { 'X-CSRF-TOKEN': token });
}

async function get_cities(stateName, token) {
    return await fetchData('get', '/cities?stateName=' + stateName, {}, { 'X-CSRF-TOKEN': token });
}

export {
    get_work_schedule,
    get_genders,
    get_states,
    get_cities,
}
