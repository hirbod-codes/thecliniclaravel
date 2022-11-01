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
import { updateState } from '../../helpers';
import { PrivilegesContext } from '../../privilegesContext';
import { get_cities, get_genders, get_states } from '../../Http/Api/general';
import { delete_account, put_account } from '../../Http/Api/accounts';

/**
 * Account
 * @augments {Component<Props, State>}
 */
export class Account extends Component {
    static propTypes = {
        account: PropTypes.object.isRequired,
        accountRole: PropTypes.string.isRequired,
        onUpdateSuccess: PropTypes.func,
    }

    static contextType = PrivilegesContext;

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

            feedbackMessages: [],

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
            isUpdating: false,

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
        if (this.props.account.gender !== undefined) {
            this.getGenders();
        }

        if (this.props.account.state !== undefined) {
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
        this.setState({ openRegularVisitsViewModal: false });
    }

    render() {
        return (
            <>
                <Stack
                    direction='column'
                    spacing={2}
                >
                    <FormControl sx={{ width: '100%' }} >
                        <Box sx={{ mt: 1, mb: 1, display: 'flex' }}>
                            <Button component='label' htmlFor='avatar' variant='contained' sx={{ mr: 1, ml: 0, flexGrow: 1 }}>
                                {translate('pages/auth/signup/choose-avatar')} {((this.state.inputs.avatar !== undefined && this.state.inputs.avatar !== null && this.state.inputs.avatar.name !== undefined && this.state.inputs.avatar.name !== null) ? (': ' + this.state.inputs.avatar.name) : '')}
                                <TextField
                                    disabled={!(this.context.privileges !== undefined && this.context.privileges.editAvatar !== undefined && this.context.privileges.editAvatar[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole] !== undefined && Number(this.context.privileges.editAvatar[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole].boolean_value) === 1) || this.state.isUpdating}
                                    id='avatar'
                                    type='file'
                                    onInput={(e) => this.setState((state) => { state.inputs.avatar = e.target.files[0] ? e.target.files[0] : ''; return state; })}
                                    required
                                    label={translate('general/avatar/single/ucFirstLetterFirstWord')}
                                    variant='standard'
                                    sx={{ display: 'none' }}
                                />
                            </Button>
                            <Button variant='contained' type='button' onClick={(e) => this.setState((state) => { state.inputs.avatar = ''; return state; })} >{
                                translate('general/reset/single/ucFirstLetterFirstWord')}
                            </Button>
                        </Box>

                        <TextField
                            disabled={!(this.context.updatableColumns !== undefined && this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole] !== undefined && this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole].indexOf('firstname') !== -1) || this.state.isUpdating}
                            onInput={(e) => this.setState((state) => { state.inputs.firstname = e.target.value; return state; })}
                            label={translate('general/firstname/single/ucFirstLetterAllWords')}
                            value={this.state.inputs.firstname !== '' ? this.state.inputs.firstname : (this.props.account.firstname === null ? '' : this.props.account.firstname)}
                            required
                            variant='standard'
                            sx={{ m: 1 }}
                        />
                        <TextField
                            disabled={!(this.context.updatableColumns !== undefined && this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole] !== undefined && this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole].indexOf('lastname') !== -1) || this.state.isUpdating}
                            onInput={(e) => this.setState((state) => { state.inputs.lastname = e.target.value; return state; })}
                            label={translate('general/lastname/single/ucFirstLetterAllWords')}
                            value={this.state.inputs.lastname !== '' ? this.state.inputs.lastname : (this.props.account.lastname === null ? '' : this.props.account.lastname)}
                            required
                            variant='standard'
                            sx={{ m: 1 }} />
                        <TextField
                            disabled={!(this.context.updatableColumns !== undefined && this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole] !== undefined && this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole].indexOf('username') !== -1) || this.state.isUpdating}
                            onInput={(e) => this.setState((state) => { state.inputs.username = e.target.value; return state; })}
                            label={translate('general/username/single/ucFirstLetterAllWords')}
                            value={this.state.inputs.username !== '' ? this.state.inputs.username : (this.props.account.username === null ? '' : this.props.account.username)}
                            required
                            variant='standard'
                            sx={{ m: 1 }} />
                        <TextField
                            disabled={!(this.context.updatableColumns !== undefined && this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole] !== undefined && this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole].indexOf('email') !== -1) || this.state.isUpdating}
                            onInput={(e) => this.setState((state) => { state.inputs.email = e.target.value; return state; })}
                            label={translate('general/email-address/single/ucFirstLetterFirstWord')}
                            value={this.state.inputs.email !== '' ? this.state.inputs.email : (this.props.account.email === null ? '' : this.props.account.email)}
                            type='email'
                            variant='standard'
                            sx={{ m: 1 }} />

                        {this.state.isUpdating
                            ? <LoadingButton loading variant='contained'>{translate('pages/account/account/update-your-password')}</LoadingButton>
                            : <Button
                                disabled={!(this.context.updatableColumns !== undefined && this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole] !== undefined && this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole].indexOf('password') !== -1) || this.state.isUpdating}
                                variant='contained'
                                type='button'
                                onClick={() => this.setState({ isUpdatingPassword: true, openSendModal: true })}
                            >
                                {translate('pages/account/account/update-your-password')}
                            </Button>
                        }

                        <TextField
                            disabled
                            label={translate('general/phonenumber/single/ucFirstLetterAllWords')}
                            value={(this.props.account.phonenumber === null ? '' : this.props.account.phonenumber)}
                            required
                            variant='standard'
                            sx={{ m: 1 }}
                        />
                        {this.state.isUpdating ? <LoadingButton loading variant='contained'>{translate('pages/account/account/update-your-phone')}</LoadingButton>
                            : <Button
                                disabled={!(this.context.updatableColumns !== undefined && this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole] !== undefined && this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole].indexOf('phonenumber') !== -1) || this.state.isUpdating}
                                variant='contained'
                                type='button'
                                onClick={() => this.setState({ isUpdatingPhonenumber: true, openSendModal: true })}
                            >
                                {translate('pages/account/account/update-your-phone')}
                            </Button>
                        }

                        {this.state.loadingGenders && <LoadingButton loading variant='contained'>{translate('general/gender/single/ucFirstLetterFirstWord')}</LoadingButton>}
                        {!this.state.loadingGenders && <Autocomplete
                            sx={{ m: 1 }}
                            disablePortal
                            defaultValue={(this.props.account.gender === null ? '' : this.props.account.gender)}
                            options={this.genders !== undefined ? this.genders : []}
                            onChange={this.handleGender}
                            renderInput={(params) => <TextField
                                {...params}
                                disabled={!(this.context.updatableColumns !== undefined && this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole] !== undefined && this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole].indexOf('gender') !== -1) || this.state.isUpdating}
                                label={translate('general/gender/single/ucFirstLetterFirstWord')}
                                required
                                variant='standard'
                            />}
                        />}

                        {/* ------------------------------------------------------------------ */}

                        {this.props.account.age !== undefined ?
                            <TextField
                                disabled={!(this.context.updatableColumns !== undefined && this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole] !== undefined && this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole].indexOf('age') !== -1) || this.state.isUpdating}
                                type='number'
                                onInput={(e) => this.setState((state) => { state.inputs.age = e.target.value; return state; })}
                                required value={this.state.inputs.age !== '' ? this.state.inputs.age : (this.props.account.age === null ? '' : this.props.account.age)}
                                label={translate('general/age/single/ucFirstLetterFirstWord')}
                                variant='standard'
                                sx={{ m: 1 }}
                                min={1}
                            />
                            : null
                        }

                        {this.props.account.state !== undefined ?
                            <>
                                {this.state.loadingStates && <LoadingButton loading variant='contained' sx={{ m: 1 }} >{translate('general/state/single/ucFirstLetterFirstWord')}</LoadingButton>}
                                {!this.state.loadingStates && <Autocomplete
                                    sx={{ m: 1 }}
                                    disablePortal
                                    defaultValue={(this.props.account.state === null ? '' : this.props.account.state)}
                                    options={this.states !== undefined ? this.states : []}
                                    onChange={this.handleState}
                                    renderInput={(params) => <TextField
                                        {...params}
                                        disabled={!(this.context.updatableColumns !== undefined && this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole] !== undefined && this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole].indexOf('state') !== -1) || this.state.isUpdating}
                                        label={translate('general/state/single/ucFirstLetterFirstWord')}
                                        required
                                        variant='standard'
                                    />}
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
                                    defaultValue={(this.props.account.city === null ? '' : this.props.account.city)}
                                    options={this.cities !== undefined ? this.cities : []}
                                    onChange={this.handleCity}
                                    renderInput={(params) => <TextField
                                        {...params}
                                        disabled={!(this.context.updatableColumns !== undefined && this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole] !== undefined && this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole].indexOf('city') !== -1) || this.state.isUpdating}
                                        label={translate('general/city/single/ucFirstLetterFirstWord')}
                                        required
                                        variant='standard'
                                    />}
                                />}
                            </>
                            : null
                        }

                        {this.props.account.address !== undefined ?
                            <TextField
                                disabled={!(this.context.updatableColumns !== undefined && this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole] !== undefined && this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole].indexOf('address') !== -1) || this.state.isUpdating}
                                onInput={(e) => this.setState((state) => { state.inputs.address = e.target.value; return state; })}
                                multiline
                                value={this.state.inputs.address !== '' ? this.state.inputs.address : (this.props.account.address === null ? '' : this.props.account.address)}
                                label={translate('general/address/single/ucFirstLetterFirstWord')}
                                variant='standard'
                                sx={{ m: 1 }}
                            />
                            : null
                        }

                        {(
                            this.context.updatableColumns !== undefined &&
                            this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole] !== undefined &&
                            this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole].length > 0 &&
                            !this.state.isUpdating
                        ) ?
                            <Button type='submit' variant='contained' onClick={this.hsndleUpdate} fullWidth>
                                {translate('general/update/single/ucFirstLetterFirstWord')}
                            </Button> :
                            <LoadingButton loading variant="contained">{translate('general/update/single/ucFirstLetterAllWords')}</LoadingButton>
                        }
                    </FormControl>

                    {(this.context.retrieveOrder.laser !== undefined && this.context.retrieveOrder.laser.length > 0 && this.context.retrieveOrder.laser.indexOf(this.props.accountRole === this.context.role ? 'self' : this.props.accountRole) !== -1) &&
                        <Button type='button' variant='contained' onClick={(e) => { this.setState({ openLaserOrdersViewModal: true }); }} fullWidth>
                            {translate('general/show/single/ucFirstLetterFirstWord')} {translate('pages/orders/order/laser-orders')}
                        </Button>
                    }

                    {(this.context.retrieveOrder.regular !== undefined && this.context.retrieveOrder.regular.length > 0 && this.context.retrieveOrder.laser.indexOf(this.props.accountRole === this.context.role ? 'self' : this.props.accountRole) !== -1) &&
                        <Button type='button' variant='contained' onClick={(e) => { this.setState({ openRegularOrdersViewModal: true }); }} fullWidth>
                            {translate('general/show/single/ucFirstLetterFirstWord')} {translate('pages/orders/order/regular-orders')}
                        </Button>
                    }

                    {(this.context.retrieveVisit.laser !== undefined && this.context.retrieveVisit.laser.length > 0 && this.context.retrieveOrder.laser.indexOf(this.props.accountRole === this.context.role ? 'self' : this.props.accountRole) !== -1) &&
                        <Button type='button' variant='contained' onClick={(e) => { this.setState({ openLaserVisitsViewModal: true }); }} fullWidth>
                            {translate('general/show/single/ucFirstLetterFirstWord')} {translate('pages/visits/visit/laser-visit')}
                        </Button>
                    }

                    {(this.context.retrieveVisit.regular !== undefined && this.context.retrieveVisit.regular.length > 0 && this.context.retrieveOrder.laser.indexOf(this.props.accountRole === this.context.role ? 'self' : this.props.accountRole) !== -1) &&
                        <Button type='button' variant='contained' onClick={(e) => { this.setState({ openRegularVisitsViewModal: true }); }} fullWidth>
                            {translate('general/show/single/ucFirstLetterFirstWord')} {translate('pages/visits/visit/regular-visit')}
                        </Button>
                    }

                    {(this.context.deleteUser !== undefined && this.context.deleteUser.length > 0 && this.context.deleteUser.indexOf(this.props.accountRole === this.context.role ? 'self' : this.props.accountRole) !== -1 && !this.state.isDeleting) ?
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
                        <SelfLaserOrdersDataGrid account={this.props.account} accountRole={this.props.accountRole} />
                    </Paper>
                </Modal>
                <Modal
                    open={this.state.openRegularOrdersViewModal}
                    onClose={this.closeRegularOrdersViewModal}
                >
                    <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                        <SelfRegularOrdersDataGrid account={this.props.account} accountRole={this.props.accountRole} />
                    </Paper>
                </Modal>

                <Modal
                    open={this.state.openLaserVisitsViewModal}
                    onClose={this.closeLaserVisitsViewModal}
                >
                    <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                        <SelfVisitsDataGrid businessName='laser' account={this.props.account} accountRole={this.props.accountRole} />
                    </Paper>
                </Modal>
                <Modal
                    open={this.state.openRegularVisitsViewModal}
                    onClose={this.closeRegularVisitsViewModal}
                >
                    <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                        <SelfVisitsDataGrid businessName='regular' account={this.props.account} accountRole={this.props.accountRole} />
                    </Paper>
                </Modal>

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
        );
    }

    async getGenders() {
        let r = await get_genders(this.state.token);

        if (r.response.status === 200) {
            this.genders = [];
            for (let i = 0; i < r.value.length; i++) {
                const gender = r.value[i];

                this.genders.push(gender);
            }
            this.setState({ loadingGenders: false });
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
            this.states = [];
            for (let i = 0; i < r.value.length; i++) {
                const state = r.value[i];

                this.states.push(state);
            }
            this.setState({ loadingStates: false });
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
            this.cities = [];
            for (let i = 0; i < r.value.length; i++) {
                const city = r.value[i];

                this.cities.push(city);
            }
            this.setState({ loadingCities: false });
        } else {
            let value = null;
            if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
            value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
            this.setState({ feedbackMessages: value });
        }
    }

    async hsndleUpdate(e) {
        if (!(this.context.updatableColumns !== undefined && this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole] !== undefined && this.context.updatableColumns[this.props.accountRole === this.context.role ? 'self' : this.props.accountRole].length > 0) || this.state.isUpdating) {
            return;
        }

        this.setState({ isUpdating: true });

        let data, specialData = {};
        let avatar = null;
        for (const k in this.state.inputs) {
            if (k === 'phonenumber' || k === 'password' || k === 'password_confirmation' || k === 'code') {
                continue;
            }

            if (Object.hasOwnProperty.call(this.state.inputs, k)) {
                const v = this.state.inputs[k];

                if (v === '') {
                    continue;
                }

                if (k === 'avatar') {
                    avatar = v;
                }

                let isSpecial = true;

                [
                    'firstname',
                    'lastname',
                    'username',
                    'email',
                    'gender',
                ].forEach((val, i) => { if (val === k) { isSpecial = false; } });

                if (isSpecial) {
                    specialData[k] = v;
                } else {
                    data[k] = v;
                }
            }
        }

        let r = await put_account(this.props.account.id, data, specialData, avatar, this.state.token);

        if (r.response.status === 200) {
            this.setState({ feedbackMessages: [{ color: 'success', open: true, message: translate('general/successful/single/ucFirstLetterFirstWord') }] });
            if (this.props.onUpdateSuccess !== undefined) {
                this.props.onUpdateSuccess();
            }
        } else {
            let value = null;
            if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
            value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
            this.setState({ feedbackMessages: value });
        }

        this.setState({ isUpdating: false });
    }

    async hsndleDelete(e) {
        if (!(this.context.deleteUser !== undefined && this.context.deleteUser.length > 0 && this.context.deleteUser.indexOf(this.props.accountRole === this.context.role ? 'self' : this.props.accountRole) !== -1) || this.state.isDeleting) {
            return;
        }

        this.setState({ isDeleting: true });

        let r = await delete_account(this.props.account.idthis.state.token);
        if (r.response.status === 200) {
            setTimeout(() => {
                fetchData('get', '/logout', {}, { 'X-CSRF-TOKEN': this.state.token }).then((res) => { window.location.href = r.response.url; }, [], false);
            }, 1000);
        } else {
            let value = null;
            if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
            value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
            this.setState({ feedbackMessages: value });
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

        let r = await fetchData('post', '/auth/send-code-to-' + (this.state.sendMethod === 'phonenumber' ? 'phonenumber' : 'email'), data, { 'X-CSRF-TOKEN': this.state.token }, [], false);

        if (r.response.status === 200) {
            this.setState({ openCodeModal: true });
            this.closeSendModal(null, 'code');
        } else {
            let value = null;
            if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
            value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
            this.setState({ feedbackMessages: value });
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
        if (!this.state.isUpdatingPhonenumber) {
            data.password = this.state.inputs.password;
            data.password_confirmation = this.state.inputs.password_confirmation;
        }

        let r = null;
        if (this.state.isUpdatingPhonenumber) {
            r = await fetchData('put', '/auth/verify-phonenumber', data, { 'X-CSRF-TOKEN': this.state.token }, [], false);

            if (r.response.status === 200) {
                let data = {};
                data.phonenumber = this.props.account.phonenumber;
                data.newPhonenumber = this.state.inputs.phonenumber;
                r = await fetchData('put', '/auth/update-phonenumber', data, { 'X-CSRF-TOKEN': this.state.token }, [], false);

                if (r.response.status === 200) {
                    this.setState({ feedbackOpen: true, feedbackMessage: translate('general/successful/single/ucFirstLetterFirstWord'), feedbackColor: 'success' });
                    this.closeCodeModal(null, 'code');
                    setTimeout(() => {
                        document.window.href = document.window.location.pathname;
                    }, 200);
                } else {
                    let value = null;
                    if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
                    value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
                    this.setState({ feedbackMessages: value });
                }
            } else {
                let value = null;
                if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
                value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
                this.setState({ feedbackMessages: value });
            }
        } else {
            r = await fetchData('put', '/auth/reset-password', data, { 'X-CSRF-TOKEN': this.state.token }, [], false);

            if (r.response.status === 200) {
                this.closeCodeModal(null, 'code');
                setTimeout(() => {
                    document.window.href = document.window.location.pathname;
                }, 200);
            } else {
                let value = null;
                if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
                value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
                this.setState({ feedbackMessages: value });
            }
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