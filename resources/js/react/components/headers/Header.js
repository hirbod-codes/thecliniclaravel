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

export class Header extends Component {
    constructor(props) {
        super(props);

        this.onLogout = this.onLogout.bind(this);

        this.state = {
            goToWelcomePage: false,
        };
    }

    onLogout(e) {
        console.log(this.state);
        this.setState({ goToWelcomePage: true });
        if (this.props.onLogout !== undefined) {
            this.props.onLogout();
        } else {
            window.location.href = '/logout';
        }
    }

    render() {
        console.log(this.state);
        if (this.state.goToWelcomePage) {
            return (
                <Navigate to='/' />
            );
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
