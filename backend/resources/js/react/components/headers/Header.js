import React, { Component } from 'react';
import { Navigate } from 'react-router-dom';

import ThemeButton from '../buttons/ThemeButton.js';
import AppLocalButton from '../buttons/AppLocalButton.js';

import LogoutIcon from '@mui/icons-material/Logout';
import LoginIcon from '@mui/icons-material/Login';

import AppBar from '@mui/material/AppBar';
import Box from '@mui/material/Box';
import Toolbar from '@mui/material/Toolbar';
import Typography from '@mui/material/Typography';
import { Button, ButtonGroup } from '@mui/material';

import { fetchData } from '../Http/fetch.js';
import { updateState } from '../helpers.js';
import { userLoggedOut } from '../../../redux/reducers/auth.js';
import store from '../../../redux/store.js';
import UserIconNavigator from '../UserIconNavigator.js';
import { connect } from 'react-redux';

import DiamondOutlinedIcon from '@mui/icons-material/DiamondOutlined';

export class Header extends Component {
    constructor(props) {
        super(props);

        this.onLogout = this.onLogout.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            goToWelcomePage: false,
            goToRegister: false,
            goToLogIn: false
        };
    }

    async onLogout(e) {
        let r = await fetchData('get', '/logout', {}, { 'X-CSRF-TOKEN': this.state.token }, [], false);
        if (r.response.status !== 200) {
            return;
        }

        await updateState(this, { goToWelcomePage: true });
        this.props.dispatch(userLoggedOut());
    }

    render() {
        let reduxStore = store.getState();

        return (
            <>
                <Box sx={{ flexGrow: 1 }}>
                    <AppBar position="static">
                        <Toolbar sx={{ flexWrap: 'wrap' }}>
                            {this.props.leftSide}

                            <DiamondOutlinedIcon fontSize="large" sx={{ m: 0.5 }} />

                            <Typography variant="h6" component="div" sx={{ flexGrow: 1 }}>
                                {this.props.title}
                            </Typography>

                            {this.props.rightSide}

                            {reduxStore.auth.isAuthenticated ?
                                (
                                    <>
                                        <UserIconNavigator image={reduxStore.auth.avatar} isEmailVerified={reduxStore.auth.isEmailVerified} />

                                        <Button sx={{ m: 1 }} size='small' variant={'contained'} onClick={this.onLogout} >
                                            <LogoutIcon size='small' />
                                        </ Button>
                                    </>
                                ) :
                                window.location.pathname !== '/login' &&
                                (
                                    <Button sx={{ m: 1 }} size='small' variant={'contained'} onClick={(e) => { this.setState({ goToLogIn: true }) }}>
                                        <LoginIcon size='small' />
                                    </ Button>
                                )
                            }

                            <ButtonGroup variant="contained" size='small'>
                                <ThemeButton />
                                <AppLocalButton />
                            </ButtonGroup>
                        </Toolbar>
                    </AppBar>
                    {/* <Toolbar /> */}
                    {this.state.goToRegister && <Navigate to={'/register'} />}
                    {this.state.goToLogIn && <Navigate to={'/login'} />}
                    {this.state.goToWelcomePage && window.location.pathname !== '/' && <Navigate to='/' />}
                </Box>
            </>
        )
    }
}

const mapStateToProps = state => ({
    redux: state
});

export default connect(mapStateToProps)(Header)
