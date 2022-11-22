function canReadSelfUser(store) {
    let roles = store.getState().role.roles.retrieveUser;

    return roles.indexOf('self') !== -1;
}

function canUpdateSelfUser(store) {
    return store.getState().role.roles.updateUser.filter((v) => v.name === 'self').length > 0;
}

function canUpdateSelfUserColumn(column, store) {
    return store.getState().role.roles.updateUser.filter((v) => v.name === 'self' && v.column === column).length > 0;
}

function canDeleteSelfUser(store) {
    let roles = store.getState().role.roles.deleteUser;

    return roles.indexOf('self') !== -1;
}

function canCreateUsers(store) {
    let roles = store.getState().role.roles.createUser;

    return roles.filter((v) => v !== 'self').length !== 0;
}

function canReadUsers(store) {
    let roles = store.getState().role.roles.retrieveUser;

    return roles.filter((v) => v !== 'self').length !== 0;
}

function canUpdateUsers(store) {
    return store.getState().role.roles.updateUser.filter((v) => v.name !== 'self').length > 0;
}

function canDeleteUsers(store) {
    let roles = store.getState().role.roles.deleteUser;

    return roles.filter((v) => v !== 'self').length !== 0;
}

function canCreateUser(role, store) {
    let roles = store.getState().role.roles.createUser;

    return roles.filter((v) => v !== 'self').indexOf(role) !== -1;
}


function canReadUser(role, store) {
    let roles = store.getState().role.roles.retrieveUser;

    return roles.filter((v) => v !== 'self').indexOf(role) !== -1;
}

function canUpdateUser(role, store) {
    return store.getState().role.roles.updateUser.filter((v) => v.name === role).length > 0;
}

function canUpdateUserColumn(role, column, store) {
    return store.getState().role.roles.updateUser.filter((v) => v.name === role && v.column === column).length > 0;
}


function canDeleteUser(role, store) {
    let roles = store.getState().role.roles.deleteUser;

    return roles.filter((v) => v !== 'self').indexOf(role) !== -1;
}

function canSelfEditAvatar(store) {
    let roles = store.getState().role.roles.privileges.editAvatar;
    return roles.self !== undefined && roles.self.boolean_value !== undefined &&  Boolean(roles.self.boolean_value) === true;
}

function canEditAvatar(role, store) {
    let roles = store.getState().role.roles.privileges.editAvatar;
    return roles[role] !== undefined && roles[role].boolean_value !== undefined &&  Boolean(roles[role].boolean_value) === true;
}

export {
    canReadSelfUser,
    canUpdateSelfUser,
    canDeleteSelfUser,
    canUpdateSelfUserColumn,
    canCreateUsers,
    canReadUsers,
    canUpdateUsers,
    canDeleteUsers,
    canCreateUser,
    canReadUser,
    canUpdateUser,
    canUpdateUserColumn,
    canDeleteUser,
    canSelfEditAvatar,
    canEditAvatar,
};