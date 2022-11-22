import { configureStore } from '@reduxjs/toolkit';
import { combineReducers } from 'redux';
import auth from './reducers/auth.js';
import local from './reducers/local.js';
import role from './reducers/role.js';
import theme from './reducers/theme.js';

const store = configureStore({
    reducer: combineReducers({
        auth: auth,
        role: role,
        local: local,
        theme: theme,
    })
});

export function observeStore(store, select, currentState, onChange) {
    function handleChange() {
        let nextState = select(store.getState());
        if (nextState !== currentState) {
            currentState = nextState;
            onChange(currentState);
        }
    }

    let unsubscribe = store.subscribe(handleChange);
    handleChange();
    return unsubscribe;
}

export default store;