import React, { Component } from 'react'

import PropTypes from 'prop-types';

import CloseIcon from '@mui/icons-material/Close';
import { Alert, Autocomplete, Box, Button, FormControl, IconButton, Slide, Snackbar, Stack, Step, StepLabel, Stepper, TextField } from '@mui/material';
import { translate } from '../../../traslation/translate';
import LoadingButton from '@mui/lab/LoadingButton';
import { fetchData } from '../../Http/fetch';
import { updateState } from '../../helpers';
import { post_account_admin, post_account_doctor, post_account_operator, post_account_patient, post_account_secretary } from '../../Http/Api/accounts';
import { get_cities, get_genders, get_states } from '../../Http/Api/general';
import { get_dataType } from '../../Http/Api/roles';
import store from '../../../../redux/store';
import { connect } from 'react-redux';

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

        this.submit = this.submit.bind(this);
        this.submitPhonenumber = this.submitPhonenumber.bind(this);
        this.submitCode = this.submitCode.bind(this);

        this.getGenders = this.getGenders.bind(this);
        this.getStates = this.getStates.bind(this);
        this.getCities = this.getCities.bind(this);

        this.handleGender = this.handleGender.bind(this);
        this.handleState = this.handleState.bind(this);
        this.handleCity = this.handleCity.bind(this);

        this.updateDataType = this.updateDataType.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            feedbackMessages: [],

            loadingGenders: true,
            loadingStates: true,
            loadingCities: true,

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

            rule: store.getState().role.roles.createUser[0],
            dataType: '',

            isSubmittingPhonenumber: false,

            isSubmittingCode: false,

            isSubmitting: false,

            code: '',

            genders: [],
            states: [],
            cities: [],

            inputs: {
                firstname: '',
                lastname: '',
                username: '',
                email: '',
                phonenumber: '',
                password: '',
                password_confirmation: '',
                gender: '',
            },

            patient: {
                age: '',
                state: '',
                city: '',
                address: '',
            },
        };
    }

    componentDidMount() {
        this.updateDataType();
    }

    componentDidUpdate(prevProps, prevState) {
        if (prevState.steps[prevState.activeStep].name !== this.state.steps[this.state.activeStep].name && this.state.steps[this.state.activeStep].name === 'submission') {
            this.getGenders();
        }

        if (prevState.rule !== this.state.rule && this.state.rule === 'patient') {
            this.getStates();
        }
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

    async updateDataType(e) {
        const elm = e.target;

        let v = '';
        if (elm.tagName === 'INPUT') {
            v = elm.getAttribute('value');
        } else {
            v = elm.innerText;
        }

        await updateState(this, { rule: v });

        let r = await get_dataType(this.state.rule, this.state.token);

        if (r.response.status !== 200) {
            let value = null;
            if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
            value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
            this.setState({ feedbackMessages: value });
            return;
        }

        this.setState({ dataType: r.value });
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
                            <TextField onInput={(e) => this.setState((state) => { state.inputs.phonenumber = e.target.value; return state; })} value={this.state.inputs.phonenumber} required label={translate('general/phonenumber/single/ucFirstLetterAllWords')} variant='standard' sx={{ m: 1 }} />

                            {this.state.isSubmittingPhonenumber && <LoadingButton loading variant="contained">{translate('general/submit/single/allLowerCase')}</LoadingButton>}
                            {!this.state.isSubmittingPhonenumber && <Button type='submit' fullWidth onClick={this.submitPhonenumber} variant='contained' >{translate('general/submit/single/ucFirstLetterFirstWord')}</Button>}
                        </FormControl>
                    </Slide>

                    <Slide direction={this.state.steps[1].animationDirection} timeout={this.duration} in={this.state.steps[1].in} mountOnEnter unmountOnExit>
                        <FormControl sx={{ width: '100%' }} >
                            <TextField type='number' onInput={(e) => this.setState({ code: e.target.value })} required label={translate('general/code/single/ucFirstLetterAllWords')} variant='standard' sx={{ m: 1 }} />

                            {this.state.isSubmittingCode && <LoadingButton loading variant="contained">{translate('general/submit/single/allLowerCase')}</LoadingButton>}
                            {!this.state.isSubmittingCode && <Button type='submit' fullWidth onClick={this.submitCode} variant='contained' >{translate('general/submit/single/ucFirstLetterFirstWord')}</Button>}
                        </FormControl>
                    </Slide>

                    <Slide direction={this.state.steps[2].animationDirection} timeout={this.duration} in={this.state.steps[2].in} mountOnEnter unmountOnExit>
                        <FormControl sx={{ width: '100%' }} >
                            <Autocomplete
                                sx={{ m: 1 }}
                                disablePortal
                                options={store.getState().role.roles.createUser}
                                onChange={this.updateDataType}
                                renderInput={(params) => <TextField {...params} label={translate('general/rule/plural/ucFirstLetterFirstWord')} required variant='standard' />}
                            />

                            <TextField
                                value={this.state.inputs.firstname}
                                onInput={(e) => this.setState((state) => { state.inputs.firstname = e.target.value; return state; })}
                                label={translate('general/firstname/single/ucFirstLetterAllWords')}
                                required
                                variant='standard'
                                sx={{ m: 1 }}
                            />
                            <TextField
                                value={this.state.inputs.lastname}
                                onInput={(e) => this.setState((state) => { state.inputs.lastname = e.target.value; return state; })}
                                label={translate('general/lastname/single/ucFirstLetterAllWords')}
                                required
                                variant='standard'
                                sx={{ m: 1 }}
                            />
                            <TextField
                                value={this.state.inputs.username}
                                onInput={(e) => this.setState((state) => { state.inputs.username = e.target.value; return state; })}
                                label={translate('general/username/single/ucFirstLetterAllWords')}
                                required
                                variant='standard'
                                sx={{ m: 1 }}
                            />
                            <TextField
                                value={this.state.inputs.email}
                                onInput={(e) => this.setState((state) => { state.inputs.email = e.target.value; return state; })}
                                label={translate('general/email-address/single/ucFirstLetterFirstWord')} type='email' variant='standard' sx={{ m: 1 }} />

                            <TextField
                                value={this.state.inputs.password}
                                required
                                onInput={(e) => this.setState((state) => { state.inputs.password = e.target.value; return state; })}
                                label={translate('general/password/single/ucFirstLetterFirstWord')}
                                type='password'
                                variant='standard'
                                sx={{ m: 1 }}
                            />
                            <TextField
                                value={this.state.inputs.password_confirmation}
                                required
                                onInput={(e) => this.setState((state) => { state.inputs.password_confirmation = e.target.value; return state; })}
                                label={translate('general/password_confirmation/single/ucFirstLetterFirstWord')}
                                type='password'
                                variant='standard'
                                sx={{ m: 1 }}
                                error={this.state.inputs.password_confirmation !== this.state.inputs.password}
                            />

                            <TextField
                                disabled
                                value={this.state.inputs.phonenumber}
                                onInput={(e) => this.setState((state) => { state.inputs.phonenumber = e.target.value; return state; })}
                                label={translate('general/phonenumber/single/ucFirstLetterAllWords')}
                                required
                                variant='standard'
                                sx={{ m: 1 }}
                            />

                            {this.state.loadingGenders && <LoadingButton loading variant='contained'>{translate('general/gender/single/ucFirstLetterFirstWord')}</LoadingButton>}
                            {!this.state.loadingGenders && <Autocomplete
                                sx={{ m: 1 }}
                                disablePortal
                                options={this.state.genders}
                                onChange={this.handleGender}
                                renderInput={(params) => <TextField {...params} label={translate('general/gender/single/ucFirstLetterFirstWord')} required variant='standard' />}
                            />
                            }

                            {this.state.dataType === 'admin' ? null : null}
                            {this.state.dataType === 'doctor' ? null : null}
                            {this.state.dataType === 'secretary' ? null : null}
                            {this.state.dataType === 'operator' ? null : null}

                            {this.state.dataType === 'patient' ?
                                <>
                                    <TextField type='number' onInput={(e) => this.setState((state) => { state.patient.age = e.target.value; return state; })} required value={this.state.patient.age} label={translate('general/age/single/ucFirstLetterFirstWord')} variant='standard' sx={{ m: 1 }} min={1} />

                                    {this.state.loadingStates && <LoadingButton loading variant='contained' sx={{ m: 1 }} >{translate('general/state/single/ucFirstLetterFirstWord')}</LoadingButton>}
                                    {!this.state.loadingStates && <Autocomplete
                                        sx={{ m: 1 }}
                                        disablePortal
                                        value={this.state.patient.state}
                                        options={this.state.states}
                                        onChange={this.handleState}
                                        renderInput={(params) => <TextField {...params} label={translate('general/state/single/ucFirstLetterFirstWord')} required variant='standard' />}
                                    />}

                                    {this.state.loadingCities && <LoadingButton loading variant='contained' sx={{ m: 1 }} >{translate('general/city/single/ucFirstLetterFirstWord')}</LoadingButton>}
                                    {!this.state.loadingCities && <Autocomplete
                                        sx={{ m: 1 }}
                                        disablePortal
                                        value={this.state.patient.city}
                                        options={this.state.cities}
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

                {this.state.feedbackMessages.map((m, i) =>
                    <Snackbar
                        key={i}
                        open={m.open}
                        autoHideDuration={6000}
                        onClose={(e, r) => { this.handleFeedbackClose(e, r, i); }}
                        action={
                            <IconButton
                                size="small"
                                onClick={(e, r) => { this.handleFeedbackClose(e, r, i); }}
                            >
                                <CloseIcon fontSize="small" />
                            </IconButton>
                        }
                    >
                        <Alert onClose={(e, r) => { this.handleFeedbackClose(e, r, i); }} severity={m.color} sx={{ width: '100%' }}>
                            {m.message}
                        </Alert>
                    </Snackbar>
                )}
            </>
        )
    }

    async submit(e) {
        this.setState({ isSubmitting: true });

        let data = {};

        for (const k in this.state.inputs) {
            if (Object.hasOwnProperty.call(this.state.inputs, k)) {
                const input = this.state.inputs[k];
                if (input === '' || input === null) {
                    continue;
                }

                data[k] = input;
            }
        }

        for (const j in this.state[this.state.dataType]) {
            if (Object.hasOwnProperty.call(this.state[this.state.dataType], j)) {
                const ruleInput = this.state[this.state.dataType][j];
                if (ruleInput === '' || ruleInput === null) {
                    continue;
                }

                data[j] = ruleInput;
            }
        }

        data.roleName = this.state.rule;
        data.token = this.state.token;

        let r = null;
        switch (this.state.rule) {
            case 'admin':
                r = await post_account_admin(data);
                break;

            case 'doctor':
                r = await post_account_doctor(data);
                break;

            case 'secretary':
                r = await post_account_secretary(data);
                break;

            case 'operator':
                r = await post_account_operator(data);
                break;

            case 'patient':
                r = await post_account_patient(data);
                break;

            default:
                break;
        }

        this.setState({ isSubmitting: false });

        if (r.response.status === 200) {
            this.setState({ feedbackOpen: true, feedbackMessage: translate('general/successful/single/ucFirstLetterFirstWord'), feedbackColor: 'success' });
            this.nextStep();
            if (this.props.onSuccess !== undefined) {
                this.props.onSuccess();
            }
        } else {
            let value = null;
            if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
            value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
            this.setState({ feedbackMessages: value });
            if (this.props.onFailure !== undefined) {
                this.props.onFailure();
            }
        }
    }

    async submitPhonenumber(e) {
        this.setState({ isSubmittingPhonenumber: true });

        let r = null;
        r = await fetchData('get', '/auth/phonenumber-availability?phonenumber=' + this.state.inputs.phonenumber, {}, { 'X-CSRF-TOKEN': this.state.token, 'Accept': 'application/json' });
        if (r.response.status !== 200) {
            let value = null;
            if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
            value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
            this.setState({ feedbackMessages: value });

            this.setState({ isSubmittingPhonenumber: false });
            return;
        }

        r = null;
        let data = { phonenumber: this.state.inputs.phonenumber };
        r = await fetchData('post', '/auth/send-code-to-phonenumber', data, { 'X-CSRF-TOKEN': this.state.token }, [], false);
        this.setState({ isSubmittingPhonenumber: false });

        let value = null;
        if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
        value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
        this.setState({ feedbackMessages: value });

        if (r.response.status === 200) {
            this.nextStep();
        }
    }

    async submitCode(e) {
        this.setState({ isSubmittingCode: true });

        let data = {
            code: this.state.code,
            phonenumber: this.state.inputs.phonenumber,
        };

        let r = await fetchData('post', '/auth/verify-phonenumber', data, { 'X-CSRF-TOKEN': this.state.token }, [], false);
        this.setState({ isSubmittingCode: false });

        let value = null;
        if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
        value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
        this.setState({ feedbackMessages: value });

        if (r.response.status === 200) {
            this.nextStep();
        }
    }

    async getGenders() {
        let r = await get_genders(this.state.token);

        if (r.response.status === 200) {
            let genders = [];
            for (let i = 0; i < r.value.length; i++) {
                const gender = r.value[i];

                genders.push(gender);
            }
            this.setState({ genders: genders, loadingGenders: false });
        } else {
            let value = null;
            if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
            value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
            this.setState({ feedbackMessages: value });
        }
    }

    async getStates() {
        let r = await get_states(this.state.token);

        if (r.response.status === 200) {
            let states = [];
            for (let i = 0; i < r.value.length; i++) {
                const state = r.value[i];

                states.push(state);
            }
            this.setState({ states: states, loadingStates: false });
        } else {
            let value = null;
            if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
            value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
            this.setState({ feedbackMessages: value });
        }
    }

    async getCities(state) {
        this.setState({ loadingCities: true });

        let r = await get_cities(state, this.state.token);

        if (r.response.status === 200) {
            let cities = [];
            for (let i = 0; i < r.value.length; i++) {
                const city = r.value[i];

                cities.push(city);
            }
            this.setState({ cities: cities, loadingCities: false });
        } else {
            let value = null;
            if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
            value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
            this.setState({ feedbackMessages: value });
        }
    }

    handleGender(e) {
        const elm = e.target;

        let v = '';
        if (elm.tagName === 'INPUT') {
            v = elm.getAttribute('value');
        } else {
            v = elm.innerText;
        }

        this.setState((state) => { state.inputs.gender = v; return state; });
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

export default connect(null)(AccountCreator)
