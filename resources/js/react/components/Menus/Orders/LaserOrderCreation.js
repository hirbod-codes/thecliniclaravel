import React, { Component } from 'react'

import PropTypes from 'prop-types';

import CloseIcon from '@mui/icons-material/Close';
import { Alert, Box, Button, CircularProgress, Divider, IconButton, Modal, Paper, Slide, Snackbar, Stack, Step, StepLabel, Stepper } from '@mui/material'

import { translate } from '../../../traslation/translate'
import PartsDataGrid from '../../Grids/Orders/PartsDataGrid'
import PackagesDataGrid from '../../Grids/Orders/PackagesDataGrid'
import { updateState } from '../../helpers'
import { fetchData } from '../../Http/fetch'
import LoadingButton from '@mui/lab/LoadingButton';
import FindAccount from '../Account/FindAccount';

/**
 * LaserOrderCreation
 * @augments {Component<Props, State>}
 */
export class LaserOrderCreation extends Component {
    static propTypes = {
        account: PropTypes.object,
        onCreated: PropTypes.func,
    }

    constructor(props) {
        super(props);

        this.submit = this.submit.bind(this);

        this.closeAccountModal = this.closeAccountModal.bind(this);

        this.duration = 500;
        this.previousStep = this.previousStep.bind(this);
        this.nextStep = this.nextStep.bind(this);
        this.exit = this.exit.bind(this);
        this.enter = this.enter.bind(this);

        this.onPartSelect = this.onPartSelect.bind(this);
        this.onPackageSelect = this.onPackageSelect.bind(this);

        this.handleFeedbackClose = this.handleFeedbackClose.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            steps: [
                {
                    name: 'select-parts',
                    completed: false,
                    animationDirection: 'left',
                    in: true,
                },
                {
                    name: 'select-packages',
                    completed: false,
                    animationDirection: 'left',
                    in: false,
                },
                {
                    name: 'result',
                    completed: false,
                    animationDirection: 'left',
                    in: false,
                }
            ],
            activeStep: 0,
            movementDisabled: false,

            accountModalOpen: false,
            account: null,

            selectedParts: [],
            selectedPackages: [],

            totalPrice: 0,
            totalPriceWithoutDiscount: 0,
            totalNeddedTime: 0,
            isCalculating: false,

            feedbackOpen: false,
            feedbackColor: 'info',
            feedbackMessage: '',

            isSubmitDisabled: false,
        };
    }

    componentDidMount() {
        if (Object.hasOwnProperty.call(this.props, 'account')) {
            this.setState({ account: this.props.account });
        } else {
            this.setState({ accountModalOpen: true });
        }
    }

    async previousStep() {
        if (this.state.activeStep <= 0) {
            return;
        }

        await updateState(this, {
            movementDisabled: true
        });

        let key = this.state.activeStep;
        let previousKey = this.state.activeStep - 1;

        await this.exit(key, 'left');

        await updateState(this, {
            activeStep: previousKey
        });

        await this.enter(previousKey, 'right');

        await updateState(this, {
            movementDisabled: false
        });
    }

    async nextStep() {
        if (this.state.activeStep >= (this.state.steps.length + 1)) {
            return;
        }

        await updateState(this, {
            movementDisabled: true
        });

        let key = this.state.activeStep;
        let nextKey = this.state.activeStep + 1;

        await this.exit(key, 'right');

        await updateState(this, {
            activeStep: nextKey
        });

        await this.enter(nextKey, 'left');

        await updateState(this, {
            movementDisabled: false
        });

        if (this.state.steps[nextKey].name === 'result') {
            this.calculate();
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
                newSteps[key].animationDirection = direction;
                newSteps[key].in = true;

                await updateState(this, {
                    steps: newSteps,
                });

                resolve();
            }, this.duration);
        });
    }

    handleFeedbackClose(event, reason) {
        if (reason === 'clickaway') {
            return;
        }

        this.setState({ feedbackOpen: false });
    }

    closeAccountModal(event, reason) {
        this.setState({ accountModalOpen: false });
    }

    render() {
        return (
            <>
                {this.state.account === null ? null :
                    <Stack sx={{ height: '100%' }} >
                        <Stepper >
                            <Step key={0} completed={this.state.steps[0].completed} active={true}>
                                <StepLabel>
                                    {translate('pages/orders/order/select-part/plural/ucFirstLetterAllWords')}
                                </StepLabel>
                            </Step>
                            <Step key={1} completed={this.state.steps[1].completed} active={true}>
                                <StepLabel>
                                    {translate('pages/orders/order/select-package/plural/ucFirstLetterAllWords')}
                                </StepLabel>
                            </Step>
                            <Step key={2} completed={this.state.steps[2].completed} active={true}>
                                <StepLabel>
                                    {translate('general/result/plural/ucFirstLetterAllWords')}
                                </StepLabel>
                            </Step>
                        </Stepper>
                        <Stack direction='row' sx={{ mt: 1 }} >
                            <Box sx={{ flex: 1 }} >
                                <Button variant='outlined' disabled={this.state.movementDisabled} type='button' onClick={this.previousStep} >
                                    {translate('general/back/single/ucFirstLetterFirstWord')}
                                </Button>
                            </Box>
                            <Button variant='outlined' type='button' onClick={this.nextStep} disabled={
                                this.state.movementDisabled ? true : ((this.state.activeStep === this.state.steps.length - 2) ? ((this.state.selectedParts.length === 0 && this.state.selectedPackages.length === 0) ? true : false) : false)
                            } >
                                {translate('general/next/single/ucFirstLetterFirstWord')}
                            </Button>
                        </Stack>
                        <Slide direction={this.state.steps[0].animationDirection} timeout={this.duration} in={this.state.steps[0].in} style={{ height: '100%' }} mountOnEnter unmountOnExit >
                            <div style={{ width: '100%', height: '100%' }} >
                                <PartsDataGrid selectedParts={this.state.selectedParts} onSelect={this.onPartSelect} gender={this.state.account.gender} businessName='laser' checkboxSelection />
                            </div>
                        </Slide>
                        <Slide direction={this.state.steps[1].animationDirection} timeout={this.duration} in={this.state.steps[1].in} style={{ height: '100%' }} mountOnEnter unmountOnExit >
                            <div style={{ width: '100%', height: '100%' }} >
                                <PackagesDataGrid selectedPackages={this.state.selectedPackages} onSelect={this.onPackageSelect} gender={this.state.account.gender} businessName='laser' checkboxSelection />
                            </div>
                        </Slide>
                        <Slide direction={this.state.steps[2].animationDirection} timeout={this.duration} in={this.state.steps[2].in} style={{ height: '100%' }} mountOnEnter unmountOnExit >
                            <Stack direction='column' divider={<Divider orientation="horizontal" />} >
                                <div>
                                    {translate('pages/orders/order/total-price')}: {this.state.isCalculating ? <CircularProgress size='small' /> : this.state.totalPrice}
                                </div>
                                <div>
                                    {translate('pages/orders/order/total-priceWithoutDiscount')}: {this.state.isCalculating ? <CircularProgress size='small' /> : this.state.totalPriceWithoutDiscount}
                                </div>
                                <div>
                                    {translate('pages/orders/order/total-neededTime')}: {this.state.isCalculating ? <CircularProgress size='small' /> : this.state.totalNeddedTime}
                                </div>
                                {this.state.isCalculating ?
                                    <LoadingButton varient='contained' type='button' loading>{translate('general/submit/single/ucFirstLetterFirstWord')}</LoadingButton> :
                                    <Button varient='contained' type='button' onClick={this.submit} disabled={this.state.isSubmitDisabled}>{translate('general/submit/single/ucFirstLetterFirstWord')}</Button>
                                }
                            </Stack>
                        </Slide>
                    </Stack >
                }

                <Modal
                    open={this.state.accountModalOpen}
                    onClose={this.closeAccountModal}
                >
                    <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                        <FindAccount handleAccount={(account) => this.setState({ account: account, accountModalOpen: false, feedbackOpen: true, feedbackMessage: translate('general/successful/single/ucFirstLetterFirstWord'), feedbackColor: 'success' })} />
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
        )
    }

    async onPartSelect(selectedParts) {
        await updateState(this, (state) => {
            state.selectedParts = selectedParts;

            state.steps[0].completed = (state.selectedParts.length === 0 && state.selectedPackages.length === 0) ? false : true;
            state.steps[1].completed = (state.selectedParts.length === 0 && state.selectedPackages.length === 0) ? false : true;
            return state;
        });
    }

    async onPackageSelect(selectedPackages) {
        await updateState(this, (state) => {
            state.selectedPackages = selectedPackages;

            state.steps[0].completed = (state.selectedParts.length === 0 && state.selectedPackages.length === 0) ? false : true;
            state.steps[1].completed = (state.selectedParts.length === 0 && state.selectedPackages.length === 0) ? false : true;
            return state;
        });
    }

    async calculate() {
        if (this.state.selectedPackages.length === 0 && this.state.selectedParts.length === 0) {
            return;
        }

        await updateState(this, { isCalculating: true });

        let data = {
            parts: this.state.selectedParts.map((v, i) => v.name),
            packages: this.state.selectedPackages.map((v, i) => v.name),
        };
        data.gender = this.state.account.gender;

        let prices = (await fetchData('post', '/laser/price-calculation', data, { 'X-CSRF-TOKEN': this.state.token })).value;

        await updateState(this, {
            isCalculating: false,
            totalPrice: prices.price,
            totalPriceWithoutDiscount: prices.priceWithoutDiscount,
            totalNeddedTime: (await fetchData('post', '/laser/time-calculation', data, { 'X-CSRF-TOKEN': this.state.token })).value,
        });
    }

    async submit(e) {
        this.setState({ isCalculating: true });
        let data = {
            accountId: this.state.account.id,
            businessName: 'laser',
            parts: this.state.selectedParts.map((v, i) => v.name),
            packages: this.state.selectedPackages.map((v, i) => v.name),
        };

        let r = await fetchData('post', '/order', data, { 'X-CSRF-TOKEN': this.state.token });

        let state = {};
        state.isSubmitDisabled = true;
        state.feedbackOpen = true;
        if (r.response.status === 200) {
            this.props.onCreated();
            state.feedbackColor = 'success';
            state.feedbackMessage = translate('general/successful/single/ucFirstLetterFirstWord');
        } else {
            state.feedbackColor = 'error';
            state.feedbackMessage = translate('general/failure/single/ucFirstLetterFirstWord');
        }
        state.isCalculating = false;
        this.setState(state);
    }
}

export default LaserOrderCreation
