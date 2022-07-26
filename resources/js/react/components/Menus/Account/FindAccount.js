import React, { Component } from 'react'

import PropTypes from 'prop-types';

import { Button, Divider, FormControl, Stack, TextField } from '@mui/material'

import { translate } from '../../../traslation/translate'
import { fetchData } from '../../Http/fetch';
import { collectMessagesFromResponse, makeFormHelperTextComponents } from '../../Http/response';
import { updateState } from '../../helpers';
import LoadingButton from '@mui/lab/LoadingButton';

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

        this.handleFirstName = this.handleFirstName.bind(this);
        this.handleLastName = this.handleLastName.bind(this);
        this.handleUsername = this.handleUsername.bind(this);
        this.handlePhonenumber = this.handlePhonenumber.bind(this);
        this.handleEmail = this.handleEmail.bind(this);

        this.handleSubmit = this.handleSubmit.bind(this);

        this.state = {
            errors: null,
            firstname: '',
            lastname: '',
            username: '',
            phonenumber: '',
            email: '',

            isSubmiting: false,
        };
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

        await updateState(this, { isSubmiting: false });

        if (r.response.status !== 200) {
            let messages = [];
            if (r.value.errors !== undefined) {
                for (const k in r.value.errors) {
                    if (Object.hasOwnProperty.call(r.value.errors, k)) {
                        const error = r.value.errors[k];

                        error.forEach((v, i) => {
                            messages.push(<FormHelperText key={i} error> {v}</ FormHelperText>);
                        });
                    }
                }
            }

            this.setState({ errors: messages });
        }
    }

    render() {
        return (
            <>
                <FormControl sx={{ backgroundColor: theme => theme.palette.secondary, justifyContent: 'space-around', width: '100%', height: '100%' }}>
                    {this.state.errors}

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
            </>
        )
    }
}

export default FindAccount
