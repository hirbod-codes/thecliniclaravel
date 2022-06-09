import React, { Component } from 'react'
import { Link } from 'react-router-dom';

import { Button } from '@mui/material';

import { translate } from '../../traslation/translate'
import { backendURL, getJsonData } from '../Http/fetch';
import Header from '../headers/Header'
import UserIconNavigator from '../UserIconNavigator.js';
import LoadingButton from '@mui/lab/LoadingButton';

export class WelcomePage extends Component {
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
                <Header
                    title={translate('general/welcome/ucFirstLetterFirstWord', this.props.currentLocaleName)}
                    rightSide={!this.state.isAuthenticationLoading ?
                        (
                            this.state.isAuthenticated ?
                                (
                                    <>
                                        <UserIconNavigator currentLocaleName={this.props.currentLocaleName} />
                                        <Button type='button' sx={{ a: { textDecoration: 'none', color: 'white' }, m: 1 }}>
                                            <Link to="/logout" >{translate('general/log-out/ucFirstLetterAllWords', this.props.currentLocaleName)}</Link>
                                        </Button>
                                    </>
                                ) :
                                <Button type='button' sx={{ a: { textDecoration: 'none', color: 'white' }, m: 1 }}>
                                    <Link to="/login" >{translate('general/log-in/ucFirstLetterAllWords', this.props.currentLocaleName)}</Link>
                                </Button>
                        ) :
                        <LoadingButton loading variant='contained' >UserIconNavigator</LoadingButton>
                    } />
            </>
        )
    }
}

export default WelcomePage
