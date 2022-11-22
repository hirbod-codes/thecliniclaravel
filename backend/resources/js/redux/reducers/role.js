import { createSlice } from '@reduxjs/toolkit';

export const role = createSlice({
    name: 'role',
    initialState: {
        roles: null
    },
    reducers: {
        gotRoles: (state, action) => {
            state.roles = action.payload;
        }
    }
});

export const { gotRoles } = role.actions;

export default role.reducer;