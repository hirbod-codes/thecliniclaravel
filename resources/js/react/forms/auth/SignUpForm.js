import React, { Component } from 'react'

import { translate } from '../../traslation/translate.js';
import { postJsonData, backendURL, getJsonData } from '../../components/Http/fetch.js';
import { iterateRecursively } from '../../components/helpers.js';

import FormControl from '@mui/material/FormControl';
import TextField from '@mui/material/TextField';
import Stack from '@mui/material/Stack';
import Button from '@mui/material/Button';
import FormHelperText from '@mui/material/FormHelperText';
import LoadingButton from '@mui/lab/LoadingButton';
import Autocomplete from '@mui/material/Autocomplete';
import Stepper from '@mui/material/Stepper';
import Step from '@mui/material/Step';
import StepLabel from '@mui/material/StepLabel';
import Box from '@mui/material/Box';
import Slide from '@mui/material/Slide';

export class SignUpForm extends Component {
    constructor(props) {
        super(props);

        this.duration = 500;

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

            error: null,
            passwordsMatch: true,

            isSubmittingPhonenumber: false,
            isSubmittingPhonenumberCode: false,
            isSubmittingRegisteration: false,
        };
    }

    async previousStep() {
        if (this.state.activeStep > 0) {
            let key = this.state.activeStep;
            let previousKey = this.state.activeStep - 1;

            await this.exit(key, 'left');

            this.setState({
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

            this.setState({
                activeStep: nextKey
            });

            await this.enter(nextKey, 'left');
        }
    }

    exit(key, direction) {
        return new Promise((resolve) => {
            let newSteps = this.state.steps;
            newSteps[key].animationDirection = direction;
            newSteps[key].in = false;

            this.setState({
                steps: newSteps,
            });

            resolve();
        });
    }

    enter(key, direction) {
        return new Promise((resolve) => {
            setTimeout(() => {
                let newSteps = this.state.steps;
                newSteps[key].completed = true;
                newSteps[key].animationDirection = direction;
                newSteps[key].in = true;

                this.setState({
                    steps: newSteps,
                });

                resolve();
            }, this.duration);
        });
    }

    render() {
        return (
            <>
                <Stack >
                    <Stepper >
                        <Step key={0} completed={this.state.steps[1].completed} active={this.state.activeStep === 0}>
                            <StepLabel>
                                {translate('general/phonenumber/ucFirstLetterAllWords', this.props.currentLocaleName)}
                            </StepLabel>
                        </Step>
                        <Step key={1} completed={this.state.steps[1].completed} active={this.state.activeStep === 1}>
                            <StepLabel>
                                {translate('pages/auth/signup/steps/send-phone-number-verification-code', this.props.currentLocaleName)}
                            </StepLabel>
                        </Step>
                        <Step key={2} completed={this.state.steps[2].completed} active={this.state.activeStep === 2}>
                            <StepLabel>
                                {translate('pages/auth/signup/steps/fill-registration-form', this.props.currentLocaleName)}
                            </StepLabel>
                        </Step>
                    </Stepper>
                    <Box sx={{ mt: 1, mb: 1, display: 'flex' }}>
                        <Button variant='contained' disabled={this.state.activeStep === 0} type='button' onClick={this.previousStep} >{translate('general/back/ucFirstLetterFirstWord', this.props.currentLocaleName)}</Button>
                    </Box>
                    <Slide direction={this.state.steps[0].animationDirection} timeout={this.duration} in={this.state.steps[0].in} mountOnEnter unmountOnExit>
                        <Box component='form' onSubmit={this.handleSubmitPhonenumber} >
                            <FormControl sx={{ width: '100%' }} >
                                {this.state.error !== null && this.state.error}

                                <TextField onInput={this.handlePhonenumber} value={this.state.phonenumber} required label={translate('general/phonenumber/ucFirstLetterAllWords', this.props.currentLocaleName)} variant='standard' sx={{ m: 1 }} />

                                {this.state.isSubmittingPhonenumber && <LoadingButton loading variant="contained">{translate('general/submit/single', this.props.currentLocaleName)}</LoadingButton>}
                                {!this.state.isSubmittingPhonenumber && <Button type='submit' fullWidth onClick={this.handleSubmitPhonenumber} variant='contained' >{translate('general/submit/ucFirstLetterFirstWord', this.props.currentLocaleName)}</Button>}
                            </FormControl>
                        </Box>
                    </Slide>
                    <Slide direction={this.state.steps[1].animationDirection} timeout={this.duration} in={this.state.steps[1].in} mountOnEnter unmountOnExit>
                        <Box component='form' onSubmit={this.handleSubmitPhonenumberCode} >
                            <FormControl sx={{ width: '100%' }} >
                                {this.state.error !== null && this.state.error}

                                <TextField onInput={this.handlePhonenumberCode} required label={translate('pages/auth/signup/steps/security-code', this.props.currentLocaleName)} variant='standard' sx={{ m: 1 }} />

                                {this.state.isSubmittingPhonenumberCode && <LoadingButton loading variant="contained">{translate('general/submit/ucFirstLetterFirstWord', this.props.currentLocaleName)}</LoadingButton>}
                                {!this.state.isSubmittingPhonenumberCode && <Button type='submit' fullWidth onClick={this.handleSubmitPhonenumberCode} variant='contained' >{translate('general/submit/ucFirstLetterFirstWord', this.props.currentLocaleName)}</Button>}
                            </FormControl>
                        </Box>
                    </Slide>
                    <Slide direction={this.state.steps[2].animationDirection} timeout={this.duration} in={this.state.steps[2].in} mountOnEnter unmountOnExit>
                        <Box component='form' onSubmit={this.handleSubmitRegister} >
                            <FormControl sx={{ width: '100%' }} >
                                {this.state.error !== null && this.state.error}

                                <TextField onInput={this.handleFirstname} required label={translate('general/firstname/ucFirstLetterAllWords', this.props.currentLocaleName)} variant='standard' sx={{ m: 1 }} />
                                <TextField onInput={this.handleLastname} required label={translate('general/lastname/ucFirstLetterAllWords', this.props.currentLocaleName)} variant='standard' sx={{ m: 1 }} />
                                <TextField onInput={this.handleUsername} required label={translate('general/username/ucFirstLetterAllWords', this.props.currentLocaleName)} variant='standard' sx={{ m: 1 }} />
                                <TextField type='email' onInput={this.handleEmail} label={translate('general/email-address/ucFirstLetterFirstWord', this.props.currentLocaleName)} variant='standard' sx={{ m: 1 }} />
                                <TextField type='password' error={!this.state.passwordsMatch} onInput={this.handlePassword} required label={translate('general/password/ucFirstLetterFirstWord', this.props.currentLocaleName)} variant='standard' sx={{ m: 1 }} />
                                <TextField type='password' error={!this.state.passwordsMatch} onInput={this.handleConfirmPassword} required label={translate('general/confirm-password/ucFirstLetterAllWords', this.props.currentLocaleName)} variant='standard' sx={{ m: 1 }} />
                                <TextField onInput={this.handlePhonenumber} value={this.state.phonenumber} disabled required label={translate('general/phonenumber/ucFirstLetterAllWords', this.props.currentLocaleName)} variant='standard' sx={{ m: 1 }} />

                                {this.state.loadingGenders && <LoadingButton loading variant='contained'>{translate('general/gender/ucFirstLetterFirstWord', this.props.currentLocaleName)}</LoadingButton>}
                                {!this.state.loadingGenders && <Autocomplete
                                    sx={{ m: 1 }}
                                    disablePortal
                                    options={this.genders}
                                    onChange={this.handleGender}
                                    renderInput={(params) => <TextField {...params} label={translate('general/gender/ucFirstLetterFirstWord', this.props.currentLocaleName)} required variant='standard' />}
                                />}

                                <Box sx={{ mt: 1, mb: 1, display: 'flex' }}>
                                    <Button component='label' htmlFor='avatar' variant='contained' sx={{ mr: 1, ml: 1, flexGrow: 1 }}>
                                        {translate('pages/auth/signup/steps/choose-avatar', this.props.currentLocaleName) + (this.state.avatar.name ? (': ' + this.state.avatar.name) : '')}
                                        <TextField id='avatar' type='file' onInput={this.handleAvatar} required label={translate('general/avatar/ucFirstLetterFirstWord', this.props.currentLocaleName)} variant='standard' sx={{ display: 'none' }} />
                                    </Button>
                                    <Button variant='contained' type='button' onClick={this.resetAvatar} >{translate('general/reset/ucFirstLetterFirstWord', this.props.currentLocaleName)}</Button>
                                </Box>

                                <TextField type='number' onInput={this.handleAge} required label={translate('general/age/ucFirstLetterFirstWord', this.props.currentLocaleName)} variant='standard' sx={{ m: 1 }} min={1} />

                                {this.state.loadingStates && <LoadingButton loading variant='contained' sx={{ m: 1 }} >{translate('general/state/ucFirstLetterFirstWord', this.props.currentLocaleName)}</LoadingButton>}
                                {!this.state.loadingStates && <Autocomplete
                                    sx={{ m: 1 }}
                                    disablePortal
                                    options={this.states}
                                    onChange={this.handleState}
                                    renderInput={(params) => <TextField {...params} label={translate('general/state/ucFirstLetterFirstWord', this.props.currentLocaleName)} required variant='standard' />}
                                />}

                                {this.state.loadingCities && <LoadingButton loading variant='contained' sx={{ m: 1 }} >{translate('general/city/ucFirstLetterFirstWord', this.props.currentLocaleName)}</LoadingButton>}
                                {!this.state.loadingCities && <Autocomplete
                                    sx={{ m: 1 }}
                                    disablePortal
                                    options={this.cities}
                                    onChange={this.handleCity}
                                    renderInput={(params) => <TextField {...params} label={translate('general/city/ucFirstLetterFirstWord', this.props.currentLocaleName)} required variant='standard' />}
                                />}

                                <TextField onInput={this.handleAddress} multiline label={translate('general/address/ucFirstLetterFirstWord', this.props.currentLocaleName)} variant='standard' sx={{ m: 1 }} />

                                {this.state.isSubmittingRegisteration && <LoadingButton loading variant="contained">{translate('general/sign-up/ucFirstLetterAllWords', this.props.currentLocaleName)}</LoadingButton>}
                                {!this.state.isSubmittingRegisteration && <Button type='submit' fullWidth onClick={this.handleSubmitRegister} variant='contained' >{translate('general/sign-up/ucFirstLetterAllWords', this.props.currentLocaleName)}</Button>}
                            </FormControl>
                        </Box>
                    </Slide>
                </Stack>
            </>
        )
    }

    componentDidMount() {
        this.getGenders();
        this.getStates();
    }

    getGenders() {
        if (!this.props.currentLocaleName) {
            return;
        }

        getJsonData(backendURL() + '/api/' + this.props.currentLocaleName + '/genders')
            .then((res) => {
                return res.json();
            })
            .then((data) => {
                this.genders = [];
                for (let i = 0; i < data.length; i++) {
                    const gender = data[i];

                    this.genders.push({ id: i, label: gender });
                }
                this.setState({ loadingGenders: false });
            });
    }

    getStates() {
        if (!this.props.currentLocaleName) {
            return;
        }

        getJsonData(backendURL() + '/api/' + this.props.currentLocaleName + '/states')
            .then((res) => {
                return res.json();
            })
            .then((data) => {
                this.states = [];
                for (let i = 0; i < data.length; i++) {
                    const state = data[i];

                    this.states.push({ id: i, label: state });
                }
                this.setState({ loadingStates: false });
            });
    }

    getCities(state) {
        if (!this.props.currentLocaleName) {
            return;
        }

        this.setState({ loadingCities: true });
        getJsonData(backendURL() + '/api/' + this.props.currentLocaleName + '/cities?stateName=' + state)
            .then((res) => {
                return res.json();
            })
            .then((data) => {
                if (data.message) {
                    this.setState({ error: data.message });
                    return;
                }

                this.cities = [];
                for (let i = 0; i < data.length; i++) {
                    const city = data[i];

                    this.cities.push({ id: i, label: city });
                }
                this.setState({ loadingCities: false });
            });
    }

    handleSubmitPhonenumber(e) {
        e.preventDefault();
        this.setState({ isSubmittingPhonenumber: true });

        let input = {};

        input.phonenumber = this.state.phonenumber;

        postJsonData(backendURL() + '/register/send-phoennumber-verification-code', input, { 'X-CSRF-TOKEN': this.state.token })
            .then((res) => {
                return res.json();
            })
            .then((data) => {
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
                                this.nextStep();
                                break;

                            default:
                                break;
                        }
                    },
                    () => {
                        this.setState({ isSubmittingPhonenumber: false });
                    }
                );
            });
    }

    handleSubmitPhonenumberCode(e) {
        e.preventDefault();
        this.setState({ isSubmittingPhonenumberCode: true });

        let input = {};

        input.phonenumber = this.state.phonenumber;
        input.code = this.state.phonenumberCode;

        postJsonData(backendURL() + '/register/verify-phoennumber-verification-code', input, { 'X-CSRF-TOKEN': this.state.token })
            .then((res) => {
                return res.json();
            })
            .then((data) => {
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
                                            () => { }
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
                                this.nextStep();
                                break;

                            default:
                                break;
                        }
                    },
                    () => {
                        this.setState({ isSubmittingPhonenumberCode: false });
                    }
                );
            });
    }

    handleSubmitRegister(e) {
        e.preventDefault();
        this.setState({ isSubmittingRegisteration: true });

        let input = new FormData();
        // let input = {};

        input.append('firstname', this.state.firstname);
        input.append('lastname', this.state.lastname);
        input.append('username', this.state.username);
        input.append('email', this.state.email);
        input.append('password', this.state.password);
        input.append('password_confirmation', this.state.confirmPassword);
        input.append('gender', this.state.gender);
        input.append('phonenumber', this.state.phonenumber);
        if (this.state.avatar) {
            input.append('avatar', this.state.avatar, this.state.avatar.name);
        }

        input.append('age', this.state.age);
        input.append('state', this.state.state);
        input.append('city', this.state.city);
        input.append('address', this.state.address);

        postJsonData(backendURL() + '/register', input, { 'X-CSRF-TOKEN': this.state.token })
            .then((res) => {
                if (res.redirected) {
                    window.location.href = res.url;
                }
                return res.json();
            }).then((data) => {
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
