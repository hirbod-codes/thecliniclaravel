import React, { Component } from 'react'

import { Avatar, IconButton, Menu, MenuItem, Tooltip } from '@mui/material'
import { translate } from '../traslation/translate';
import { backendURL, getJsonData, postJsonData } from './Http/fetch';
import { updateState } from './helpers';
import LoadingButton from '@mui/lab/LoadingButton';
import { Link } from 'react-router-dom';
import SlidingDialog from './Menus/SlidingDialog';

export class UserIconNavigator extends Component {
    constructor(props) {
        super(props);

        this.handleIconMenuOpen = this.handleIconMenuOpen.bind(this);
        this.handleIconMenuClose = this.handleIconMenuClose.bind(this);
        this.handleModalClose = this.handleModalClose.bind(this);
        this.handleModalOpen = this.handleModalOpen.bind(this);
        this.sendEmailVerificationCode = this.sendEmailVerificationCode.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            anchorEl: null,
            open: false,

            isAvatarLoading: true,

            image: null,

            isEmailVerified: false,
            modalOpen: false,
            emailVerificationSlideTimeout: 300
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
                                            {translate('generalSentences/verify-email-address/ucFirstLetterFirstWord', this.props.currentLocaleName)}
                                        </MenuItem>
                                    }
                                >
                                    {translate('generalSentences/send-email-verification-message/ucFirstLetterFirstWord', this.props.currentLocaleName)}
                                </SlidingDialog>

                            }
                            <MenuItem ><Link to='/orders' style={{ textDecoration: 'none' }} >
                                {translate('general/order/plural/ucFirstLetterFirstWord', this.props.currentLocaleName)}
                            </Link></MenuItem>
                            <MenuItem ><Link to='/visits' style={{ textDecoration: 'none' }} >
                                {translate('general/visit/plural/ucFirstLetterFirstWord', this.props.currentLocaleName)}
                            </Link></MenuItem>
                            <MenuItem ><Link to='/settings' style={{ textDecoration: 'none' }} >
                                {translate('general/setting/plural/ucFirstLetterFirstWord', this.props.currentLocaleName)}
                            </Link></MenuItem>
                        </Menu>
                    </>
                }
            </>
        )
    }

    async handleModalClose(e) {
        this.setState({ modalOpen: false });
    }

    async handleModalOpen(e) {
        this.setState({ modalOpen: true });
    }

    sendEmailVerificationCode() {
        postJsonData(backendURL() + '/email/verification-notification', {}, { 'X-CSRF-TOKEN': this.state.token }).then((res) => { console.log(res); return res.text() }).then((data) => console.log(data));
    }

    handleIconMenuClose(e) {
        this.setState({ anchorEl: null, open: false });
    }

    handleIconMenuOpen(e) {
        this.setState({ anchorEl: e.target, open: true });
    }
}

export default UserIconNavigator
