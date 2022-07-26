import React, { Component } from 'react'

import PropTypes from 'prop-types';

import CloseIcon from '@mui/icons-material/Close';
import { Alert, Autocomplete, Box, Button, FormControl, FormControlLabel, FormLabel, IconButton, Radio, RadioGroup, Slide, Snackbar, Stack, Step, StepLabel, Stepper, TextField } from '@mui/material';
import { translate } from '../../../traslation/translate';
import LoadingButton from '@mui/lab/LoadingButton';
import { LocaleContext } from '../../localeContext';
import { fetchData } from '../../Http/fetch';
import { updateState } from '../../helpers';

/**
 * AccountCreator
 * @augments {Component<Props, State>}
 */
export class AccountCreator extends Component {
    static propTypes = {
        onSuccess: PropTypes.func,
        onFailure: PropTypes.func,
    }

    constructor(props) {
        super(props);

        this.duration = 500;

        this.previousStep = this.previousStep.bind(this);
        this.nextStep = this.nextStep.bind(this);

        this.handleFeedbackClose = this.handleFeedbackClose.bind(this);

        this.submitPhonenumber = this.submitPhonenumber.bind(this);
        this.submitCode = this.submitCode.bind(this);

        this.getGenders = this.getGenders.bind(this);
        this.getStates = this.getStates.bind(this);
        this.getCities = this.getCities.bind(this);

        this.handleGender = this.handleGender.bind(this);
        this.handleState = this.handleState.bind(this);
        this.handleCity = this.handleCity.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            feedbackOpen: false,
            feedbackMessage: '',
            feedbackColor: 'info',

            steps: [
                {
                    name: 'phonenumber',
                    completed: false,
                    animationDirection: 'left',
                    in: true,
                },
                {
                    name: 'code',
                    completed: false,
                    animationDirection: 'left',
                    in: false,
                },
                {
                    name: 'submission',
                    completed: false,
                    animationDirection: 'left',
                    in: false,
                },
            ],
            activeStep: 0,

            rule: '',

            phonenumberSubnitionErrors: [],
            isSubmittingPhonenumber: false,
            code_created_at_encrypted: null,
            code_encrypted: null,
            phonenumber_encrypted: null,
            phonenumber_verified_at_encrypted: null,

            codeSubnitionErrors: [],
            isSubmittingCode: false,

            errors: [],
            isSubmitting: false,

            code: '',

            inputs: {
                firstname: '',
                lastname: '',
                username: '',
                email: '',
                phonenumber: '',
                password: '',
                password_confirmation: '',
                age: '',
                avatar: null,
            },

            patient: {
                genders: '',
                state: '',
                city: '',
                address: '',
            },
        };
    }

    componentDidMount() {
        if (this.state.rule === 'patient') {
            this.getGenders();
            this.getStates();
        }
    }

    handleFeedbackClose(event, reason) {
        if (reason === 'clickaway') {
            return;
        }

        this.setState({ feedbackOpen: false });
    }

    async previousStep() {
        if (this.state.activeStep <= 0) {
            return;
        }

        let key = this.state.activeStep;
        let previousKey = this.state.activeStep - 1;

        await this.exit(key, 'left');

        await updateState(this, {
            activeStep: previousKey
        });

        await this.enter(previousKey, 'right');
    }

    async nextStep() {
        if (this.state.activeStep >= this.state.steps.length) {
            return;
        }

        let key = this.state.activeStep;
        let nextKey = this.state.activeStep + 1;

        await this.exit(key, 'right');

        await updateState(this, {
            activeStep: nextKey
        });

        await this.enter(nextKey, 'left');
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
        return (
            <>
                <Stack spacing={2}>
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
                        <FormControl sx={{ width: '100%' }} >
                            {this.state.phonenumberSubnitionErrors !== null && this.state.phonenumberSubnitionErrors}

                            <TextField onInput={(e) => this.setState((state) => { state.inputs.phonenumber = e.target.value; return state; })} value={this.state.inputs.phonenumber} required label={translate('general/phonenumber/single/ucFirstLetterAllWords')} variant='standard' sx={{ m: 1 }} />

                            {this.state.isSubmittingPhonenumber && <LoadingButton loading variant="contained">{translate('general/submit/single/allLowerCase')}</LoadingButton>}
                            {!this.state.isSubmittingPhonenumber && <Button type='submit' fullWidth onClick={this.submitPhonenumber} variant='contained' >{translate('general/submit/single/ucFirstLetterFirstWord')}</Button>}
                        </FormControl>
                    </Slide>

                    <Slide direction={this.state.steps[1].animationDirection} timeout={this.duration} in={this.state.steps[1].in} mountOnEnter unmountOnExit>
                        <FormControl sx={{ width: '100%' }} >
                            {this.state.codeSubnitionErrors !== null && this.state.codeSubnitionErrors}

                            <TextField type='number' onInput={(e) => this.setState({ code: e.target.value })} value={this.state.inputs.phonenumber} required label={translate('general/code/single/ucFirstLetterAllWords')} variant='standard' sx={{ m: 1 }} />

                            {this.state.isSubmittingCode && <LoadingButton loading variant="contained">{translate('general/submit/single/allLowerCase')}</LoadingButton>}
                            {!this.state.isSubmittingCode && <Button type='submit' fullWidth onClick={this.submitCode} variant='contained' >{translate('general/submit/single/ucFirstLetterFirstWord')}</Button>}
                        </FormControl>
                    </Slide>

                    <Slide direction={this.state.steps[2].animationDirection} timeout={this.duration} in={this.state.steps[1].in} mountOnEnter unmountOnExit>
                        <FormControl sx={{ width: '100%' }} >
                            {this.state.errors}

                            <FormLabel id="demo-row-radio-buttons-group-label">{translate('general/rule/plural/ucFirstLetterFirstWord')}</FormLabel>
                            <RadioGroup
                                value={this.state.rule}
                                onChange={(e) => this.setState({ rule: e.target.value })}
                                row
                                name="row-radio-buttons-group"
                            >
                                <FormControlLabel value="admin" control={<Radio />} label={translate('general/admin/single/ucFirstLetterFirstWord')} />
                                <FormControlLabel value="doctor" control={<Radio />} label={translate('general/doctor/single/ucFirstLetterFirstWord')} />
                                <FormControlLabel value="secretary" control={<Radio />} label={translate('general/secretary/single/ucFirstLetterFirstWord')} />
                                <FormControlLabel value="operator" control={<Radio />} label={translate('general/operator/single/ucFirstLetterFirstWord')} />
                                <FormControlLabel value="patient" control={<Radio />} label={translate('general/patient/single/ucFirstLetterFirstWord')} />
                            </RadioGroup>

                            <Box sx={{ mt: 1, mb: 1, display: 'flex' }}>
                                <Button component='label' htmlFor='avatar' variant='contained' sx={{ mr: 1, ml: 0, flexGrow: 1 }}>
                                    {translate('pages/auth/signup/choose-avatar')} {((this.state.inputs.avatar !== undefined && this.state.inputs.avatar !== null && this.state.inputs.avatar.name !== undefined && this.state.inputs.avatar.name !== null) ? (': ' + this.state.inputs.avatar.name) : '')}
                                    <TextField id='avatar' type='file' onInput={(e) => this.setState((state) => { state.inputs.avatar = e.target.files[0] ? e.target.files[0] : ''; return state; })} required label={translate('general/avatar/single/ucFirstLetterFirstWord')} variant='standard' sx={{ display: 'none' }} />
                                </Button>
                                <Button variant='contained' type='button' onClick={(e) => this.setState((state) => { state.inputs.avatar = ''; return state; })} >{translate('general/reset/single/ucFirstLetterFirstWord')}</Button>
                            </Box>

                            <TextField value={this.state.inputs.firstname} onInput={(e) => this.setState((state) => { state.inputs.firstname = e.target.value; return state; })} label={translate('general/firstname/single/ucFirstLetterAllWords')} required variant='standard' sx={{ m: 1 }} />
                            <TextField value={this.state.inputs.lastname} onInput={(e) => this.setState((state) => { state.inputs.lastname = e.target.value; return state; })} label={translate('general/lastname/single/ucFirstLetterAllWords')} required variant='standard' sx={{ m: 1 }} />
                            <TextField value={this.state.inputs.username} onInput={(e) => this.setState((state) => { state.inputs.username = e.target.value; return state; })} label={translate('general/username/single/ucFirstLetterAllWords')} required variant='standard' sx={{ m: 1 }} />
                            <TextField value={this.state.inputs.email} onInput={(e) => this.setState((state) => { state.inputs.email = e.target.value; return state; })} label={translate('general/email-address/single/ucFirstLetterFirstWord')} type='email' variant='standard' sx={{ m: 1 }} />

                            <TextField value={this.state.inputs.password} onInput={(e) => this.setState((state) => { state.inputs.password = e.target.value; return state; })} label={translate('general/password-address/single/ucFirstLetterFirstWord')} type='password' variant='standard' sx={{ m: 1 }} />
                            <TextField value={this.state.inputs.password_confirmation} onInput={(e) => this.setState((state) => { state.inputs.password_confirmation = e.target.value; return state; })} label={translate('general/password_confirmation/single/ucFirstLetterFirstWord')} type='password' variant='standard' sx={{ m: 1 }} error={this.state.inputs.password_confirmation !== this.state.inputs.password} />

                            <TextField disabled value={this.state.inputs.phonenumber} onInput={(e) => this.setState((state) => { state.inputs.phonenumber = e.target.value; return state; })} label={translate('general/phonenumber/single/ucFirstLetterAllWords')} required variant='standard' sx={{ m: 1 }} />

                            {this.state.loadingGenders && <LoadingButton loading variant='contained'>{translate('general/gender/single/ucFirstLetterFirstWord')}</LoadingButton>}
                            {!this.state.loadingGenders && <Autocomplete
                                sx={{ m: 1 }}
                                disablePortal
                                options={this.genders}
                                onChange={this.handleGender}
                                renderInput={(params) => <TextField {...params} label={translate('general/gender/single/ucFirstLetterFirstWord')} required variant='standard' />}
                            />
                            }

                            {this.state.rule === 'admin' ? null : null}
                            {this.state.rule === 'doctor' ? null : null}
                            {this.state.rule === 'secretary' ? null : null}
                            {this.state.rule === 'operator' ? null : null}

                            {this.state.rule === 'patient' ?
                                <>
                                    <TextField type='number' onInput={(e) => this.setState((state) => { state.patient.age = e.target.value; return state; })} required value={this.state.patient.age} label={translate('general/age/single/ucFirstLetterFirstWord')} variant='standard' sx={{ m: 1 }} min={1} />

                                    {this.state.loadingStates && <LoadingButton loading variant='contained' sx={{ m: 1 }} >{translate('general/state/single/ucFirstLetterFirstWord')}</LoadingButton>}
                                    {!this.state.loadingStates && <Autocomplete
                                        sx={{ m: 1 }}
                                        disablePortal
                                        value={this.state.patient.state}
                                        options={this.states}
                                        onChange={this.handleState}
                                        renderInput={(params) => <TextField {...params} label={translate('general/state/single/ucFirstLetterFirstWord')} required variant='standard' />}
                                    />}

                                    {this.state.loadingCities && <LoadingButton loading variant='contained' sx={{ m: 1 }} >{translate('general/city/single/ucFirstLetterFirstWord')}</LoadingButton>}
                                    {!this.state.loadingCities && <Autocomplete
                                        sx={{ m: 1 }}
                                        disablePortal
                                        value={this.state.patient.city}
                                        options={this.cities}
                                        onChange={this.handleCity}
                                        renderInput={(params) => <TextField {...params} label={translate('general/city/single/ucFirstLetterFirstWord')} required variant='standard' />}
                                    />}

                                    <TextField onInput={(e) => this.setState((state) => { state.patient.address = e.target.value; return state; })} multiline value={this.state.patient.address} label={translate('general/address/single/ucFirstLetterFirstWord')} variant='standard' sx={{ m: 1 }} />
                                </>
                                : null
                            }

                            {this.state.isSubmitting && <LoadingButton loading variant="contained">{translate('general/submit/single/allLowerCase')}</LoadingButton>}
                            {!this.state.isSubmitting && <Button type='submit' fullWidth onClick={this.submit} variant='contained' >{translate('general/submit/single/ucFirstLetterFirstWord')}</Button>}
                        </FormControl>
                    </Slide>
                </Stack>

                <Snackbar
                    open={this.state.feedbackOpen}
                    autoHideDuration={6000}
                    onClose={this.handleFeedbackClose}
                    action={
                        <IconButton
                            size="small"
                            onClick={this.handleFeedbackClose}
                        >
                            <CloseIcon fontSize="small" />
                        </IconButton>
                    }
                >
                    <Alert onClose={this.handleFeedbackClose} severity={this.state.feedbackColor} sx={{ width: '100%' }}>
                        {this.state.feedbackMessage}
                    </Alert>
                </Snackbar>
            </>
        )
    }

    async submit(e) {
        this.setState({ isSubmitting: true });

        let data = {
            code_created_at_encrypted: this.state.code_created_at_encrypted,
            code_encrypted: this.state.code_encrypted,
            code: this.state.code,
            phonenumber_encrypted: this.state.phonenumber_encrypted,
            phonenumber_verified_at_encrypted: this.state.phonenumber_verified_at_encrypted,
        };

        for (const k in this.state.inputs) {
            if (Object.hasOwnProperty.call(this.state.inputs, k)) {
                const input = this.state.inputs[k];

                data[k] = input;
            }
        }

        for (const j in this.state[this.state.rule]) {
            if (Object.hasOwnProperty.call(this.state[this.state.rule], j)) {
                const ruleInput = this.state[this.state.rule][j];

                data[j] = ruleInput;
            }
        }

        let r = await fetchData('post', '/account/' + this.state.rule, data);
        if (r.response.status === 200) {
            this.setState({ feedbackOpen: true, feedbackMessage: translate('general/successful/single/ucFirstLetterFirstWord'), feedbackColor: 'success' });
            this.nextStep();
            if (this.props.onSuccess !== undefined) {
                this.props.onSuccess();
            }
        } else {
            this.setState({ feedbackOpen: true, feedbackMessage: translate('general/failure/single/ucFirstLetterFirstWord'), feedbackColor: 'error' });
            if (this.props.onFailure !== undefined) {
                this.props.onFailure();
            }
        }

        this.setState({ isSubmitting: false });
    }

    async submitPhonenumber(e) {
        this.setState({ isSubmittingPhonenumber: true });

        let data = { phonenumber: this.state.inputs.phonenumber };
        let r = await fetchData('post', '/account/send-phoennumber-verification-code', data, { 'X-CSRF-TOKEN': this.state.token });

        if (r.response.status === 200) {
            this.setState({
                code_created_at_encrypted: r.value.code_created_at_encrypted,
                code_encrypted: r.value.code_encrypted,
                phonenumber_encrypted: r.value.phonenumber_encrypted,
                phonenumber_verified_at_encrypted: r.value.phonenumber_verified_at_encrypted,
            });

            this.setState({ feedbackOpen: true, feedbackMessage: translate('general/successful/single/ucFirstLetterFirstWord'), feedbackColor: 'success' });
            this.nextStep();
        } else {
            this.setState({ feedbackOpen: true, feedbackMessage: translate('general/failure/single/ucFirstLetterFirstWord'), feedbackColor: 'error' });
            if (r.response.status === 422) {
                let messages = [];
                for (const k in r.value.errors) {
                    if (Object.hasOwnProperty.call(r.value.errors, k)) {
                        const errors = r.value.errors[k];

                        errors.forEach((v, i) => {
                            messages.push(v);
                        });
                    }
                }
                this.setState({ phonenumberSubnitionErrors: messages });
            }
        }

        this.setState({ isSubmittingPhonenumber: false });
    }

    async submitCode(e) {
        this.setState({ isSubmittingCode: true });

        let data = {
            code: this.state.code,
            phonenumber: this.state.inputs.phonenumber,
            code_created_at_encrypted: this.state.code_created_at_encrypted,
            code_encrypted: this.state.code_encrypted,
            phonenumber_encrypted: this.state.phonenumber_encrypted,
        };

        let r = await fetch('post', '/account/verify-phoennumber-verification-code', data, { 'X-CSRF-TOKEN': this.state.token });
        if (r.response.status === 200) {
            this.setState({ feedbackOpen: true, feedbackMessage: translate('general/successful/single/ucFirstLetterFirstWord'), feedbackColor: 'success' });
            this.nextStep();
        } else {
            this.setState({ feedbackOpen: true, feedbackMessage: translate('general/failure/single/ucFirstLetterFirstWord'), feedbackColor: 'error' });
            if (r.response.status === 422) {
                let messages = [];
                for (const k in r.value.errors) {
                    if (Object.hasOwnProperty.call(r.value.errors, k)) {
                        const errors = r.value.errors[k];

                        errors.forEach((v, i) => {
                            messages.push(v);
                        });
                    }
                }
                this.setState({ codeSubnitionErrors: messages });
            }
        }

        this.setState({ isSubmittingCode: false });
    }

    async getGenders() {
        const locale = LocaleContext._currentValue.currentLocale.shortName;
        let r = await fetchData('get', '/api/' + locale + '/genders', {}, { 'X-CSRF-TOKEN': this.state.token });
        if (r.response.status !== 200) {
            return;
        }

        this.genders = [];
        for (let i = 0; i < r.value.length; i++) {
            const gender = r.value[i];

            this.genders.push(gender);
        }
        this.setState({ loadingGenders: false });
    }

    async getStates() {
        const locale = LocaleContext._currentValue.currentLocale.shortName;
        let r = await fetchData('get', '/api/' + locale + '/states', {}, { 'X-CSRF-TOKEN': this.state.token });
        if (r.response.status !== 200) {
            return;
        }

        this.states = [];
        for (let i = 0; i < r.value.length; i++) {
            const state = r.value[i];

            this.states.push(state);
        }
        this.setState({ loadingStates: false });
    }

    async getCities(state) {
        this.setState({ loadingCities: true });
        const locale = LocaleContext._currentValue.currentLocale.shortName;
        let r = await fetchData('get', '/api/' + locale + '/cities?stateName=' + state, {}, { 'X-CSRF-TOKEN': this.state.token });
        if (r.response.status !== 200) {
            return;
        }

        if (r.value.message !== undefined) {
            this.setState({ error: r.value.message });
            return;
        }

        this.cities = [];
        for (let i = 0; i < r.value.length; i++) {
            const city = r.value[i];

            this.cities.push(city);
        }
        this.setState({ loadingCities: false });
    }

    handleGender(e) {
        const elm = e.target;

        let v = '';
        if (elm.tagName === 'INPUT') {
            v = elm.getAttribute('value');
        } else {
            v = elm.innerText;
        }

        this.setState((state) => { state.patient.gender = v; return state; });
    }

    handleState(e) {
        const elm = e.target;

        let v = '';
        if (elm.tagName === 'INPUT') {
            v = elm.getAttribute('value');
        } else {
            v = elm.innerText;
        }

        this.setState((state) => { state.patient.state = v; return state; });
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

        this.setState((state) => { state.patient.city = v; return state; });
    }
}

export default AccountCreator
