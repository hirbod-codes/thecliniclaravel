import React, { Component } from 'react';

import { postJsonData, backendURL } from '../../components/Http/fetch.js';

import FormControl from '@mui/material/FormControl';
import TextField from '@mui/material/TextField';
import Stack from '@mui/material/Stack';
import Button from '@mui/material/Button';
import FormHelperText from '@mui/material/FormHelperText';
import LoadingButton from '@mui/lab/LoadingButton';

export class LogInForm extends Component {
    constructor(props) {
        super(props);

        this.state = {
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

        let res = postJsonData(backendURL() + '/login', input, {}, true);

        if (typeof (res) === 'string') {
            window.location = res;
            return;
        }

        res.then((data) => {
            this.setState({ isLoading: false });

            if (!data.errors) {
                this.setState({ error: <FormHelperText error>{data.message}</FormHelperText> });
            } else {
                var errorMessage = [];
                let i = 0;
                for (const k in data.errors) {
                    if (Object.hasOwnProperty.call(data.errors, k)) {
                        const error = data.errors[k];
                        errorMessage.push(<FormHelperText key={i} error>{error}</FormHelperText>);
                    }
                    i++;
                }
                this.setState({ error: errorMessage });
            }
        });
    }

    render() {
        return (
            <>
                <Stack component='form' onSubmit={this.handleSubmit}>
                    <FormControl sx={{ backgroundColor: theme => theme.palette.secendary }}>
                        <TextField value={this.state.username} onInput={this.handleUsername} required label="User Name" variant='standard' sx={{ m: 1 }} />
                        <TextField value={this.state.email} onInput={this.handleEmail} required label="Email Address" variant='standard' sx={{ m: 1 }} />
                        <TextField type='password' onInput={this.handlePassword} required label="Password" variant='standard' sx={{ m: 1 }} />
                        {this.state.error !== null && this.state.error}
                        {this.state.isLoading && <LoadingButton loading variant="contained">Log in</LoadingButton>}
                        {!this.state.isLoading && <Button type='submit' fullWidth onClick={this.handleSubmit} variant='contained' >Log in</Button>}
                    </FormControl>
                </Stack>
            </>
        )
    }
}

export default LogInForm
