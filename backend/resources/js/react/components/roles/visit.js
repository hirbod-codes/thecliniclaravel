function canCreateSelfVisit(business, store) {
    let roles = store.getState().role.roles.createVisit[business];

    return roles.indexOf('self') !== -1;
}

function canReadSelfVisit(business, store) {
    let roles = store.getState().role.roles.retrieveVisit[business];

    return roles.indexOf('self') !== -1;
}

function canDeleteSelfVisit(business, store) {
    let roles = store.getState().role.roles.deleteVisit[business];

    return roles.indexOf('self') !== -1;
}

function canCreateVisits(business, store) {
    let roles = store.getState().role.roles.createVisit[business];

    return roles.filter((v) => v !== 'self').length !== 0;
}

function canReadVisits(business, store) {
    let roles = store.getState().role.roles.retrieveVisit[business];

    return roles.filter((v) => v !== 'self').length !== 0;
}

function canDeleteVisits(business, store) {
    let roles = store.getState().role.roles.deleteVisit[business];

    return roles.filter((v) => v !== 'self').length !== 0;
}

function canCreateVisit(role, business, store) {
    let roles = store.getState().role.roles.createVisit[business];

    return roles.filter((v) => v !== 'self').indexOf(role) !== -1;
}


function canReadVisit(role, business, store) {
    let roles = store.getState().role.roles.retrieveVisit[business];

    return roles.filter((v) => v !== 'self').indexOf(role) !== -1;
}


function canDeleteVisit(role, business, store) {
    let roles = store.getState().role.roles.deleteVisit[business];

    return roles.filter((v) => v !== 'self').indexOf(role) !== -1;
}

export {
    canCreateSelfVisit,
    canReadSelfVisit,
    canDeleteSelfVisit,
    canCreateVisits,
    canReadVisits,
    canDeleteVisits,
    canCreateVisit,
    canReadVisit,
    canDeleteVisit,
};