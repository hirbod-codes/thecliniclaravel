import React, { Component } from 'react';
import { Link } from 'react-router-dom';

import ThemeButton from '../buttons/ThemeButton.js';
import AppLocalButton from '../buttons/AppLocalButton.js';
import { translate } from '../../traslation/translate.js';

import AppBar from '@mui/material/AppBar';
import Box from '@mui/material/Box';
import Toolbar from '@mui/material/Toolbar';
import Typography from '@mui/material/Typography';
import LoadingButton from '@mui/lab/LoadingButton';

export class Header extends Component {
    render() {
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

                            {this.props.isAuthenticationLoading && <LoadingButton loading >AuthenticationLoading</LoadingButton>}

                            {!this.props.isAuthenticationLoading &&
                                (
                                    this.props.isAuthenticated ?
                                        (
                                            <>
                                                {this.props.navigator}

                                                <Link to='/logout' onClick={(e) => { window.location.href = '/logout'; }} style={{ color: 'white', m: 1 }} >
                                                    {translate('general/log-out/single/ucFirstLetterAllWords')}
                                                </ Link>
                                            </>
                                        ) :
                                        (
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
                                        )
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
