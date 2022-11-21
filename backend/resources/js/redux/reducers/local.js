import { createSlice } from '@reduxjs/toolkit';

export const local = createSlice({
    name: 'local',
    initialState: {
        locals: [],
        local: null,
        localName: null,
    },
    reducers: {
        gotLocals: (state, action) => {
            state.locals = action.payload;
        },
        gotLocal: (state, action) => {
            console.log('gotLocal action :>> ', action);
            state.local = action.payload;
        },
        setLocal: (state, action) => {
            console.log('setLocal action :>> ', action);
            state.localName = action.payload;
        }
    }
});

export const { gotLocals, gotLocal, setLocal } = local.actions;

export default local.reducer;