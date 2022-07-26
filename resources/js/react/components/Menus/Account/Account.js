import React, { Component } from 'react'

import PropTypes from 'prop-types';

import CloseIcon from '@mui/icons-material/Close';
import { Alert, Autocomplete, Box, Button, FormControl, IconButton, Modal, Paper, Snackbar, Stack, TextField } from '@mui/material';
import LoadingButton from '@mui/lab/LoadingButton';

import SelfLaserOrdersDataGrid from '../../Grids/Orders/SelfLaserOrdersDataGrid';
import SelfRegularOrdersDataGrid from '../../Grids/Orders/SelfRegularOrdersDataGrid';
import SelfVisitsDataGrid from '../../Grids/Visits/SelfVisitsDataGrid';
import { translate } from '../../../traslation/translate';
import { fetchData } from '../../Http/fetch';
import { LocaleContext } from '../../localeContext';
import { updateState } from '../../helpers';

/**
 * Account
 * @augments {Component<Props, State>}
 */
export class Account extends Component {
    static propTypes = {
        privileges: PropTypes.object.isRequired,
        account: PropTypes.object.isRequired,
        isSelf: PropTypes.bool.isRequired,
    }

    constructor(props) {
        super(props);

        this.handleFeedbackClose = this.handleFeedbackClose.bind(this);

        this.closeLaserOrdersViewModal = this.closeLaserOrdersViewModal.bind(this);
        this.closeRegularOrdersViewModal = this.closeRegularOrdersViewModal.bind(this);

        this.closeLaserVisitsViewModal = this.closeLaserVisitsViewModal.bind(this);
        this.closeRegularVisitsViewModal = this.closeRegularVisitsViewModal.bind(this);

        this.closeSendModal = this.closeSendModal.bind(this);
        this.closeCodeModal = this.closeCodeModal.bind(this);

        this.hsndleUpdate = this.hsndleUpdate.bind(this);
        this.hsndleDelete = this.hsndleDelete.bind(this);
        this.send = this.send.bind(this);
        this.resend = this.resend.bind(this);
        this.sendCode = this.sendCode.bind(this);

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

            openLaserOrdersViewModal: false,
            openRegularOrdersViewModal: false,

            openLaserVisitsViewModal: false,
            openRegularVisitsViewModal: false,

            openSendModal: false,
            openCodeModal: false,

            isSending: false,
            isSendingCode: false,

            sendMethod: null,

            isUpdatingPhonenumber: false,
            isUpdatingPassword: false,

            isUpdatable: false,
            isUpdating: false,
            isDeletable: false,
            isDeleting: false,

            loadingGenders: true,
            error: null,

            inputs: {
                firstname: '',
                lastname: '',
                username: '',
                email: '',
                phonenumber: '',
                password: '',
                password_confirmation: '',
                code: '',
                genders: '',
                age: '',
                avatar: null,

                state: '',
                city: '',
                address: '',
            },
        };
    }

    componentDidMount() {
        if (Boolean(this.props.privileges[(this.props.isSelf ? 'selfAccountUpdate' : 'accountUpdate')]) === true && this.state.isUpdatable === false) {
            this.setState({ isUpdatable: true });
        }

        if (Boolean(this.props.privileges[(this.props.isSelf ? 'selfAccountDelete' : 'accountDelete')]) === true && this.state.isDeletable === false) {
            this.setState({ isDeletable: true });
        }

        if (this.props.account.gender !== undefined) {
            this.getGenders();
        }

        if (this.props.account.state !== undefined) {
            this.getStates();
        }
    }

    handleFeedbackClose(event, reason) {
        if (reason === 'clickaway') {
            return;
        }

        this.setState({ feedbackOpen: false });
    }

    closeSendModal(e, r) {
        this.setState({ openSendModal: false });
        if (r === 'code') {
            return;
        }

        this.setState({ isUpdatingPhonenumber: false, isUpdatingPassword: false });
    }

    closeCodeModal(e, r) {
        this.setState({ openCodeModal: false });
        if (r === 'code') {
            return;
        }

        this.setState({ isUpdatingPhonenumber: false, isUpdatingPassword: false });
    }

    closeLaserOrdersViewModal() {
        this.setState({ openLaserOrdersViewModal: false });
    }

    closeRegularOrdersViewModal() {
        this.setState({ openRegularOrdersViewModal: false });
    }

    closeLaserVisitsViewModal() {
        this.setState({ openLaserVisitsViewModal: false });
    }

    closeRegularVisitsViewModal() {
        this.setState({ openLaserVisitsViewModal: false });
    }

    render() {
        console.log('this.props', this.props);
        console.log('this.state', this.state);
        return (
            <>
                <Stack
                    direction='column'
                    spacing={2}
                >
                    <FormControl sx={{ width: '100%' }} >
                        {this.state.error !== null && this.state.error}

                        <Box sx={{ mt: 1, mb: 1, display: 'flex' }}>
                            <Button component='label' htmlFor='avatar' variant='contained' sx={{ mr: 1, ml: 0, flexGrow: 1 }}>
                                {translate('pages/auth/signup/choose-avatar')} {((this.state.inputs.avatar !== undefined && this.state.inputs.avatar !== null && this.state.inputs.avatar.name !== undefined && this.state.inputs.avatar.name !== null) ? (': ' + this.state.inputs.avatar.name) : '')}
                                <TextField disabled={!this.state.isUpdatable || this.state.isUpdating} id='avatar' type='file' onInput={(e) => this.setState((state) => { state.inputs.avatar = e.target.files[0] ? e.target.files[0] : ''; return state; })} required label={translate('general/avatar/single/ucFirstLetterFirstWord')} variant='standard' sx={{ display: 'none' }} />
                            </Button>
                            <Button variant='contained' type='button' onClick={(e) => this.setState((state) => { state.inputs.avatar = ''; return state; })} >{translate('general/reset/single/ucFirstLetterFirstWord')}</Button>
                        </Box>

                        <TextField disabled={!this.state.isUpdatable || this.state.isUpdating} onInput={(e) => this.setState((state) => { state.inputs.firstname = e.target.value; return state; })} label={translate('general/firstname/single/ucFirstLetterAllWords')} value={this.state.inputs.firstname !== '' ? this.state.inputs.firstname : this.props.account.firstname} required variant='standard' sx={{ m: 1 }} />
                        <TextField disabled={!this.state.isUpdatable || this.state.isUpdating} onInput={(e) => this.setState((state) => { state.inputs.lastname = e.target.value; return state; })} label={translate('general/lastname/single/ucFirstLetterAllWords')} value={this.state.inputs.lastname !== '' ? this.state.inputs.lastname : this.props.account.lastname} required variant='standard' sx={{ m: 1 }} />
                        <TextField disabled={!this.state.isUpdatable || this.state.isUpdating} onInput={(e) => this.setState((state) => { state.inputs.username = e.target.value; return state; })} label={translate('general/username/single/ucFirstLetterAllWords')} value={this.state.inputs.username !== '' ? this.state.inputs.username : this.props.account.username} required variant='standard' sx={{ m: 1 }} />
                        <TextField disabled={!this.state.isUpdatable || this.state.isUpdating} onInput={(e) => this.setState((state) => { state.inputs.email = e.target.value; return state; })} label={translate('general/email-address/single/ucFirstLetterFirstWord')} value={this.state.inputs.email !== '' ? this.state.inputs.email : this.props.account.email} type='email' variant='standard' sx={{ m: 1 }} />

                        {this.state.isUpdating ? <LoadingButton loading variant='contained'>{translate('pages/account/account/update-your-password')}</LoadingButton>
                            : <Button disabled={!this.state.isUpdatable} variant='contained' type='button' onClick={() => this.setState({ isUpdatingPassword: true, openSendModal: true })} >{translate('pages/account/account/update-your-password')}</Button>
                        }

                        <TextField disabled label={translate('general/phonenumber/single/ucFirstLetterAllWords')} value={this.props.account.phonenumber} required variant='standard' sx={{ m: 1 }} />
                        {this.state.isUpdating ? <LoadingButton loading variant='contained'>{translate('pages/account/account/update-your-phone')}</LoadingButton>
                            : <Button disabled={!this.state.isUpdatable} variant='contained' type='button' onClick={() => this.setState({ isUpdatingPhonenumber: true, openSendModal: true })} >{translate('pages/account/account/update-your-phone')}</Button>
                        }

                        {this.state.loadingGenders && <LoadingButton loading variant='contained'>{translate('general/gender/single/ucFirstLetterFirstWord')}</LoadingButton>}
                        {!this.state.loadingGenders && <Autocomplete
                            sx={{ m: 1 }}
                            disablePortal
                            defaultValue={this.props.account.gender}
                            options={this.genders}
                            onChange={this.handleGender}
                            renderInput={(params) => <TextField disabled={!this.state.isUpdatable || this.state.isUpdating} {...params} label={translate('general/gender/single/ucFirstLetterFirstWord')} required variant='standard' />}
                        />}

                        {/* ------------------------------------------------------------------ */}

                        {this.props.account.age !== undefined ?
                            <TextField disabled={!this.state.isUpdatable || this.state.isUpdating} type='number' onInput={(e) => this.setState((state) => { state.inputs.age = e.target.value; return state; })} required value={this.state.inputs.age !== '' ? this.state.inputs.age : this.props.account.age} label={translate('general/age/single/ucFirstLetterFirstWord')} variant='standard' sx={{ m: 1 }} min={1} />
                            : null
                        }

                        {this.props.account.state !== undefined ?
                            <>
                                {this.state.loadingStates && <LoadingButton loading variant='contained' sx={{ m: 1 }} >{translate('general/state/single/ucFirstLetterFirstWord')}</LoadingButton>}
                                {!this.state.loadingStates && <Autocomplete
                                    sx={{ m: 1 }}
                                    disablePortal
                                    defaultValue={this.props.account.state}
                                    options={this.states}
                                    onChange={this.handleState}
                                    renderInput={(params) => <TextField disabled={!this.state.isUpdatable || this.state.isUpdating} {...params} label={translate('general/state/single/ucFirstLetterFirstWord')} required variant='standard' />}
                                />}
                            </>
                            : null
                        }


                        {this.props.account.city !== undefined ?
                            <>
                                {this.state.loadingCities && <LoadingButton loading variant='contained' sx={{ m: 1 }} >{translate('general/city/single/ucFirstLetterFirstWord')}</LoadingButton>}
                                {!this.state.loadingCities && <Autocomplete
                                    sx={{ m: 1 }}
                                    disablePortal
                                    defaultValue={this.props.account.city}
                                    options={this.cities}
                                    onChange={this.handleCity}
                                    renderInput={(params) => <TextField disabled={!this.state.isUpdatable || this.state.isUpdating} {...params} label={translate('general/city/single/ucFirstLetterFirstWord')} required variant='standard' />}
                                />}
                            </>
                            : null
                        }

                        {this.props.account.address !== undefined ?
                            <TextField onInput={(e) => this.setState((state) => { state.inputs.address = e.target.value; return state; })} disabled={!this.state.isUpdatable || this.state.isUpdating} multiline value={this.state.inputs.address !== '' ? this.state.inputs.address : this.props.account.address} label={translate('general/address/single/ucFirstLetterFirstWord')} variant='standard' sx={{ m: 1 }} />
                            : null
                        }

                        {(this.state.isUpdatable && !this.state.isUpdating) ?
                            <Button type='submit' variant='contained' onClick={this.hsndleUpdate} fullWidth>
                                {translate('general/update/single/ucFirstLetterFirstWord')}
                            </Button> :
                            <LoadingButton loading variant="contained">{translate('general/update/single/ucFirstLetterAllWords')}</LoadingButton>
                        }
                    </FormControl>

                    <Button type='button' variant='contained' onClick={(e) => { this.setState({ openLaserOrdersViewModal: true }); }} fullWidth>
                        {translate('general/show/single/ucFirstLetterFirstWord')} {translate('pages/orders/order/laser-orders')}
                    </Button>

                    <Button type='button' variant='contained' onClick={(e) => { this.setState({ openRegularOrdersViewModal: true }); }} fullWidth>
                        {translate('general/show/single/ucFirstLetterFirstWord')} {translate('pages/orders/order/regular-orders')}
                    </Button>

                    <Button type='button' variant='contained' onClick={(e) => { this.setState({ openLaserVisitsViewModal: true }); }} fullWidth>
                        {translate('general/show/single/ucFirstLetterFirstWord')} {translate('pages/visits/visit/laser-visit')}
                    </Button>

                    <Button type='button' variant='contained' onClick={(e) => { this.setState({ openRegularVisitsViewModal: true }); }} fullWidth>
                        {translate('general/show/single/ucFirstLetterFirstWord')} {translate('pages/visits/visit/regular-visit')}
                    </Button>

                    {(this.state.isDeletable && !this.state.isDeleting) ?
                        <Button type='submit' variant='contained' onClick={this.hsndleDelete} fullWidth color='error' >
                            {translate('general/delete/single/ucFirstLetterFirstWord')}
                        </Button>
                        :
                        <LoadingButton loading variant="contained">{translate('pages/account/account/delete-account')}</LoadingButton>
                    }
                </Stack>

                <Modal
                    open={this.state.openSendModal}
                    onClose={this.closeSendModal}
                >
                    <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                        <Stack
                            direction='column'
                            spacing={2}
                        >
                            <p>{translate('pages/account/account/choose-btw-verification-methods')} </p>
                            <Button fullWidth type='button' variant='contained' onClick={async () => { await updateState(this, { sendMethod: 'phonenumber' }); this.send() }}>{translate('general/phonenumber/single/ucFirstLetterFirstWord')}</Button>
                            <Button fullWidth type='button' variant='contained' onClick={async () => { await updateState(this, { sendMethod: 'email' }); this.send() }}>{translate('general/email/single/ucFirstLetterFirstWord')}</Button>
                        </Stack>
                    </Paper>
                </Modal>

                <Modal
                    open={this.state.openCodeModal}
                    onClose={this.closeCodeModal}
                >
                    <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                        <Stack
                            direction='column'
                            spacing={2}
                        >
                            <TextField variant='standard' type='text' onInput={(e) => this.setState((state) => { state.inputs.code = e.target.value; return state; })} label={translate('general/code/single/ucFirstLetterAllWords')} sx={{ m: 1 }} />
                            {this.state.isUpdatingPhonenumber ?
                                <TextField variant='standard' type='text' onInput={(e) => this.setState((state) => { state.inputs.phonenumber = e.target.value; return state; })} label={translate('general/phonenumber/single/ucFirstLetterAllWords')} sx={{ m: 1 }} />
                                :
                                (this.state.isUpdatingPassword ?
                                    <>
                                        <TextField variant='standard' type='text' onInput={(e) => this.setState((state) => { state.inputs.password = e.target.value; return state; })} label={translate('general/password/single/ucFirstLetterAllWords')} sx={{ m: 1 }} />
                                        <TextField error={this.state.inputs.password === this.state.inputs.password_confirmation}
                                            variant='standard' type='text' onInput={(e) => this.setState((state) => { state.inputs.password_confirmation = e.target.value; return state; })} label={translate('general/confirm-password/single/ucFirstLetterAllWords')} sx={{ m: 1 }} />
                                    </>
                                    : null)
                            }
                            <Button disabled={this.state.isSendingCode} type='button' variant='contained' onClick={this.sendCode}>{translate('general/submit/single/ucFirstLetterFirstWord')}</Button>
                            <Button disabled={this.state.isSending} type='button' variant='contained' onClick={this.resend}>{translate('general/resend/single/ucFirstLetterFirstWord')}</Button>
                        </Stack>
                    </Paper>
                </Modal>

                <Modal
                    open={this.state.openLaserOrdersViewModal}
                    onClose={this.closeLaserOrdersViewModal}
                >
                    <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                        <SelfLaserOrdersDataGrid account={this.props.account} privileges={this.props.privileges} />
                    </Paper>
                </Modal>
                <Modal
                    open={this.state.openRegularOrdersViewModal}
                    onClose={this.closeRegularOrdersViewModal}
                >
                    <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                        <SelfRegularOrdersDataGrid account={this.props.account} privileges={this.props.privileges} />
                    </Paper>
                </Modal>
                <Modal
                    open={this.state.openLaserVisitsViewModal}
                    onClose={this.closeLaserVisitsViewModal}
                >
                    <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                        <SelfVisitsDataGrid businessName='laser' account={this.props.account} privileges={this.props.privileges} />
                    </Paper>
                </Modal>
                <Modal
                    open={this.state.openRegularVisitsViewModal}
                    onClose={this.closeRegularVisitsViewModal}
                >
                    <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                        <SelfVisitsDataGrid businessName='regular' account={this.props.account} privileges={this.props.privileges} />
                    </Paper>
                </Modal>

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
        );
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

    async hsndleUpdate(e) {
        if (Boolean(this.props.privileges[(this.props.isSelf ? 'selfAccountUpdate' : 'accountUpdate')]) !== true || this.state.isUpdating === true) {
            return;
        }

        this.setState({ isUpdating: true });

        let data = {};
        for (const k in this.state.inputs) {
            console.log('k', k);
            if (k === 'avatar' || k === 'phonenumber' || k === 'password' || k === 'password_confirmation' || k === 'code') {
                continue;
            }

            if (Object.hasOwnProperty.call(this.state.inputs, k)) {
                const v = this.state.inputs[k];
                console.log('v', v);

                if (v === '') {
                    continue;
                }

                data[k] = v;
            }
        }

        let r = await fetchData('put', '/account/' + this.props.account.id, data, { 'X-CSRF-TOKEN': this.state.token });
        console.log(r);
        if (r.response.status === 200) {
            this.setState({ feedbackOpen: true, feedbackMessage: translate('general/successful/single/ucFirstLetterFirstWord'), feedbackColor: 'success' });
        } else {
            this.setState({ feedbackOpen: true, feedbackMessage: translate('general/failure/single/ucFirstLetterFirstWord'), feedbackColor: 'error' });
        }

        this.setState({ isUpdating: false });
    }

    async hsndleDelete(e) {
        if (Boolean(this.props.privileges[(this.props.isSelf ? 'selfAccountDelete' : 'accountDelete')]) !== true || this.state.isDeleting === true) {
            return;
        }

        this.setState({ isDeleting: true });

        let r = await fetchData('delete', '/account/' + this.props.account.id, {}, { 'X-CSRF-TOKEN': this.state.token });
        if (r.response.status === 200) {
            this.setState({ feedbackOpen: true, feedbackMessage: translate('general/successful/single/ucFirstLetterFirstWord'), feedbackColor: 'success' });
            setTimeout(() => {
                fetchData('get', '/logout', {}, { 'X-CSRF-TOKEN': this.state.token }).then((res) => { window.location.href = r.response.url; });
            }, 1000);
        } else {
            this.setState({ feedbackOpen: true, feedbackMessage: translate('general/failure/single/ucFirstLetterFirstWord'), feedbackColor: 'error' });
        }

        this.setState({ isDeleting: false });
    }

    async send(e) {
        this.setState({ isSending: true });

        let data = {};
        if (this.state.sendMethod === 'phonenumber') {
            data.phonenumber = this.props.account.phonenumber;
        } else {
            data.email = this.props.account.email;
        }

        let r = await fetchData('post', '/forgot-password', data, { 'X-CSRF-TOKEN': this.state.token });
        if (r.response.status === 200) {
            this.setState({ openCodeModal: true });
            this.setState({ feedbackOpen: true, feedbackMessage: translate('general/successful/single/ucFirstLetterFirstWord'), feedbackColor: 'success' });
            this.closeSendModal(null, 'code');
        } else {
            this.setState({ feedbackOpen: true, feedbackMessage: translate('general/failure/single/ucFirstLetterFirstWord'), feedbackColor: 'error' });
        }

        this.setState({ isSending: false });
    }

    resend() {
        this.send(null);
    }

    async sendCode(e) {
        this.setState({ isSendingCode: true });

        let data = {};
        data.code = this.state.inputs.code;

        if (this.state.sendMethod === 'email') {
            data.email = this.props.account.email;
        } else {
            data.phonenumber = this.props.account.phonenumber;
        }
        if (this.state.isUpdatingPhonenumber) {
            data.newPhonenumber = this.state.inputs.phonenumber;
        } else {
            data.password = this.state.inputs.password;
            data.password_confirmation = this.state.inputs.password_confirmation;
        }

        let r = await fetchData('put', '/reset-' + (this.state.isUpdatingPhonenumber ? 'phonenumber' : 'password'), data, { 'X-CSRF-TOKEN': this.state.token });
        if (r.response.status === 200) {
            this.setState({ feedbackOpen: true, feedbackMessage: translate('general/successful/single/ucFirstLetterFirstWord'), feedbackColor: 'success' });
            this.closeCodeModal(null, 'code');
        } else {
            this.setState({ feedbackOpen: true, feedbackMessage: translate('general/failure/single/ucFirstLetterFirstWord'), feedbackColor: 'error' });
        }

        this.setState({ isSendingCode: false });
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

        this.setState((state) => { state.inputs.state = v; return state; });
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

        this.setState((state) => { state.inputs.city = v; return state; });
    }
}

export default Account
