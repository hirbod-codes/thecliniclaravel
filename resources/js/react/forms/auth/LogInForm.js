import React, { Component } from 'react';

import { postJsonData, backendURL } from '../../components/Http/fetch.js';

import FormControl from '@mui/material/FormControl';
import TextField from '@mui/material/TextField';
import Stack from '@mui/material/Stack';
import Button from '@mui/material/Button';
import FormHelperText from '@mui/material/FormHelperText';
import LoadingButton from '@mui/lab/LoadingButton';
import { iterateRecursively } from '../../components/helpers.js';
import { translate } from '../../traslation/translate.js';

export class LogInForm extends Component {
    constructor(props) {
        super(props);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            username: '',
            email: '',
            password: '',
            error: null,
            isLoading: false
        };

        this.handleEmail = this.handleEmail.bind(this);
        this.handleUsername = this.handleUsername.bind(this);
        this.handlePassword = this.handlePassword.bind(this);

        this.handleSubmit = this.handleSubmit.bind(this);
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
                <FormControl sx={{ backgroundColor: theme => theme.palette.secendary }}>
                    <TextField value={this.state.username} onInput={this.handleUsername} required label={translate('general/username/ucFirstLetterAllWords', this.props.currentLocaleName)} variant='standard' sx={{ m: 1 }} />
                    <TextField value={this.state.email} onInput={this.handleEmail} required label={translate('general/email-address/ucFirstLetterAllWords', this.props.currentLocaleName)} variant='standard' sx={{ m: 1 }} />
                    <TextField type='password' onInput={this.handlePassword} required label={translate('general/password/ucFirstLetterFirstWord', this.props.currentLocaleName)} variant='standard' sx={{ m: 1 }} />
                    {this.state.error !== null && this.state.error}
                    {this.state.isLoading && <LoadingButton loading variant="contained">{translate('general/log-in/ucFirstLetterAllWords', this.props.currentLocaleName)}</LoadingButton>}
                    {!this.state.isLoading && <Button type='submit' fullWidth onClick={this.handleSubmit} variant='contained' >{translate('general/log-in/ucFirstLetterAllWords', this.props.currentLocaleName)}</Button>}
                </FormControl>
            </Stack>
        )
    }
}

export default LogInForm
