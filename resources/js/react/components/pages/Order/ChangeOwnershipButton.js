import { Button, Divider, FormControl, Stack, TextField } from '@mui/material'
import React, { Component } from 'react'
import { translate } from '../../../traslation/translate'
import { getJsonData } from '../../Http/fetch';
import { collectMessagesFromResponse, makeFormHelperTextComponents } from '../../Http/response';
import { LocaleContext } from '../../localeContext';
import SlidingDialog from '../../Menus/SlidingDialog'

export class ChangeOwnershipButton extends Component {
    constructor(props) {
        super(props);

        this.handleUsername = this.handleUsername.bind(this);
        this.handleFirstName = this.handleFirstName.bind(this);
        this.handleLastName = this.handleLastName.bind(this);
        this.handlePhonenumber = this.handlePhonenumber.bind(this);
        this.handleEmail = this.handleEmail.bind(this);
        this.handleSubmit = this.handleSubmit.bind(this);

        this.state = {
            firstname: '',
            lastname: '',
            username: '',
            phonenumber: '',
            email: '',

            errors: [],
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

    handleSubmit(e) {
        this.setState({ errors: [] });

        let placeholder = '';
        for (const k in this.state) {
            if (Object.hasOwnProperty.call(this.state, k)) {
                const val = this.state[k];
                if (val !== '') {
                    if (k === 'firstname' || k === 'lastname') {
                        placeholder = this.state.firstname + '-' + this.state.lastname;
                    } else {
                        placeholder = val;
                    }
                    break;
                }
            }
        }

        getJsonData('/account/' + placeholder, { 'X-CSRF-TOKEN': this.state.token })
            .then((res) => {
                console.log(res.headers.get('Content-Type'));
                if (res.headers.get('Content-Type') === 'application/json') {
                    return res.json();
                } else {
                    return res.text();
                }
            })
            .then((data) => {
                console.log(data);
                let collectedData = collectMessagesFromResponse(data);
                if (collectedData !== false) {
                    this.setState({ errors: makeFormHelperTextComponents(collectedData) });
                    console.log(collectedData);
                } else {
                    this.props.handleAccountId(data.id);
                    this.setState({ errors: makeFormHelperTextComponents(collectedData) });
                }
            });
    }

    render() {
        return (
            <>
                <LocaleContext.Consumer>
                    {({ locales, currentLocale, isLocaleLoading, changeLocale }) => {
                        return <SlidingDialog
                            open={this.props.open}
                            slideTrigger={<div></div>}
                            onClose={this.props.onClose}
                        >
                            <FormControl sx={{ backgroundColor: theme => theme.palette.secondary }}>
                                {this.state.errors}

                                <Stack direction='row' divider={<Divider orientation='vertical' flexItem></Divider>} spacing={2}>
                                    <TextField type='text' value={this.state.firstname} onInput={this.handleFirstName} label={translate('general/firstname/single/ucFirstLetterAllWords', currentLocale.shortName)} variant='standard' sx={{ m: 1 }} />
                                    <TextField type='text' value={this.state.lastname} onInput={this.handleLastName} label={translate('general/lastname/single/ucFirstLetterAllWords', currentLocale.shortName)} variant='standard' sx={{ m: 1 }} />
                                </Stack>

                                <TextField type='text' value={this.state.username} onInput={this.handleUsername} label={translate('general/username/single/ucFirstLetterAllWords', currentLocale.shortName)} variant='standard' sx={{ m: 1 }} />
                                <TextField type='text' value={this.state.phonenumber} onInput={this.handlePhonenumber} label={translate('general/phonenumber/single/ucFirstLetterAllWords', currentLocale.shortName)} variant='standard' sx={{ m: 1 }} />
                                <TextField type='text' value={this.state.email} onInput={this.handleEmail} label={translate('general/email/single/ucFirstLetterAllWords', currentLocale.shortName)} variant='standard' sx={{ m: 1 }} />

                                <Button type='submit' fullWidth onClick={this.handleSubmit} variant='contained' >{translate('general/submit/single/ucFirstLetterAllWords', currentLocale.shortName)}</Button>
                            </FormControl>
                        </SlidingDialog>
                    }}
                </LocaleContext.Consumer>
            </>
        )
    }
}

export default ChangeOwnershipButton
