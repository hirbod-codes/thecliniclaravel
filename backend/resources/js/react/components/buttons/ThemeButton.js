import React, { Component } from 'react';

import { gotTheme } from '../../../redux/reducers/theme.js';
import store from '../../../redux/store.js';
import { connect } from 'react-redux';
import { Button } from '@mui/material';

import DarkModeIcon from '@mui/icons-material/DarkMode';
import LightModeIcon from '@mui/icons-material/LightMode';

export class ThemeButton extends Component {
    render() {
        let theme = store.getState().theme.theme;
        return (
            <Button
                size='small'
                startIcon={theme.includes('dark') ? <DarkModeIcon /> : <LightModeIcon />}
                onClick={(e) => {
                    let theme = store.getState().theme.theme;
                    this.props.dispatch(gotTheme(theme.includes('dark') ? theme.replace('dark', 'light') : theme.replace('light', 'dark')));
                }}
            />
        )
    }
}

const mapStateToProps = state => ({
    redux: state
});

export default connect(mapStateToProps)(ThemeButton)
