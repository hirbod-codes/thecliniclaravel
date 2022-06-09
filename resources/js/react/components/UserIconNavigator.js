import React, { Component } from 'react'

import { Avatar, IconButton, Menu, MenuItem, Tooltip } from '@mui/material'
import { translate } from '../traslation/translate';
import { backendURL, getJsonData } from './Http/fetch';
import { updateState } from './helpers';
import LoadingButton from '@mui/lab/LoadingButton';

export class UserIconNavigator extends Component {
    constructor(props) {
        super(props);

        this.handleOpen = this.handleOpen.bind(this);
        this.handleClose = this.handleClose.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            anchorEl: null,
            open: false,

            accountId: null,
            isAvatarLoading: true,

            image: null,
            imageRaw: null,
        };
    }

    componentDidMount() {
        this.initialize();
    }

    async initialize() {
        let accountData = await getJsonData(backendURL() + '/account', { 'X-CSRF-TOKEN': this.state.token }).then((res) => res.json());

        let avatarData = await getJsonData(backendURL() + '/avatar?accountId=' + accountData.id, { 'X-CSRF-TOKEN': this.state.token }).then((res) => res.text());

        await updateState(this, {
            isAvatarLoading: false,
            image: 'data:image/png;base64,' + avatarData
        });
    }

    render() {
        return (
            <>
                {this.state.isAvatarLoading && <LoadingButton loading variant='contained' >Avatar</LoadingButton>}
                {!this.state.isAvatarLoading &&
                    <>
                        <Tooltip title={translate('general/account/ucFirstLetterFirstWord', this.props.currentLocaleName)} >
                            <IconButton onClick={this.handleOpen} >
                                <Avatar alt={translate('general/avatar/ucFirstLetterFirstWord', this.props.currentLocaleName)} src={this.state.image} />
                            </IconButton>
                        </Tooltip>
                        <Menu
                            anchorEl={this.state.anchorEl}
                            open={this.state.open}
                            onClose={this.handleClose}
                        >
                            <MenuItem onClick={(e) => { }}>Profile</MenuItem>
                        </Menu>
                    </>
                }
            </>
        )
    }

    handleClose(e) {
        this.setState({ anchorEl: null, open: false });
    }

    handleOpen(e) {
        this.setState({ anchorEl: e.target, open: true });
    }
}

export default UserIconNavigator
