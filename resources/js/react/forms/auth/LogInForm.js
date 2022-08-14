import React, { Component } from 'react';

import CloseIcon from '@mui/icons-material/Close';
import FormControl from '@mui/material/FormControl';
import TextField from '@mui/material/TextField';
import Stack from '@mui/material/Stack';
import Button from '@mui/material/Button';
import LoadingButton from '@mui/lab/LoadingButton';
import { Alert, Divider, IconButton, Snackbar } from '@mui/material';

import { translate } from '../../traslation/translate.js';
import { fetchData, backendURL } from '../../components/Http/fetch.js';
import SlidingDialog from '../../components/Menus/SlidingDialog.js';

export class LogInForm extends Component {
    constructor(props) {
        super(props);

        this.handleFeedbackClose = this.handleFeedbackClose.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            feedbackMessages: [],

            username: '',
            email: '',
            password: '',

            isLoading: false,

            fpOpen: null,
            fpEmail: '',
            fpPhonenumber: '',

            isFPLoading: false,

            rpOpen: false,
            rpCode: '',
            rpPassword: '',
            rpPasswordConfirmation: '',

            isRPLoading: false,
        };

        this.handleEmail = this.handleEmail.bind(this);
        this.handleUsername = this.handleUsername.bind(this);
        this.handlePassword = this.handlePassword.bind(this);

        this.handleSubmit = this.handleSubmit.bind(this);

        this.handleFPPhonenumber = this.handleFPPhonenumber.bind(this);
        this.handleFPEmail = this.handleFPEmail.bind(this);
        this.handleSubmitFP = this.handleSubmitFP.bind(this);

