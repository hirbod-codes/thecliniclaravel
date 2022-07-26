import React, { Component } from 'react'
import { Link } from 'react-router-dom';

import { Avatar, FormHelperText, IconButton, Menu, MenuItem, Tooltip } from '@mui/material'
import LoadingButton from '@mui/lab/LoadingButton';

import { translate } from '../traslation/translate';
import { fetchData } from './Http/fetch';
import { updateState } from './helpers';
import SlidingDialog from './Menus/SlidingDialog';

export class UserIconNavigator extends Component {
    constructor(props) {
        super(props);

        this.handleResponseDialogClose = this.handleResponseDialogClose.bind(this);
        this.handleRegularOrderSubmition = this.handleRegularOrderSubmition.bind(this);

        this.handleIconMenuOpen = this.handleIconMenuOpen.bind(this);
        this.handleModalOpen = this.handleModalOpen.bind(this);
        this.handleOrderMenuOpen = this.handleOrderMenuOpen.bind(this);
        this.handleRegularOrderMenuOpen = this.handleRegularOrderMenuOpen.bind(this);

        this.handleIconMenuClose = this.handleIconMenuClose.bind(this);
        this.handleModalClose = this.handleModalClose.bind(this);
        this.handleOrderMenuClose = this.handleOrderMenuClose.bind(this);
        this.handleRegularOrderMenuClose = this.handleRegularOrderMenuClose.bind(this);

        this.sendEmailVerificationCode = this.sendEmailVerificationCode.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            anchorEl: null,
            open: false,

            isAvatarLoading: true,

            isEmailVerified: false,
            modalOpen: false,
            emailVerificationSlideTimeout: 300,

            orderMenuAnchorEl: null,
            orderMenuOpen: false,

            regularOrderMenuOpen: false,

            responseDialogOpen: false,
            responseErrors: [],
        };
    }

    componentDidMount() {
        this.initialize();
    }

    async initialize() {
        let isEmailVerified = await fetchData('get', '/isEmailVerified', {}, { 'X-CSRF-TOKEN': this.state.token });
        isEmailVerified = isEmailVerified.value;

        await updateState(this, {
            isAvatarLoading: false,
            isEmailVerified: isEmailVerified.verified,
        });
    }

    sendEmailVerificationCode() {
        fetchData('post', '/email/verification-notification', {}, { 'X-CSRF-TOKEN': this.state.token });
    }

    async handleRegularOrderSubmition(e) {
        let r = await fetchData('post', '/order', { businessName: 'regular' }, { 'X-CSRF-TOKEN': this.state.token });
        if (r.response.redirected === 200) {
            window.location.replace(r.response.url);
        }

        if (r.value.errors !== undefined) {
            let messages = [];
            for (const k in r.value.errors) {
                if (Object.hasOwnProperty.call(r.value.errors, k)) {
                    const error = r.value.errors[k];

                    error.forEach((v, i) => {
                        messages.push(<FormHelperText key={i} error>{v}</FormHelperText>);
                    });
                }
            }
            this.setState({ responseErrors: messages, responseDialogOpen: true });
        }
    }

    render() {
        return (
            <>
                {this.props.isAvatarLoading && <LoadingButton loading variant='contained' >{translate('general/avatar/single/ucFirstLetterFirstWord')}</LoadingButton>}
                {!this.props.isAvatarLoading &&
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
                                        <MenuItem onClick={(e) => { this.handleModalOpen(); this.sendEmailVerificationCode(); }}>
                                            <Link to='/#' style={{ textDecoration: 'none' }} >
                                                {translate('generalSentences/verify-email-address/ucFirstLetterFirstWord')}
                                            </Link>
                                        </MenuItem>
                                    }
                                >
                                    {translate('generalSentences/send-email-verification-message/ucFirstLetterFirstWord')}
                                </SlidingDialog>
                            }
                            {window.location.pathname !== '/dashboard/account' &&
                                <MenuItem>
                                    <Link to='/dashboard/account' style={{ textDecoration: 'none' }} >
                                        {translate('pages/account/account/account/single/ucFirstLetterFirstWord')}
                                    </Link>
                                </MenuItem>
                            }
                            {window.location.pathname !== '/dashboard/order' &&
                                <MenuItem>
                                    <Link to='/dashboard/order' style={{ textDecoration: 'none' }} >
                                        {translate('pages/orders/order/order/single/ucFirstLetterFirstWord')}
                                    </Link>
                                </MenuItem>
                            }
                            {window.location.pathname !== '/dashboard/visit' &&
                                <MenuItem>
                                    <Link to='/dashboard/visit' style={{ textDecoration: 'none' }} >
                                        {translate('pages/visits/visit/visit/single/ucFirstLetterFirstWord')}
                                    </Link>
                                </MenuItem>
                            }
                            {window.location.pathname !== '/settings' &&
                                <MenuItem >
                                    <Link to='/settings' style={{ textDecoration: 'none' }} >
                                        {translate('general/setting/plural/ucFirstLetterFirstWord')}
                                    </Link>
                                </MenuItem>
                            }
                        </Menu>

                        <SlidingDialog
                            open={this.state.responseDialogOpen}
                            slideTrigger={<div></div>}
                            onClose={this.handleResponseDialogClose}
                        >
                            {this.state.responseErrors}
                        </SlidingDialog>
                    </>
                }
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

    handleOrderMenuClose(e) {
        this.setState({ orderMenuAnchorEl: null, orderMenuOpen: false });
    }

    handleOrderMenuOpen(e) {
        this.setState({ orderMenuAnchorEl: e.target, orderMenuOpen: true });
    }

    handleRegularOrderMenuClose(e) {
        this.setState({ regularOrderMenuOpen: false });
    }

    handleRegularOrderMenuOpen(e) {
        this.setState({ regularOrderMenuOpen: true });
    }
}

export default UserIconNavigator
