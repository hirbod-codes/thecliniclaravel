function canCreateSelfOrder(business, store) {
    let roles = store.getState().role.roles.createOrder[business];

    return roles.indexOf('self') !== -1;
}

function canReadSelfOrder(business, store) {
    let roles = store.getState().role.roles.retrieveOrder[business];

    return roles.indexOf('self') !== -1;
}

function canDeleteSelfOrder(business, store) {
    let roles = store.getState().role.roles.deleteOrder[business];

    return roles.indexOf('self') !== -1;
}

function canCreateOrders(business, store) {
    let roles = store.getState().role.roles.createOrder[business];

    return roles.filter((v) => v !== 'self').length !== 0;
}

function canReadOrders(business, store) {
    let roles = store.getState().role.roles.retrieveOrder[business];

    return roles.filter((v) => v !== 'self').length !== 0;
}

function canDeleteOrders(business, store) {
    let roles = store.getState().role.roles.deleteOrder[business];

    return roles.filter((v) => v !== 'self').length !== 0;
}

function canCreateOrder(role, business, store) {
    let roles = store.getState().role.roles.createOrder[business];

    return roles.filter((v) => v !== 'self').indexOf(role) !== -1;
}


function canReadOrder(role, business, store) {
    let roles = store.getState().role.roles.retrieveOrder[business];

    return roles.filter((v) => v !== 'self').indexOf(role) !== -1;
}


function canDeleteOrder(role, business, store) {
    let roles = store.getState().role.roles.deleteOrder[business];

    return roles.filter((v) => v !== 'self').indexOf(role) !== -1;
}

function canEditRegularOrderPrice(role, store) {
    let roles = store.getState().role.roles.privileges.editRegularOrderPrice;
    return roles[role] !== undefined && Boolean(roles[role].boolean_value) === true;
}

function canEditRegularOrderNeededTime(role, store) {
    let roles = store.getState().role.roles.privileges.editRegularOrderNeededTime;
    return roles[role] !== undefined && Boolean(roles[role].boolean_value) === true;
}

export {
    canEditRegularOrderNeededTime,
    canEditRegularOrderPrice,
    canCreateSelfOrder,
    canReadSelfOrder,
    canDeleteSelfOrder,
    canCreateOrders,
    canReadOrders,
    canDeleteOrders,
    canCreateOrder,
    canReadOrder,
    canDeleteOrder,
};