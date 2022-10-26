import React, { Component } from 'react';
import { Link, Navigate } from 'react-router-dom';

import ThemeButton from '../buttons/ThemeButton.js';
import AppLocalButton from '../buttons/AppLocalButton.js';
import { translate } from '../../traslation/translate.js';

import AppBar from '@mui/material/AppBar';
import Box from '@mui/material/Box';
import Toolbar from '@mui/material/Toolbar';
import Typography from '@mui/material/Typography';
import LoadingButton from '@mui/lab/LoadingButton';
import { Button } from '@mui/material';
import { fetchData } from '../Http/fetch.js';
import { updateState } from '../helpers.js';

export class Header extends Component {
    constructor(props) {
        super(props);

        this.onLogout = this.onLogout.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            goToWelcomePage: false,
        };
    }

    async onLogout(e) {
        let r = await fetchData('get', '/logout', {}, { 'X-CSRF-TOKEN': this.state.token });
        if (r.response.status !== 200) {
            return;
        }

        await updateState(this, { goToWelcomePage: true });
        if (this.props.onLogout !== undefined) {
            this.props.onLogout();
        } else {
            window.location.href = '/logout';
        }
    }

    render() {
        if (this.state.goToWelcomePage && window.location.pathname !== '/') {
            return (
                <Navigate to='/' />
            );
        } else {
            if (this.state.goToWelcomePage && window.location.pathname === '/') {
                this.setState({ goToWelcomePage: false });
            }
        }

        return (
            <>
                <Box sx={{ flexGrow: 1 }}>
                    <AppBar position="fixed">
                        <Toolbar>
                            {this.props.leftSide}

                            <Typography variant="h6" component="div" sx={{ flexGrow: 1 }}>
                                {this.props.title}
                            </Typography>

                            {this.props.rightSide}

                            {this.props.isAuthenticated ?
                                (
                                    this.props.isAuthenticationLoading ?
                                        <LoadingButton loading >AuthenticationLoading</LoadingButton> :
                                        <>
                                            {this.props.navigator}

                                            <Button onClick={this.onLogout} style={{ color: 'white', m: 1 }} >
                                                {translate('general/log-out/single/ucFirstLetterAllWords')}
                                            </ Button>
                                        </>
                                ) :
                                window.location.pathname === '/login' ?
                                    (
                                        <Link to='/register' style={{ textDecoration: 'none', color: 'white', m: 1 }} >
                                            {translate('general/sign-up/single/ucFirstLetterAllWords')}
                                        </ Link>
                                    ) :
                                    (
                                        <Link to='/login' style={{ textDecoration: 'none', color: 'white', m: 1 }} >
                                            {translate('general/log-in/single/ucFirstLetterAllWords')}
                                        </ Link>
                                    )
                            }

                            <ThemeButton buttonProps={{ sx: { m: 1 } }} />
                            <AppLocalButton buttonProps={{ sx: { m: 1 } }} />
                        </Toolbar>
                    </AppBar>
                    <Toolbar />
                </Box>
            </>
        )
    }
}

export default Header
