import { createSlice } from '@reduxjs/toolkit';

export const theme = createSlice({
    name: 'theme',
    initialState: {
        theme: null,
    },
    reducers: {
        gotTheme: (state, action) => {
            state.theme = action.payload;
        }
    }
});

export const { gotTheme } = theme.actions;

export default theme.reducer;