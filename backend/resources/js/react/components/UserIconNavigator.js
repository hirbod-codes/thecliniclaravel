import React, { Component } from 'react'
import { Navigate } from 'react-router-dom';

import { Avatar, Button, IconButton, Menu, MenuItem, Tooltip } from '@mui/material'

import { translate } from '../traslation/translate';
import { fetchData } from './Http/fetch';
import { updateState } from './helpers';
import SlidingDialog from './Menus/SlidingDialog';
import { connect } from 'react-redux';

export class UserIconNavigator extends Component {
    constructor(props) {
        super(props);

        this.handleResponseDialogClose = this.handleResponseDialogClose.bind(this);

        this.handleIconMenuOpen = this.handleIconMenuOpen.bind(this);
        this.handleModalOpen = this.handleModalOpen.bind(this);

        this.handleIconMenuClose = this.handleIconMenuClose.bind(this);
        this.handleModalClose = this.handleModalClose.bind(this);

        this.sendEmailVerificationCode = this.sendEmailVerificationCode.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            toDashboardAccount: false,
            toDashboardOrder: false,
            toDashboardVisit: false,

            anchorEl: null,
            open: false,

            isEmailVerified: false,
            modalOpen: false,
            emailVerificationSlideTimeout: 300,

            responseDialogOpen: false,
            responseErrors: [],
        };
    }

    componentDidMount() {
        this.initialize();
    }

    async initialize() {
        let isEmailVerified = await fetchData('get', '/isEmailVerified', {}, { 'X-CSRF-TOKEN': this.state.token }, [], false);
        isEmailVerified = isEmailVerified.value;

        await updateState(this, { isEmailVerified: isEmailVerified.verified });
    }

    sendEmailVerificationCode() {
        fetchData('post', '/email/verification-notification', {}, { 'X-CSRF-TOKEN': this.state.token }, [], false);
    }

    render() {
        return (
            <>
                <Tooltip title={translate('general/account/single/ucFirstLetterFirstWord')} >
                    <IconButton onClick={this.handleIconMenuOpen} >
                        <Avatar alt={translate('general/avatar/single/ucFirstLetterFirstWord')} src={this.props.image} />
                    </IconButton>
                </Tooltip>
                <Menu
                    anchorEl={this.state.anchorEl}
                    open={this.state.open}
                    onClose={this.handleIconMenuClose}
                >
                    {!this.props.isEmailVerified &&
                        <SlidingDialog
                            open={this.state.modalOpen}
                            onClose={this.handleModalClose}
                            timeout={this.state.emailVerificationSlideTimeout}
                            slideTrigger={
                                <MenuItem >
                                    <Button onClick={(e) => { this.handleModalOpen(); this.sendEmailVerificationCode(); }}>
                                        {translate('generalSentences/verify-email-address/ucFirstLetterFirstWord')}
                                    </Button>
                                </MenuItem>
                            }
                        >
                            {translate('generalSentences/send-email-verification-message/ucFirstLetterFirstWord')}
                        </SlidingDialog>
                    }

                    {window.location.pathname !== '/dashboard/account' &&
                        <MenuItem>
                            <Button onClick={() => { this.setState({ toDashboardAccount: true }); }} >
                                {translate('pages/account/account/account/single/ucFirstLetterFirstWord')}
                            </Button>
                        </MenuItem>
                    }
                    {this.state.toDashboardAccount && <Navigate to='/dashboard/account' />}

                    {window.location.pathname !== '/dashboard/order' &&
                        <MenuItem>
                            <Button onClick={() => { this.setState({ toDashboardOrder: true }); }} >
                                {translate('pages/orders/order/order/single/ucFirstLetterFirstWord')}
                            </Button>
                        </MenuItem>
                    }
                    {this.state.toDashboardOrder && <Navigate to='/dashboard/order' />}

                    {window.location.pathname !== '/dashboard/visit' &&
                        <MenuItem>
                            <Button onClick={() => { this.setState({ toDashboardVisit: true }); }} >
                                {translate('pages/visits/visit/visit/single/ucFirstLetterFirstWord')}
                            </Button>
                        </MenuItem>
                    }
                    {this.state.toDashboardVisit && <Navigate to='/dashboard/visit' />}
                </Menu>

                <SlidingDialog
                    open={this.state.responseDialogOpen}
                    slideTrigger={<div></div>}
                    onClose={this.handleResponseDialogClose}
                >
                    {this.state.responseErrors}
                </SlidingDialog>
            </>
        )
    }

    handleResponseDialogClose(e) {
        this.setState({ responseDialogOpen: false });
    }

    handleModalClose(e) {
        this.setState({ modalOpen: false });
    }

    async handleModalOpen(e) {
        this.setState({ modalOpen: true });
    }

    handleIconMenuClose(e) {
        this.setState({ anchorEl: null, open: false });
    }

    handleIconMenuOpen(e) {
        this.setState({ anchorEl: e.target, open: true });
    }
}

const mapStateToProps = state => ({
    redux: state
});

export default connect(mapStateToProps)(UserIconNavigator)
