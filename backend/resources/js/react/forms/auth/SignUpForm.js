import React, { Component } from 'react'

import { translate } from '../../traslation/translate.js';
import { fetchData } from '../../components/Http/fetch.js';
import { updateState } from '../../components/helpers.js';

import CloseIcon from '@mui/icons-material/Close';
import FormControl from '@mui/material/FormControl';
import TextField from '@mui/material/TextField';
import Stack from '@mui/material/Stack';
import Button from '@mui/material/Button';
import LoadingButton from '@mui/lab/LoadingButton';
import Autocomplete from '@mui/material/Autocomplete';
import Stepper from '@mui/material/Stepper';
import Step from '@mui/material/Step';
import StepLabel from '@mui/material/StepLabel';
import Box from '@mui/material/Box';
import Slide from '@mui/material/Slide';
import { Alert, IconButton, Snackbar } from '@mui/material';
import { Navigate } from 'react-router-dom';
import { get_cities, get_genders, get_states } from '../../components/Http/Api/general.js';

export class SignUpForm extends Component {
    constructor(props) {
        super(props);

        this.duration = 500;

        this.handleFeedbackClose = this.handleFeedbackClose.bind(this);

        this.previousStep = this.previousStep.bind(this);
        this.nextStep = this.nextStep.bind(this);

        this.handleFirstname = this.handleFirstname.bind(this);
        this.handleLastname = this.handleLastname.bind(this);
        this.handleUsername = this.handleUsername.bind(this);
        this.handleEmail = this.handleEmail.bind(this);
        this.handlePassword = this.handlePassword.bind(this);
        this.handleConfirmPassword = this.handleConfirmPassword.bind(this);
        this.handleGender = this.handleGender.bind(this);
        this.handlePhonenumber = this.handlePhonenumber.bind(this);
        this.handlePhonenumberCode = this.handlePhonenumberCode.bind(this);
        this.handleAvatar = this.handleAvatar.bind(this);
        this.resetAvatar = this.resetAvatar.bind(this);
        this.handleAge = this.handleAge.bind(this);
        this.handleState = this.handleState.bind(this);
        this.handleCity = this.handleCity.bind(this);
        this.handleAddress = this.handleAddress.bind(this);

        this.handleSubmitPhonenumber = this.handleSubmitPhonenumber.bind(this);
        this.handleSubmitPhonenumberCode = this.handleSubmitPhonenumberCode.bind(this);
        this.handleSubmitRegister = this.handleSubmitRegister.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            feedbackMessages: [],
            goToWelcomePage: false,

            steps: [
                {
                    name: 'phonenumber',
                    completed: true,
                    animationDirection: 'left',
                    in: true,
                    handler: this.handleSubmitPhonenumber
                },
                {
                    name: 'sendPhonenumberVerificationCode',
                    completed: false,
                    animationDirection: 'left',
                    in: false,
                    handler: this.handleSubmitPhonenumberCode
                },
                {
                    name: 'fillRegistrationForm',
                    completed: false,
                    animationDirection: 'left',
                    in: false,
                    handler: this.handleSubmitRegister
                }
            ],
            activeStep: 0,

            isPhonenumberUnique: true,

            firstname: '',
            lastname: '',
            username: '',
            email: '',
            password: '',
            confirmPassword: '',
            gender: '',
            phonenumber: '',
            phonenumberCode: '',
            avatar: '',
            age: 0,
            state: '',
            city: '',
            address: '',

            loadingGenders: true,
            loadingStates: true,
            loadingCities: true,

            passwordsMatch: true,

            isSubmittingPhonenumber: false,
            isSubmittingPhonenumberCode: false,
            isSubmittingRegisteration: false,
        };
    }

    componentDidMount() {
        this.getGenders();
        this.getStates();
    }

    handleFeedbackClose(event, reason, key) {
        if (reason === 'clickaway') {
            return;
        }

        let feedbackMessages = this.state.feedbackMessages;
        feedbackMessages[key].open = false;
        this.setState({ feedbackMessages: feedbackMessages });
    }

    async previousStep() {
        if (this.state.activeStep > 0) {
            let key = this.state.activeStep;
            let previousKey = this.state.activeStep - 1;

            await this.exit(key, 'left');

            await updateState(this, {
                activeStep: previousKey
            });

            await this.enter(previousKey, 'right');
        }
    }

    async nextStep() {
        if (this.state.activeStep < this.state.steps.length) {
            let key = this.state.activeStep;
            let nextKey = this.state.activeStep + 1;

            await this.exit(key, 'right');

            await updateState(this, {
                activeStep: nextKey
            });

            await this.enter(nextKey, 'left');
        }
    }

    exit(key, direction) {
        return new Promise(async (resolve) => {
            let newSteps = this.state.steps;
            newSteps[key].animationDirection = direction;
            newSteps[key].in = false;

            await updateState(this, {
                steps: newSteps,
            });

            resolve();
        });
    }

    enter(key, direction) {
        return new Promise((resolve) => {
            setTimeout(async () => {
                let newSteps = this.state.steps;
                newSteps[key].completed = true;
                newSteps[key].animationDirection = direction;
                newSteps[key].in = true;

                await updateState(this, {
                    steps: newSteps,
                });

                resolve();
            }, this.duration);
        });
    }

    render() {
        if (this.state.goToWelcomePage) {
            return (
                <Navigate to='/' />
            );
        }

        return (
            <>
                <Stack >
                    <Stepper >
                        <Step key={0} completed={this.state.steps[1].completed} active={this.state.activeStep === 0}>
                            <StepLabel>
                                {translate('general/phonenumber/single/ucFirstLetterAllWords')}
                            </StepLabel>
                        </Step>
                        <Step key={1} completed={this.state.steps[1].completed} active={this.state.activeStep === 1}>
                            <StepLabel>
                                {translate('pages/auth/signup/send-phone-number-verification-code')}
                            </StepLabel>
                        </Step>
                        <Step key={2} completed={this.state.steps[2].completed} active={this.state.activeStep === 2}>
                            <StepLabel>
                                {translate('pages/auth/signup/fill-registration-form')}
                            </StepLabel>
                        </Step>
                    </Stepper>
                    <Box sx={{ mt: 1, mb: 1, display: 'flex' }}>
                        <Button variant='contained' disabled={this.state.activeStep === 0} type='button' onClick={this.previousStep} >{translate('general/back/single/ucFirstLetterFirstWord')}</Button>
                    </Box>
                    <Slide direction={this.state.steps[0].animationDirection} timeout={this.duration} in={this.state.steps[0].in} mountOnEnter unmountOnExit>
                        <Box component='form' onSubmit={this.handleSubmitPhonenumber} >
                            <FormControl sx={{ width: '100%' }} >
                                <TextField onInput={this.handlePhonenumber} error={!this.state.isPhonenumberUnique} value={this.state.phonenumber} required label={translate('general/phonenumber/single/ucFirstLetterAllWords')} variant='standard' sx={{ m: 1 }} />

                                {this.state.isSubmittingPhonenumber && <LoadingButton loading variant="contained">{translate('general/submit/single/allLowerCase')}</LoadingButton>}
                                {!this.state.isSubmittingPhonenumber && <Button type='submit' fullWidth onClick={this.handleSubmitPhonenumber} variant='contained' >{translate('general/submit/single/ucFirstLetterFirstWord')}</Button>}
                            </FormControl>
                        </Box>
                    </Slide>
                    <Slide direction={this.state.steps[1].animationDirection} timeout={this.duration} in={this.state.steps[1].in} mountOnEnter unmountOnExit>
                        <Box component='form' onSubmit={this.handleSubmitPhonenumberCode} >
                            <FormControl sx={{ width: '100%' }} >
                                <TextField onInput={this.handlePhonenumberCode} required label={translate('pages/auth/signup/security-code')} variant='standard' sx={{ m: 1 }} />

                                {this.state.isSubmittingPhonenumberCode && <LoadingButton loading variant="contained">{translate('general/submit/single/ucFirstLetterFirstWord')}</LoadingButton>}
                                {!this.state.isSubmittingPhonenumberCode && <Button type='submit' fullWidth onClick={this.handleSubmitPhonenumberCode} variant='contained' >{translate('general/submit/single/ucFirstLetterFirstWord')}</Button>}
                            </FormControl>
                        </Box>
                    </Slide>
                    <Slide direction={this.state.steps[2].animationDirection} timeout={this.duration} in={this.state.steps[2].in} mountOnEnter unmountOnExit>
                        <Box component='form' onSubmit={this.handleSubmitRegister} >
                            <FormControl sx={{ width: '100%' }} >
                                <TextField onInput={this.handleFirstname} required label={translate('general/firstname/single/ucFirstLetterAllWords')} variant='standard' sx={{ m: 1 }} />
                                <TextField onInput={this.handleLastname} required label={translate('general/lastname/single/ucFirstLetterAllWords')} variant='standard' sx={{ m: 1 }} />
                                <TextField onInput={this.handleUsername} required label={translate('general/username/single/ucFirstLetterAllWords')} variant='standard' sx={{ m: 1 }} />
                                <TextField type='email' onInput={this.handleEmail} label={translate('general/email-address/single/ucFirstLetterFirstWord')} variant='standard' sx={{ m: 1 }} />
                                <TextField type='password' error={!this.state.passwordsMatch} onInput={this.handlePassword} required label={translate('general/password/single/ucFirstLetterFirstWord')} variant='standard' sx={{ m: 1 }} />
                                <TextField type='password' error={!this.state.passwordsMatch} onInput={this.handleConfirmPassword} required label={translate('general/confirm-password/single/ucFirstLetterAllWords')} variant='standard' sx={{ m: 1 }} />
                                <TextField value={this.state.phonenumber} disabled required label={translate('general/phonenumber/single/ucFirstLetterAllWords')} variant='standard' sx={{ m: 1 }} />

                                {this.state.loadingGenders && <LoadingButton loading variant='contained'>{translate('general/gender/single/ucFirstLetterFirstWord')}</LoadingButton>}
                                {!this.state.loadingGenders && <Autocomplete
                                    sx={{ m: 1 }}
                                    disablePortal
                                    options={this.genders}
                                    onChange={this.handleGender}
                                    onClick={this.handleGender}
                                    renderInput={(params) => <TextField {...params} label={translate('general/gender/single/ucFirstLetterFirstWord')} required variant='standard' />}
                                />}

                                <TextField type='number' onInput={this.handleAge} required label={translate('general/age/single/ucFirstLetterFirstWord')} variant='standard' sx={{ m: 1 }} min={1} />

                                {this.state.loadingStates && <LoadingButton loading variant='contained' sx={{ m: 1 }} >{translate('general/state/single/ucFirstLetterFirstWord')}</LoadingButton>}
                                {!this.state.loadingStates && <Autocomplete
                                    sx={{ m: 1 }}
                                    disablePortal
                                    options={this.states}
                                    onChange={this.handleState}
                                    renderInput={(params) => <TextField {...params} label={translate('general/state/single/ucFirstLetterFirstWord')} required variant='standard' />}
                                />}

                                {this.state.loadingCities && <LoadingButton loading variant='contained' sx={{ m: 1 }} >{translate('general/city/single/ucFirstLetterFirstWord')}</LoadingButton>}
                                {!this.state.loadingCities && <Autocomplete
                                    sx={{ m: 1 }}
                                    disablePortal
                                    options={this.cities}
                                    onChange={this.handleCity}
                                    renderInput={(params) => <TextField {...params} label={translate('general/city/single/ucFirstLetterFirstWord')} required variant='standard' />}
                                />}

                                <TextField onInput={this.handleAddress} multiline label={translate('general/address/single/ucFirstLetterFirstWord')} variant='standard' sx={{ m: 1 }} />

                                {this.state.isSubmittingRegisteration && <LoadingButton loading variant="contained">{translate('general/sign-up/single/ucFirstLetterAllWords')}</LoadingButton>}
                                {!this.state.isSubmittingRegisteration && <Button type='submit' fullWidth onClick={this.handleSubmitRegister} variant='contained' >{translate('general/sign-up/single/ucFirstLetterAllWords')}</Button>}
                            </FormControl>
                        </Box>
                    </Slide>
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

    async getGenders() {
        let r = await get_genders(this.state.token);

        if (r.response.status === 200) {
            this.genders = [];
            for (let i = 0; i < r.value.length; i++) {
                const gender = r.value[i];

                this.genders.push({ id: i, label: gender });
            }
        } else {
            let value = null;
            if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
            value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
            this.setState({ feedbackMessages: value });
        }
        this.setState({ loadingGenders: false });
    }

    async getStates() {

        let r = await get_states(this.state.token);

        if (r.response.status === 200) {
            this.states = [];
            for (let i = 0; i < r.value.length; i++) {
                const state = r.value[i];

                this.states.push({ id: i, label: state });
            }
        } else {
            let value = null;
            if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
            value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
            this.setState({ feedbackMessages: value });
        }
        this.setState({ loadingStates: false });
    }

    async getCities(state) {
        this.setState({ loadingCities: true });

        let r = await get_cities(state, this.state.token);

        if (r.response.status === 200) {
            this.cities = [];
            for (let i = 0; i < r.value.length; i++) {
                const city = r.value[i];

                this.cities.push({ id: i, label: city });
            }
        } else {
            let value = null;
            if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
            value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
            this.setState({ feedbackMessages: value });
        }

        this.setState({ loadingCities: false });
    }

    async handleSubmitPhonenumber(e) {
        e.preventDefault();
        this.setState({ isSubmittingPhonenumber: true });

        let r = null;
        r = await fetchData('get', '/auth/phonenumber-availability?phonenumber=' + this.state.phonenumber, {}, { 'X-CSRF-TOKEN': this.state.token, 'Accept': 'application/json' });
        if (r.response.status !== 200) {
            let value = null;
            if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
            value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
            this.setState({ feedbackMessages: value });

            this.setState({ isSubmittingPhonenumber: false });
            return;
        }

        r = null;
        r = await fetchData('post', '/auth/send-code-to-phonenumber', { phonenumber: this.state.phonenumber }, { 'X-CSRF-TOKEN': this.state.token, 'Accept': 'application/json' });
        if (r.response.status === 200) {
            this.nextStep();
        } else {
            let value = null;
            if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
            value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
            this.setState({ feedbackMessages: value });
        }

        this.setState({ isSubmittingPhonenumber: false });
    }

    async handleSubmitPhonenumberCode(e) {
        e.preventDefault();
        this.setState({ isSubmittingPhonenumberCode: true });

        let input = {};

        input.phonenumber = this.state.phonenumber;
        input.code = this.state.phonenumberCode;

        let r = await fetchData('post', '/auth/verify-phonenumber', input, { 'X-CSRF-TOKEN': this.state.token, 'Accept': 'application/json' });

        if (r.response.status === 200) {
            this.nextStep();
        } else {
            let value = null;
            if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
            value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
            this.setState({ feedbackMessages: value });
        }

        this.setState({ isSubmittingPhonenumberCode: false });
    }

    async handleSubmitRegister(e) {
        e.preventDefault();
        this.setState({ isSubmittingRegisteration: true });

        let input = { userAttributes: {}, userAccountAttributes: {} };

        input.userAttributes.firstname = this.state.firstname;
        input.userAttributes.lastname = this.state.lastname;
        input.userAttributes.username = this.state.username;
        input.userAttributes.email = this.state.email;
        input.userAttributes.password = this.state.password;
        input.userAttributes.password_confirmation = this.state.confirmPassword;
        input.userAttributes.gender = this.state.gender;
        input.userAttributes.phonenumber = this.state.phonenumber;

        input.userAccountAttributes.age = this.state.age;
        input.userAccountAttributes.state = this.state.state;
        input.userAccountAttributes.city = this.state.city;
        input.userAccountAttributes.address = this.state.address;

        let r = await fetchData('post', '/register', input, { 'X-CSRF-TOKEN': this.state.token, 'Accept': 'application/json' });

        if (r.response.status === 200) {
            if (r.response.redirected) {
                this.setState({ goToWelcomePage: true });
                if (this.props.onRegister !== undefined) {
                    this.props.onRegister();
                } else {
                    window.location.href = r.response.url;
                }
            }
        } else {
            let value = null;
            if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
            value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
            this.setState({ feedbackMessages: value });
        }

        this.setState({ isSubmittingRegisteration: false });
    }

    handleFirstname(e) {
        this.setState({ firstname: e.target.value });
    }

    handleLastname(e) {
        this.setState({ lastname: e.target.value });
    }

    handleUsername(e) {
        this.setState({ username: e.target.value });
    }

    handleEmail(e) {
        this.setState({ email: e.target.value });
    }

    handlePassword(e) {
        this.setState({ passwordsMatch: e.target.value === this.state.confirmPassword });

        this.setState({ password: e.target.value });
    }

    handleConfirmPassword(e) {
        this.setState({ passwordsMatch: e.target.value === this.state.password });

        this.setState({ confirmPassword: e.target.value });
    }

    handleGender(e) {
        const elm = e.target;

        let v = '';
        if (elm.tagName === 'INPUT') {
            v = elm.getAttribute('value');
        } else {
            v = elm.innerText;
        }

        this.setState({ gender: v });
    }

    handlePhonenumber(e) {
        this.setState({ phonenumber: e.target.value });
    }

    handlePhonenumberCode(e) {
        this.setState({ phonenumberCode: e.target.value });
    }

    handleAvatar(e) {
        this.setState({ avatar: e.target.files[0] ? e.target.files[0] : '' });
    }

    resetAvatar(e) {
        this.setState({ avatar: '' });
    }

    handleAge(e) {
        this.setState({ age: e.target.value });
    }

    handleState(e) {
        const elm = e.target;

        let v = '';
        if (elm.tagName === 'INPUT') {
            v = elm.getAttribute('value');
        } else {
            v = elm.innerText;
        }

        this.setState({ state: v });
        this.getCities(v);
    }

    handleCity(e) {
        const elm = e.target;

        let v = '';
        if (elm.tagName === 'INPUT') {
            v = elm.getAttribute('value');
        } else {
            v = elm.innerText;
        }

        this.setState({ city: v });
    }

    handleAddress(e) {
        this.setState({ address: e.target.value });
    }
}

export default SignUpForm
