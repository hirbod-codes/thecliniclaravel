import React, { Component } from 'react'

import { Avatar, Button, Divider, IconButton, Menu, MenuItem, Stack, Tooltip } from '@mui/material'
import { translate } from '../traslation/translate';
import { backendURL, getJsonData, postJsonData } from './Http/fetch';
import { updateState } from './helpers';
import LoadingButton from '@mui/lab/LoadingButton';
import { Link } from 'react-router-dom';
import SlidingDialog from './Menus/SlidingDialog';
import { collectMessagesFromResponse, makeFormHelperTextComponents } from './Http/response';

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

            image: null,

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
        let accountData = await getJsonData(backendURL() + '/account', { 'X-CSRF-TOKEN': this.state.token }).then((res) => res.json());

        let avatarData = await getJsonData(backendURL() + '/avatar?accountId=' + accountData.id, { 'X-CSRF-TOKEN': this.state.token }).then((res) => res.text());

        let isEmailVerifiedData = await getJsonData(backendURL() + '/isEmailVerified', { 'X-CSRF-TOKEN': this.state.token }).then((res) => res.json());

        await updateState(this, {
            isAvatarLoading: false,
            isEmailVerified: isEmailVerifiedData.verified,
            image: 'data:image/png;base64,' + avatarData
        });
    }

    sendEmailVerificationCode() {
        postJsonData(backendURL() + '/email/verification-notification', {}, { 'X-CSRF-TOKEN': this.state.token }).then((res) => { return res.text() });
    }

    handleRegularOrderSubmition(e) {
        postJsonData('/order', { businessName: 'regular' }, { 'X-CSRF-TOKEN': this.state.token })
            .then((res) => {
                if (res.redirected) {
                    window.location.replace(res.url);
                }
                return res.json();
            })
            .then((data) => {
                if (Object.hasOwnProperty.call(data, 'errors')) {
                    let collectedData = collectMessagesFromResponse(data);
                    if (collectedData !== false) {
                        this.setState({ responseErrors: makeFormHelperTextComponents(collectedData), responseDialogOpen: true });
                    }
                }
            });
    }

    render() {
        return (
            <>
                {this.state.isAvatarLoading && <LoadingButton loading variant='contained' >{translate('general/avatar/single/ucFirstLetterFirstWord', this.props.currentLocaleName)}</LoadingButton>}
                {!this.state.isAvatarLoading &&
                    <>
                        <Tooltip title={translate('general/account/single/ucFirstLetterFirstWord', this.props.currentLocaleName)} >
                            <IconButton onClick={this.handleIconMenuOpen} >
                                <Avatar alt={translate('general/avatar/single/ucFirstLetterFirstWord', this.props.currentLocaleName)} src={this.state.image} />
                            </IconButton>
                        </Tooltip>
                        <Menu
                            anchorEl={this.state.anchorEl}
                            open={this.state.open}
                            onClose={this.handleIconMenuClose}
                        >
                            {!this.state.isEmailVerified &&
                                <SlidingDialog
                                    open={this.state.modalOpen}
                                    onClose={this.handleModalClose}
                                    timeout={this.state.emailVerificationSlideTimeout}
                                    slideTrigger={
                                        <MenuItem onClick={(e) => { this.handleModalOpen(); this.sendEmailVerificationCode(); }}>
                                            <Link to='/#' style={{ textDecoration: 'none' }} >
                                                {translate('generalSentences/verify-email-address/ucFirstLetterFirstWord', this.props.currentLocaleName)}
                                            </Link>
                                        </MenuItem>
                                    }
                                >
                                    {translate('generalSentences/send-email-verification-message/ucFirstLetterFirstWord', this.props.currentLocaleName)}
                                </SlidingDialog>
                            }
                            {window.location.pathname !== '/order/laser/page' &&
                                [
                                    <MenuItem key={0} onClick={this.handleOrderMenuOpen}>
                                        <Link to='/#' style={{ textDecoration: 'none' }} >
                                            {translate('general/order/plural/ucFirstLetterFirstWord', this.props.currentLocaleName)}
                                        </Link>
                                    </MenuItem>,
                                    <Menu
                                        key={1}
                                        anchorEl={this.state.orderMenuAnchorEl}
                                        open={this.state.orderMenuOpen}
                                        onClose={this.handleOrderMenuClose}
                                    >
                                        <MenuItem >
                                            <Link to='/order/laser/page' style={{ textDecoration: 'none' }} >
                                                {translate('general/laser-order/single/ucFirstLetterFirstWord', this.props.currentLocaleName)}
                                            </Link>
                                        </MenuItem>
                                        <SlidingDialog
                                            open={this.state.regularOrderMenuOpen}
                                            onClose={this.handleModalClose}
                                            timeout={this.state.emailVerificationSlideTimeout}
                                            slideTrigger={
                                                <MenuItem >
                                                    <Link to='#' onClick={this.handleRegularOrderMenuOpen} style={{ textDecoration: 'none' }} >
                                                        {translate('general/regular-order/single/ucFirstLetterFirstWord', this.props.currentLocaleName)}
                                                    </Link>
                                                </MenuItem>
                                            }
                                        >
                                            <Stack
                                                justifyContent='center'
                                                direction="column"
                                                divider={<Divider orientation="horizontal" />}
                                                spacing={2}
                                            >
                                                <div>{translate('/pages/orders/order/regular-order-submition-warning', this.props.currentLocaleName)}</div>
                                                <Button fullWidth variant='contained' onClick={this.handleRegularOrderSubmition}>{translate('/general/submit/single/ucFirstLetterFirstWord', this.props.currentLocaleName)}</Button>
                                            </Stack>
                                        </SlidingDialog>
                                    </Menu>
                                ]
                            }
                            {window.location.pathname !== '/settings' &&
                                <MenuItem >
                                    <Link to='/settings' style={{ textDecoration: 'none' }} >
                                        {translate('general/setting/plural/ucFirstLetterFirstWord', this.props.currentLocaleName)}
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
