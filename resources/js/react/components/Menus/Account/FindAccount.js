import React, { Component } from 'react'

import PropTypes from 'prop-types';

import CloseIcon from '@mui/icons-material/Close';
import { Alert, Button, Divider, FormControl, IconButton, Snackbar, Stack, TextField } from '@mui/material'
import LoadingButton from '@mui/lab/LoadingButton';

import { translate } from '../../../traslation/translate'
import { fetchData } from '../../Http/fetch';
import { updateState } from '../../helpers';

/**
 * FindAccount
 * @augments {Component<Props, State>}
 */
export class FindAccount extends Component {
    static propTypes = {
        handleAccount: PropTypes.func.isRequired,
    };

    constructor(props) {
        super(props);

        this.handleFeedbackClose = this.handleFeedbackClose.bind(this);

        this.handleFirstName = this.handleFirstName.bind(this);
        this.handleLastName = this.handleLastName.bind(this);
        this.handleUsername = this.handleUsername.bind(this);
        this.handlePhonenumber = this.handlePhonenumber.bind(this);
        this.handleEmail = this.handleEmail.bind(this);

        this.handleSubmit = this.handleSubmit.bind(this);

        this.state = {

            feedbackMessages: [],
            firstname: '',
            lastname: '',
            username: '',
            phonenumber: '',
            email: '',

            isSubmiting: false,
        };
    }

    handleFeedbackClose(event, reason, key) {
        if (reason === 'clickaway') {
            return;
        }

        let feedbackMessages = this.state.feedbackMessages;
        feedbackMessages[key].open = false;
        this.setState({ feedbackMessages: feedbackMessages });
    }

    async handleSubmit(e) {
        await updateState(this, { isSubmiting: true, errors: [] });

        let placeholder = '';
        for (const k in this.state) {
            if (Object.hasOwnProperty.call(this.state, k)) {
                const val = this.state[k];
                if (val !== '' && k !== 'errors') {
                    if (k === 'firstname' || k === 'lastname') {
                        placeholder = this.state.firstname + '-' + this.state.lastname;
                    } else {
                        placeholder = val;
                    }
                    break;
                }
            }
        }

        let r = await fetchData('get', '/account/' + placeholder, {}, { 'X-CSRF-TOKEN': this.state.token });

        if (r.response.status === 200) {
            let role = await fetchData('get', '/role-name?accountId=' + r.value.id, {}, { 'X-CSRF-TOKEN': this.state.token });

            if (r.response.status === 200) {
                if (role.response.status === 200) {
                    this.props.handleAccount(r.value, role.value);
                }
            } else {
                let value = null;
                if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
                value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
                this.setState({ feedbackMessages: value });
            }
        } else {
            let value = null;
            if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
            value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
            this.setState({ feedbackMessages: value });
        }

        await updateState(this, { isSubmiting: false });
    }

    render() {
        return (
            <>
                <FormControl sx={{ backgroundColor: theme => theme.palette.secondary, justifyContent: 'space-around', width: '100%', height: '100%' }}>
                    <Stack direction='row' divider={<Divider orientation='vertical' flexItem></Divider>} spacing={2} justifyContent='space-around'>
                        <TextField fullWidth type='text' value={this.state.firstname} onInput={this.handleFirstName} label={translate('general/firstname/single/ucFirstLetterAllWords')} variant='standard' sx={{ m: 1 }} />
                        <TextField fullWidth type='text' value={this.state.lastname} onInput={this.handleLastName} label={translate('general/lastname/single/ucFirstLetterAllWords')} variant='standard' sx={{ m: 1 }} />
                    </Stack>

                    <TextField fullWidth type='text' value={this.state.username} onInput={this.handleUsername} label={translate('general/username/single/ucFirstLetterAllWords')} variant='standard' sx={{ m: 1 }} />
                    <TextField fullWidth type='text' value={this.state.phonenumber} onInput={this.handlePhonenumber} label={translate('general/phonenumber/single/ucFirstLetterAllWords')} variant='standard' sx={{ m: 1 }} />
                    <TextField fullWidth type='text' value={this.state.email} onInput={this.handleEmail} label={translate('general/email/single/ucFirstLetterAllWords')} variant='standard' sx={{ m: 1 }} />

                    {this.state.isSubmiting ?
                        <LoadingButton varient='contained' type='button' loading>{translate('general/submit/single/ucFirstLetterFirstWord')}</LoadingButton> :
                        <Button type='submit' fullWidth onClick={this.handleSubmit} variant='contained' >{translate('general/submit/single/ucFirstLetterAllWords')}</Button>
                    }
                </FormControl>

                {this.state.feedbackMessages.map((m, i) =>
                    <Snackbar
                        key={i}
                        open={m.open}
                        autoHideDuration={6000}
                        onClose={(e, r) => this.handleFeedbackClose(e, r, i)}
                        action={
                            <IconButton
                                size="small"
                                onClick={(e, r) => this.handleFeedbackClose(e, r, i)}
                            >
                                <CloseIcon fontSize="small" />
                            </IconButton>
                        }
                    >
                        <Alert onClose={(e, r) => this.handleFeedbackClose(e, r, i)} severity={m.color} sx={{ width: '100%' }}>
                            {m.message}
                        </Alert>
                    </Snackbar>
                )}
            </>
        )
    }

    handleFirstName(e) {
        this.setState({
            firstname: e.target.value,
            username: '',
            phonenumber: '',
            email: '',
        });
    }

    handleLastName(e) {
        this.setState({
            lastname: e.target.value,
            username: '',
            phonenumber: '',
            email: '',
        });
    }

    handleUsername(e) {
        this.setState({
            firstname: '',
            lastname: '',
            username: e.target.value,
            phonenumber: '',
            email: '',
        });
    }

    handlePhonenumber(e) {
        this.setState({
            lastname: '',
            firstname: '',
            username: '',
            phonenumber: e.target.value,
            email: '',
        });
    }

    handleEmail(e) {
        this.setState({
            lastname: '',
            firstname: '',
            username: '',
            phonenumber: '',
            email: e.target.value,
        });
    }
}

export default FindAccount