        this.handleRPClose = this.handleRPClose.bind(this);
        this.handleRPCode = this.handleRPCode.bind(this);
        this.handleRPPassword = this.handleRPPassword.bind(this);
        this.handleRPPasswordConfirmation = this.handleRPPasswordConfirmation.bind(this);
        this.handleSubmitRP = this.handleSubmitRP.bind(this);
    }

    handleFeedbackClose(event, reason, key) {
        if (reason === 'clickaway') {
            return;
        }

        let feedbackMessages = this.state.feedbackMessages;
        feedbackMessages[key].open = false;
        this.setState({ feedbackMessages: feedbackMessages });
    }

    handleUsername(e) {
        this.setState({ username: e.target.value, email: '' });
    }

    handleEmail(e) {
        this.setState({ username: '', email: e.target.value });
    }

    handlePassword(e) {
        this.setState({ password: e.target.value });
    }

    async handleSubmit(e) {
        e.preventDefault();
        this.setState({ isLoading: true });

        let input = {};

        if (this.state.username) {
            input.username = this.state.username;
        } else {
            if (this.state.email) {
                input.email = this.state.email;
            }
        }
        input.password = this.state.password;

        let r = await fetchData('post', backendURL() + '/login', input, { 'X-CSRF-TOKEN': this.state.token });

        if (r.response.status === 200) {
            if (r.response.redirected) {
                window.location.href = r.response.url;
            }
        } else {
            let value = null;
            if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
            value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
            this.setState({ feedbackMessages: value });
        }
        this.setState({ isLoading: false });
    }

    render() {
        return (
            <>
                <Stack component='form' onSubmit={this.handleSubmit}>
                    <FormControl sx={{ backgroundColor: theme => theme.palette.secondary }}>
                        <TextField value={this.state.username} onInput={this.handleUsername} required label={translate('general/username/single/ucFirstLetterAllWords')} variant='standard' sx={{ m: 1 }} />
                        <TextField value={this.state.email} onInput={this.handleEmail} required label={translate('general/email-address/single/ucFirstLetterAllWords')} variant='standard' sx={{ m: 1 }} />
                        <TextField type='password' onInput={this.handlePassword} required label={translate('general/password/single/ucFirstLetterFirstWord')} variant='standard' sx={{ m: 1 }} />
                        {this.state.error !== null && this.state.error}
                        {this.state.isLoading && <LoadingButton loading variant="contained">{translate('general/log-in/single/ucFirstLetterAllWords')}</LoadingButton>}
                        {!this.state.isLoading && <Button type='submit' fullWidth onClick={this.handleSubmit} variant='contained' >{translate('general/log-in/single/ucFirstLetterAllWords')}</Button>}

                        <Divider sx={{ mt: 2, mb: 2 }} />

                        <SlidingDialog
                            target={true}
                            open={this.state.fpOpen}
                            slideTriggerInner={translate('pages/auth/login/forgot-password')}
                            slideTriggerProps={{ variant: 'outlined' }}
                        >
                            <FormControl sx={{ backgroundColor: theme => theme.palette.secondary }}>
                                <TextField fullWidth onInput={this.handleFPPhonenumber} label={translate('general/phonenumber/single/ucFirstLetterAllWords')} variant='standard' sx={{ m: 1 }} />
                                <TextField fullWidth onInput={this.handleFPEmail} label={translate('general/email-address/single/ucFirstLetterAllWords')} variant='standard' sx={{ m: 1 }} />
                                {this.state.isFPLoading && <LoadingButton loading variant="contained">{translate('general/submit/single/ucFirstLetterAllWords')}</LoadingButton>}
                                {!this.state.isFPLoading && <Button type='submit' fullWidth onClick={this.handleSubmitFP} variant='contained' >{translate('general/submit/single/ucFirstLetterAllWords')}</Button>}
                            </FormControl>
                        </SlidingDialog>

                        <SlidingDialog
                            open={this.state.rpOpen}
                            slideTrigger={<div></div>}
                            onClose={this.handleRPClose}
                        >
                            <FormControl sx={{ backgroundColor: theme => theme.palette.secondary }}>
                                <TextField type='number' onInput={this.handleRPCode} label={translate('general/security-code/single/ucFirstLetterAllWords')} variant='standard' sx={{ m: 1 }} />
                                <TextField type='password' onInput={this.handleRPPassword} label={translate('general/password/single/ucFirstLetterAllWords')} variant='standard' sx={{ m: 1 }} />
                                <TextField type='password' onInput={this.handleRPPasswordConfirmation} label={translate('general/confirm-password/single/ucFirstLetterAllWords')} variant='standard' sx={{ m: 1 }} />
                                {this.state.isRPLoading && <LoadingButton loading variant="contained">{translate('general/submit/single/ucFirstLetterAllWords')}</LoadingButton>}
                                {!this.state.isRPLoading && <Button type='submit' fullWidth onClick={this.handleSubmitRP} variant='contained' >{translate('general/submit/single/ucFirstLetterAllWords')}</Button>}
                            </FormControl>
                        </SlidingDialog>
                    </FormControl>
                </Stack>

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

    handleFPPhonenumber(e) {
        this.setState({ fpEmail: '', fpPhonenumber: e.target.value });
    }

    handleFPEmail(e) {
        this.setState({ fpPhonenumber: '', fpEmail: e.target.value });
    }

    async handleSubmitFP(e) {
        e.preventDefault();
        this.setState({ isFPLoading: true, fpError: '', rpError: '' });

        if (!this.state.fpPhonenumber & !this.state.fpEmail) {
            return;
        }

        let r = await fetchData('post', '/auth/send-code-to-' + (this.state.fpPhonenumber ? 'phonenumber' : 'email'), this.state.fpPhonenumber ? { phonenumber: this.state.fpPhonenumber } : { email: this.state.fpEmail }, { 'X-CSRF-TOKEN': this.state.token });
        this.setState({ isFPLoading: false });

        let value = null;
        if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
        value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
        this.setState({ feedbackMessages: value });

        if (r.response.status === 200) {
            this.setState({ rpOpen: true, fpOpen: false });
        }

    }

    handleRPClose() {
        this.setState({ rpOpen: false });
    }

    handleRPCode(e) {
        this.setState({ rpCode: e.target.value });
    }

    handleRPPassword(e) {
        this.setState({ rpPassword: e.target.value });
    }

    handleRPPasswordConfirmation(e) {
        this.setState({ rpPasswordConfirmation: e.target.value });
    }

    async handleSubmitRP(e) {
        e.preventDefault();
        this.setState({ isFPLoading: true });

        let input = {};
        input.code = this.state.rpCode;
        input.password = this.state.rpPassword;
        input.password_confirmation = this.state.rpPasswordConfirmation;

        let r = await fetchData('put', '/auth/reset-password', input, { 'X-CSRF-TOKEN': this.state.token });
        this.setState({ isFPLoading: false });

        let value = null;
        if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
        value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
        this.setState({ feedbackMessages: value });

        if (r.response.status === 200) {
            setTimeout(() => {
                this.setState({ rpOpen: false, fpOpen: null });
            }, 200);
        }
    }
}

export default LogInForm
