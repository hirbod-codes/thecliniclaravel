import React, { Component } from 'react';

import ThemeButton from '../buttons/ThemeButton.js';
import AppLocalButton from '../buttons/AppLocalButton.js';

import AppBar from '@mui/material/AppBar';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Toolbar from '@mui/material/Toolbar';
import Typography from '@mui/material/Typography';
import UserIconNavigator from '../UserIconNavigator.js';
import { translate } from '../../traslation/translate.js';
import LoadingButton from '@mui/lab/LoadingButton';
import { backendURL, getJsonData } from '../Http/fetch.js';
import { Link } from 'react-router-dom';

export class Header extends Component {
    constructor(props) {
        super(props);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            isAuthenticationLoading: true,
            isAuthenticated: false,
        };
    }

    componentDidMount() {
        getJsonData(backendURL() + '/isAuthenticated', { 'X-CSRF-TOKEN': this.state.token })
            .then((res) => {
                return res.json();
            })
            .then((data) => {
                this.setState({ isAuthenticated: data.authenticated, isAuthenticationLoading: false });
            });
    }

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

                            {this.state.isAuthenticationLoading && <LoadingButton loading variant='contained' >AuthenticationLoading</LoadingButton>}

                            {!this.state.isAuthenticationLoading &&
                                (
                                    this.state.isAuthenticated ?
                                        (
                                            <>
                                                <UserIconNavigator currentLocaleName={this.props.currentLocaleName} />

                                                <Button type='button' variant='contained' onClick={(e) => { window.location.href = '/logout'; }} sx={{ a: { textDecoration: 'none', color: 'white' }, m: 1 }} >
                                                    {translate('general/log-out/single/ucFirstLetterAllWords', this.props.currentLocaleName)}
                                                </Button>
                                            </>
                                        ) :
                                        (
                                            this.props.isLogInPage ?
                                                (
                                                    <Button type='button' variant='contained' sx={{ a: { textDecoration: 'none', color: 'white' }, m: 1 }} >
                                                        <Link to='/register' style={{ textDecoration: 'none', color: 'white' }} >
                                                            {translate('general/sign-up/single/ucFirstLetterAllWords', this.props.currentLocaleName)}
                                                        </ Link>
                                                    </Button>
                                                ) :
                                                (
                                                    <Button type='button' variant='contained' sx={{ a: { textDecoration: 'none', color: 'white' }, m: 1 }} >
                                                        <Link to='/login' style={{ textDecoration: 'none', color: 'white' }} >
                                                            {translate('general/log-in/single/ucFirstLetterAllWords', this.props.currentLocaleName)}
                                                        </ Link>
                                                    </Button>
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
