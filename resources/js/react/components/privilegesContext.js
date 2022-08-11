import React from 'react';

let PrivilegesContext = React.createContext({});

/**
 *  let privileges = {
 *      role: '',
 *      createUser: ['admin', ...],
 *      createOrder: {laser: ['admin', ...], ,,,},
 *      createVisit: {laser: ['admin', ...], ,,,},
 *      retrieveUser: ['admin', ...],
 *      retrieveOrder: {laser: ['admin', ...], ,,,},
 *      retrieveVisit: {laser: ['admin', ...], ,,,},
 *      updateUser: [{name: 'admin', column: 'username'}, ...],
 *      deleteUser: ['admin', ...],
 *      deleteOrder: {laser: ['admin', ...], ,,,},
 *      deleteVisit: {laser: ['admin', ...], ,,,},
 *      privileges: {'name': {'admin': {} , ...}, ...}
 *      updatableColumns: {'admin':['id', 'firstname', ...], ...}
 *  };
 */

function formatPrivileges(privileges) {
    console.log('privileges');
    console.log(privileges);
    if (privileges === null) { return PrivilegesContext._currentValue; }

    let result = {};

    result = privileges;
    result.role = privileges.role;

    let updatableColumns = {};
    let selfUpdatableColumns = [];
    for (let i = 0; i < privileges.updateUser.length; i++) {
        const v = privileges.updateUser[i];

        if (updatableColumns[v.name] === undefined) {
            updatableColumns[v.name] = [];
        }

        if (updatableColumns[v.name] !== undefined && updatableColumns[v.name].indexOf(v.column) === -1) {
            updatableColumns[v.name].push(v.column);
        }

        if (v.name === 'self' && selfUpdatableColumns.indexOf(v.column) === -1) {
            selfUpdatableColumns.push(v.column);
        }
    }
    result.updatableColumns = updatableColumns;
    result.selfUpdatableColumns = selfUpdatableColumns;

    console.log('result');
    console.log(result);
    return result;
}

export { PrivilegesContext, formatPrivileges };
