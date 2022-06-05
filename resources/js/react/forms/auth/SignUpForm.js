import React, { Component } from 'react'

import { postJsonData, backendURL, getJsonData } from '../../components/Http/fetch.js';

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
import { Toolbar } from '@mui/material';

export class SignUpForm extends Component {
    constructor(props) {
        super(props);

        this.duration = 500;

        this.state = {
            steps: [
                {
                    name: 'phonenumber',
                    completed: true,
                    animationDirection: 'left',
                    in: true
                },
                {
                    name: 'sendPhonenumberVerificationCode',
                    completed: false,
                    animationDirection: 'left',
                    in: false
                },
                {
                    name: 'fillRegistrationForm',
                    completed: false,
                    animationDirection: 'left',
                    in: false
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
            avatar: '',
            age: 0,
            state: '',
            city: '',
            address: '',

            loadingGenders: true,
            loadingStates: true,
            loadingCities: true,

            errorSubmittingPhonenumber: null,
            errorSubmittingPhonenumberCode: null,
            errorRegistration: null,
            passwordsMatch: true,

            isSubmittingPhonenumber: false,
            isSubmittingPhonenumberCode: false,
            isSubmittingRegisteration: false,
        };

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
        this.handleAvatar = this.handleAvatar.bind(this);
        this.handleAge = this.handleAge.bind(this);
        this.handleState = this.handleState.bind(this);
        this.handleCity = this.handleCity.bind(this);
        this.handleAddress = this.handleAddress.bind(this);

        this.handleSubmitPhonenumber = this.handleSubmitPhonenumber.bind(this);
        this.handleSubmitPhonenumberCode = this.handleSubmitPhonenumberCode.bind(this);
        this.handleSubmitRegister = this.handleSubmitRegister.bind(this);
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
                                Phone Number
                            </StepLabel>
                        </Step>
                        <Step key={1} completed={this.state.steps[1].completed} active={this.state.activeStep === 1}>
                            <StepLabel>
                                Send Phone Number Verification Code
                            </StepLabel>
                        </Step>
                        <Step key={2} completed={this.state.steps[2].completed} active={this.state.activeStep === 2}>
                            <StepLabel>
                                Fill Registration Form
                            </StepLabel>
                        </Step>
                    </Stepper>
                    <Toolbar>
                        <Box sx={{ flexGrow: 1 }}>
                            <Button variant='contained' disabled={this.state.activeStep === 0} type='button' onClick={this.previousStep} >Back</Button>
                        </Box>
                        <Box>
                            <Button variant='contained' disabled={this.state.activeStep === (this.state.steps.length - 1)} type='button' onClick={this.nextStep}>Next</Button>
                        </Box>
                    </Toolbar>
                    <Slide direction={this.state.steps[0].animationDirection} timeout={this.duration} in={this.state.steps[0].in} mountOnEnter unmountOnExit>
                        <Box component='form' onSubmit={this.handleSubmitPhonenumber} >
                            <FormControl sx={{ width: '100%' }} >
                                <TextField onInput={this.handlePhonenumber} value={this.state.phonenumber} required label="Phone Number" variant='standard' sx={{ m: 1 }} />

                                {this.state.errorSubmittingPhonenumber !== null && <FormHelperText error>{this.state.errorSubmittingPhonenumber}</FormHelperText>}

                                {this.state.isSubmittingPhonenumber && <LoadingButton loading variant="contained">Submit</LoadingButton>}
                                {!this.state.isSubmittingPhonenumber && <Button type='submit' fullWidth onClick={this.handleSubmitPhonenumber} variant='contained' >Submit</Button>}
                            </FormControl>
                        </Box>
                    </Slide>
                    <Slide direction={this.state.steps[1].animationDirection} timeout={this.duration} in={this.state.steps[1].in} mountOnEnter unmountOnExit>
                        <Box component='form' onSubmit={this.handleSubmitPhonenumberCode} >
                            <FormControl sx={{ width: '100%' }} >
                                <TextField onInput={this.handlePhonenumberCode} required label="Security Code" variant='standard' sx={{ m: 1 }} />

                                {this.state.errorSubmittingPhonenumberCode !== null && <FormHelperText error>{this.state.errorSubmittingPhonenumberCode}</FormHelperText>}

                                {this.state.isSubmittingPhonenumberCode && <LoadingButton loading variant="contained">Submit</LoadingButton>}
                                {!this.state.isSubmittingPhonenumberCode && <Button type='submit' fullWidth onClick={this.handleSubmitPhonenumber} variant='contained' >Submit</Button>}
                            </FormControl>
                        </Box>
                    </Slide>
                    <Slide direction={this.state.steps[2].animationDirection} timeout={this.duration} in={this.state.steps[2].in} mountOnEnter unmountOnExit>
                        <Box component='form' onSubmit={this.handleSubmitRegister} >
                            <FormControl sx={{ width: '100%' }} >
                                <TextField onInput={this.handleFirstname} required label="First Name" variant='standard' sx={{ m: 1 }} />
                                <TextField onInput={this.handleLastname} required label="Last Name" variant='standard' sx={{ m: 1 }} />
                                <TextField onInput={this.handleUsername} required label="User Name" variant='standard' sx={{ m: 1 }} />
                                <TextField type='email' onInput={this.handleEmail} label="Email Address" variant='standard' sx={{ m: 1 }} />
                                <TextField type='password' error={!this.state.passwordsMatch} onInput={this.handlePassword} required label="Password" variant='standard' sx={{ m: 1 }} />
                                <TextField type='password' error={!this.state.passwordsMatch} onInput={this.handleConfirmPassword} required label="Confirm Password" variant='standard' sx={{ m: 1 }} />
                                <TextField onInput={this.handlePhonenumber} value={this.state.phonenumber} disabled required label="Phone Number" variant='standard' sx={{ m: 1 }} />

                                {this.state.loadingGenders && <LoadingButton loading variant='contained'>Gender</LoadingButton>}
                                {!this.state.loadingGenders && <Autocomplete
                                    sx={{ m: 1 }}
                                    disablePortal
                                    options={this.genders}
                                    onChange={this.handleGender}
                                    renderInput={(params) => <TextField {...params} label="Gender" required variant='standard' />}
                                />}

                                <Button component='label' htmlFor='avatar' variant='contained' sx={{ m: 1 }}>Choose an avatar{this.state.avatar.name ? (': ' + this.state.avatar.name) : ''}
                                    <TextField id='avatar' type='file' onInput={this.handleAvatar} required label="Avatar" variant='standard' sx={{ display: 'none' }} />
                                </Button>

                                <TextField type='number' onInput={this.handleAge} required label="Age" variant='standard' sx={{ m: 1 }} min={1} />

                                {this.state.loadingStates && <LoadingButton loading variant='contained' sx={{ m: 1 }} >State</LoadingButton>}
                                {!this.state.loadingStates && <Autocomplete
                                    sx={{ m: 1 }}
                                    disablePortal
                                    options={this.states}
                                    onChange={this.handleState}
                                    renderInput={(params) => <TextField {...params} label="State" required variant='standard' />}
                                />}

                                {this.state.loadingCities && <LoadingButton loading variant='contained' sx={{ m: 1 }} >City</LoadingButton>}
                                {!this.state.loadingCities && <Autocomplete
                                    sx={{ m: 1 }}
                                    disablePortal
                                    options={this.cities}
                                    onChange={this.handleCity}
                                    renderInput={(params) => <TextField {...params} label="City" required variant='standard' />}
                                />}

                                <TextField onInput={this.handleAddress} multiline label="Address" variant='standard' sx={{ m: 1 }} />

                                {this.state.errorRegistration !== null && <FormHelperText error>{this.state.error}</FormHelperText>}

                                {this.state.isSubmittingRegisteration && <LoadingButton loading variant="contained">Sign Up</LoadingButton>}
                                {!this.state.isSubmittingRegisteration && <Button type='submit' fullWidth onClick={this.handleSubmitRegister} variant='contained' >Sign Up</Button>}
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
        getJsonData(backendURL() + '/api/en/genders').then((data) => {
            this.genders = [];
            for (let i = 0; i < data.length; i++) {
                const gender = data[i];

                this.genders.push({ id: i, label: gender });
            }
            this.setState({ loadingGenders: false });
        });
    }

    getStates() {
        getJsonData(backendURL() + '/api/en/states').then((data) => {
            this.states = [];
            for (let i = 0; i < data.length; i++) {
                const state = data[i];

                this.states.push({ id: i, label: state });
            }
            this.setState({ loadingStates: false });
        });
    }

    getCities(state) {
        console.log(state);
        this.setState({ loadingCities: true });
        getJsonData(backendURL() + '/api/en/cities?stateName=' + state).then((data) => {
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
        return;
        this.setState({ isSubmittingPhonenumber: true });

        let input = {};

        input.phonenumber = this.state.phonenumber;

        let token = document.head.querySelector('meta[name="csrf-token"]').getAttribute('content');

        postJsonData(backendURL() + '/register/send-phoennumber-verification-code', input, { 'X-CSRF-TOKEN': token }).then((data) => {
            console.log(data);
            if (data.errors) {
                var errorMessage = [];
                let i = 0;
                for (const k in data.errors) {
                    if (Object.hasOwnProperty.call(data.errors, k)) {
                        const error = data.errors[k];
                        errorMessage.push(<FormHelperText key={i} error>{error}</FormHelperText>);
                    }
                    i++;
                }

                this.setState({ errorSubmittingPhonenumber: errorMessage });
                this.setState({ isSubmittingPhonenumber: false });
            }

            if (data.message) {
                this.setState({ errorSubmittingPhonenumber: <FormHelperText>{data.message}</FormHelperText> });
                this.setState({ isSubmittingPhonenumber: false });

                return;
            }
        });
    }

    handleSubmitPhonenumberCode(e) {
        e.preventDefault();
        return;
        this.setState({ isSubmittingPhonenumberCode: true });

        let input = {};

        input.phonenumber = this.state.phonenumber;
        input.code = this.state.phonenumberCode;

        let token = document.head.querySelector('meta[name="csrf-token"]').getAttribute('content');

        postJsonData(backendURL() + '/register/verify-phoennumber-verification-code', input, { 'X-CSRF-TOKEN': token }).then((data) => {
            console.log(data);
            if (data.errors) {
                var errorMessage = [];
                let i = 0;
                for (const k in data.errors) {
                    if (Object.hasOwnProperty.call(data.errors, k)) {
                        const error = data.errors[k];
                        errorMessage.push(<FormHelperText key={i} error>{error}</FormHelperText>);
                    }
                    i++;
                }

                this.setState({ errorSubmittingPhonenumberCode: errorMessage });
                this.setState({ isSubmittingPhonenumberCode: false });
            }

            if (data.message) {
                this.setState({ errorSubmittingPhonenumberCode: <FormHelperText>{data.message}</FormHelperText> });
                this.setState({ isSubmittingPhonenumberCode: false });

                return;
            }
        });
    }

    handleSubmitRegister(e) {
        e.preventDefault();
        return;
        this.setState({ isSubmittingRegisteration: true });

        let input = {};

        input.firstname = this.state.firstname;
        input.lastname = this.state.lastname;
        input.username = this.state.username;
        input.email = this.state.email;
        input.password = this.state.password;
        input.password_confirmation = this.state.confirmPassword;
        input.gender = this.state.gender;
        input.phonenumber = this.state.phonenumber;
        if (this.state.avatar) {
            input.avatar = this.state.avatar;
        }

        input.age = this.state.age;
        input.state = this.state.state;
        input.city = this.state.city;
        input.address = this.state.address;
        console.log(input);

        let token = document.head.querySelector('meta[name="csrf-token"]').getAttribute('content');
        console.log(token);

        let res = postJsonData(backendURL() + '/register', input, { 'X-CSRF-TOKEN': token }, true);

        if (typeof res === 'string') {
            window.location = res.url;
            return;
        }

        res.then((data) => {
            console.log(data);
            if (data.errors) {
                var errorMessage = [];
                let i = 0;
                for (const k in data.errors) {
                    if (Object.hasOwnProperty.call(data.errors, k)) {
                        const error = data.errors[k];
                        errorMessage.push(<FormHelperText key={i} error>{error}</FormHelperText>);
                    }
                    i++;
                }

                this.setState({ errorRegistration: errorMessage });
                this.setState({ isSubmittingRegisteration: false });
            }

            if (data.message) {
                this.setState({ errorRegistration: <FormHelperText>{data.message}</FormHelperText> });
                this.setState({ isSubmittingRegisteration: false });

                return;
            }
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
        this.setState({ gender: e.target.innerText });
    }

    handlePhonenumber(e) {
        this.setState({ phonenumber: e.target.value });
    }

    handleAvatar(e) {
        this.setState({ avatar: e.target.files[0] ? e.target.files[0] : null });
    }

    handleAge(e) {
        this.setState({ age: e.target.value });
    }

    handleState(e) {
        this.setState({ state: e.target.innerText });
        this.getCities(e.target.innerText);
    }

    handleCity(e) {
        this.setState({ city: e.target.innerText });
    }

    handleAddress(e) {
        this.setState({ address: e.target.value });
    }
}

export default SignUpForm
