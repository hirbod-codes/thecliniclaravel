import { createSlice } from '@reduxjs/toolkit';

export const auth = createSlice({
    name: 'auth',
    initialState: {
        account: null,
        avatar: null,
        isAuthenticated: false,
        userLogged: null,
        isEmailVerified: false
    },
    reducers: {
        gotAccount: (state, action) => { state.account = action.payload; },
        gotAvatar: (state, action) => { state.avatar = action.payload; },
        isEmailVerified: (state, action) => { state.isEmailVerified = action.payload; },
        isAuthenticated: (state, action) => { state.isAuthenticated = true; },
        isNotAuthenticated: (state, action) => { state.isAuthenticated = false; },
        userLoggedIn: (state, action) => { state.userLogged = 'in'; },
        userLoggedOut: (state, action) => { state.userLogged = 'out'; },
    }
});

export const { loggedIn, loggedOut, isEmailVerified, gotAccount, gotAvatar, isAuthenticated, isNotAuthenticated, userLoggedIn, userLoggedOut } = auth.actions;

export default auth.reducer;