import React, { Component } from 'react';

import FormControl from '@mui/material/FormControl';
import TextField from '@mui/material/TextField';
import Stack from '@mui/material/Stack';
import Button from '@mui/material/Button';
import FormHelperText from '@mui/material/FormHelperText';
import LoadingButton from '@mui/lab/LoadingButton';

import { iterateRecursively } from '../../components/helpers.js';
import { translate } from '../../traslation/translate.js';
import { postJsonData, backendURL, putJsonData } from '../../components/Http/fetch.js';
import SlidingDialog from '../../components/Menus/SlidingDialog.js';
import { Divider } from '@mui/material';

export class LogInForm extends Component {
    constructor(props) {
        super(props);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            username: '',
            email: '',
            password: '',

            error: null,
            isLoading: false,

            fpOpen: null,
            fpEmail: '',
            fpPhonenumber: '',

            fpError: null,
            isFPLoading: false,

            rpOpen: false,
            rpCode: '',
            rpPassword: '',
            rpPasswordConfirmation: '',

            rpError: null,
            isRPLoading: false,

            rpSuccessfulOpen: false,
            rpSuccessfulError: null,
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

        this.handleRPSuccessfulClose = this.handleRPSuccessfulClose.bind(this);
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

    handleSubmit(e) {
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

        postJsonData(backendURL() + '/login', input, { 'X-CSRF-TOKEN': this.state.token })
            .then((res) => {
                if (res.redirected && res.status === 200) {
                    window.location.href = res.url;
                }
                return res.json();
            }).then((data) => {
                this.setState({ isLoading: false });

                let message = [];

                iterateRecursively(data,
                    () => { },
                    (array, v, k, i) => {
                        switch (k) {
                            case 'errors':
                                iterateRecursively(v,
                                    () => { },
                                    (array2, v2, k2, i2) => {
                                        iterateRecursively(v2,
                                            () => { },
                                            (array3, v3, k3, i3) => {
                                                message.push(<FormHelperText key={i3} error>{v3}</FormHelperText>);
                                            },
                                            () => {
                                            },
                                        );
                                    },
                                    () => {
                                        this.setState({ error: message });
                                    },
                                );
                                break;

                            case 'error':
                                this.setState({ error: <FormHelperText error>{v}</FormHelperText> });
                                break;

                            case 'message':
                                if ('errors' in array) {
                                    return;
                                }

                                this.setState({ error: <FormHelperText>{v}</FormHelperText> });
                                break;

                            default:
                                break;
                        }
                    },
                    () => {
                        this.setState({ isSubmittingRegisteration: false });
                    }
                );
            });
    }

    render() {
        return (
            <Stack component='form' onSubmit={this.handleSubmit}>
                <FormControl sx={{ backgroundColor: theme => theme.palette.secondary }}>
                    <TextField value={this.state.username} onInput={this.handleUsername} required label={translate('general/username/single/ucFirstLetterAllWords', this.props.currentLocaleName)} variant='standard' sx={{ m: 1 }} />
                    <TextField value={this.state.email} onInput={this.handleEmail} required label={translate('general/email-address/single/ucFirstLetterAllWords', this.props.currentLocaleName)} variant='standard' sx={{ m: 1 }} />
                    <TextField type='password' onInput={this.handlePassword} required label={translate('general/password/single/ucFirstLetterFirstWord', this.props.currentLocaleName)} variant='standard' sx={{ m: 1 }} />
                    {this.state.error !== null && this.state.error}
                    {this.state.isLoading && <LoadingButton loading variant="contained">{translate('general/log-in/single/ucFirstLetterAllWords', this.props.currentLocaleName)}</LoadingButton>}
                    {!this.state.isLoading && <Button type='submit' fullWidth onClick={this.handleSubmit} variant='contained' >{translate('general/log-in/single/ucFirstLetterAllWords', this.props.currentLocaleName)}</Button>}

                    <Divider sx={{ mt: 2, mb: 2 }} />

                    <SlidingDialog
                        target={true}
                        open={this.state.fpOpen}
                        slideTriggerInner={translate('pages/auth/login/forgot-password', this.props.currentLocaleName)}
                        slideTriggerProps={{ variant: 'outlined' }}
                    >
                        <FormControl sx={{ backgroundColor: theme => theme.palette.secondary }}>
                            {this.state.fpError !== null && this.state.fpError}
                            <TextField onInput={this.handleFPPhonenumber} label={translate('general/phonenumber/single/ucFirstLetterAllWords', this.props.currentLocaleName)} variant='standard' sx={{ m: 1 }} />
                            <TextField onInput={this.handleFPEmail} label={translate('general/email-address/single/ucFirstLetterAllWords', this.props.currentLocaleName)} variant='standard' sx={{ m: 1 }} />
                            {this.state.isFPLoading && <LoadingButton loading variant="contained">{translate('general/submit/single/ucFirstLetterAllWords', this.props.currentLocaleName)}</LoadingButton>}
                            {!this.state.isFPLoading && <Button type='submit' fullWidth onClick={this.handleSubmitFP} variant='contained' >{translate('general/submit/single/ucFirstLetterAllWords', this.props.currentLocaleName)}</Button>}
                        </FormControl>
                    </SlidingDialog>

                    <SlidingDialog
                        open={this.state.rpOpen}
                        slideTrigger={<div></div>}
                        onClose={this.handleRPClose}
                    >
                        <FormControl sx={{ backgroundColor: theme => theme.palette.secondary }}>
                            {this.state.rpError !== null && this.state.rpError}
                            <TextField type='number' onInput={this.handleRPCode} label={translate('general/security-code/single/ucFirstLetterAllWords', this.props.currentLocaleName)} variant='standard' sx={{ m: 1 }} />
                            <TextField type='password' onInput={this.handleRPPassword} label={translate('general/password/single/ucFirstLetterAllWords', this.props.currentLocaleName)} variant='standard' sx={{ m: 1 }} />
                            <TextField type='password' onInput={this.handleRPPasswordConfirmation} label={translate('general/confirm-password/single/ucFirstLetterAllWords', this.props.currentLocaleName)} variant='standard' sx={{ m: 1 }} />
                            {this.state.isRPLoading && <LoadingButton loading variant="contained">{translate('general/submit/single/ucFirstLetterAllWords', this.props.currentLocaleName)}</LoadingButton>}
                            {!this.state.isRPLoading && <Button type='submit' fullWidth onClick={this.handleSubmitRP} variant='contained' >{translate('general/submit/single/ucFirstLetterAllWords', this.props.currentLocaleName)}</Button>}
                        </FormControl>
                    </SlidingDialog>

                    <SlidingDialog
                        open={this.state.rpSuccessfulOpen}
                        slideTrigger={<div></div>}
                        onClose={this.handleRPSuccessfulClose}
                    >
                        {this.state.rpSuccessfulError !== null && this.state.rpSuccessfulError}
                        <Button onClick={this.handleRPSuccessfulClose} type='button'>{translate('general/ok/single/ucFirstLetterAllWords', this.props.currentLocaleName)}</Button>
                    </SlidingDialog>
                </FormControl>
            </Stack>
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

        let input = {};
        if (this.state.fpPhonenumber) {
            input.phonenumber = this.state.fpPhonenumber;
        }
        if (this.state.fpEmail) {
            input.email = this.state.fpEmail;
        }

        let error = false;
        let fpData = await postJsonData(backendURL() + '/forgot-password', input, { 'X-CSRF-TOKEN': this.state.token }).then((res) => {
            console.log(res);
            if (res.status !== 200) {
                error = true;
            }

            return res.json()
        });
        console.log(fpData);

        this.setState({ isFPLoading: false });

        if (error) {
            let messages = [];
            for (const k in fpData.errors) {
                fpData.errors[k].forEach((v, i) => {
                    console.log(i)
                    messages.push(<FormHelperText key={i} error>{v}</FormHelperText>);
                });
            }
            this.setState({ fpError: messages });
        } else {
            this.setState({ rpOpen: true, fpOpen: false });
            this.setState({ fpError: null, rpError: <FormHelperText>{fpData.message}</FormHelperText> });
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
        if (this.state.fpPhonenumber) {
            input.phonenumber = this.state.fpPhonenumber;
        }
        if (this.state.fpEmail) {
            input.email = this.state.fpEmail;
        }

        let error = false;
        let rpData = await putJsonData(backendURL() + '/reset-password', input, { 'X-CSRF-TOKEN': this.state.token }).then((res) => {
            console.log(res);
            if (res.status !== 200) {
                error = true;
            }

            return res.json()
        });
        console.log(rpData);

        this.setState({ isFPLoading: false });

        if (error) {
            let messages = [];
            for (const k in rpData.errors) {
                rpData.errors[k].forEach((v, i) => {
                    console.log(i)
                    messages.push(<FormHelperText key={i} error>{v}</FormHelperText>);
                });
            }
            this.setState({ rpError: messages });
        } else {
            this.setState({ rpSuccessfulError: <FormHelperText>{rpData.message}</FormHelperText> });
            setTimeout(() => {
                this.setState({ rpOpen: false, fpOpen: null, rpSuccessfulOpen: true, rpError: null });
            }, 200);
        }
    }

    handleRPSuccessfulClose() {
        this.setState({ rpSuccessfulOpen: false, rpSuccessfulError: null });
    }
}

export default LogInForm
