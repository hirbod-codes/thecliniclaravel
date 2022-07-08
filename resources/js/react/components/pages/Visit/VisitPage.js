import React, { Component } from 'react'
import { Link } from 'react-router-dom'

import { Box, Button, Divider, Fab, Grid, Stack, Tab, Tabs } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import ClearIcon from '@mui/icons-material/Clear';

import { translate } from '../../../traslation/translate'
import Header from '../../headers/Header'
import TabPanel from '../../Menus/TabPanel'
import LoadingButton from '@mui/lab/LoadingButton'
import { getJsonData, postJsonData } from '../../Http/fetch'
import { getFormatedDateAccordingToLocale, updateState } from '../../helpers';
import WeekDayInputComponents from './WeekDayInputComponents';
import { collectMessagesFromResponse, makeFormHelperTextComponents } from '../../Http/response';
import SlidingDialog from '../../Menus/SlidingDialog';

export class VisitPage extends Component {
    constructor(props) {
        super(props);

        this.getTargetUserId = this.getTargetUserId.bind(this);

        this.handleVisitTabChange = this.handleVisitTabChange.bind(this);

        this.handleWeekDayComponentFulfillment = this.handleWeekDayComponentFulfillment.bind(this);

        this.insertWeekDayStack = this.insertWeekDayStack.bind(this);
        this.removeWeekDayStack = this.removeWeekDayStack.bind(this);

        this.handleClosestVisitRefresh = this.handleClosestVisitRefresh.bind(this);
        this.handleWeeklyVisitRefresh = this.handleWeeklyVisitRefresh.bind(this);

        this.handleClosestVisitSubmit = this.handleClosestVisitSubmit.bind(this);
        this.handleWeeklyVisitSubmit = this.handleWeeklyVisitSubmit.bind(this);

        this.addWeekDay = this.addWeekDay.bind(this);

        this.handleResponseDialogClose = this.handleResponseDialogClose.bind(this);

        this.state = {
            week: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],

            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            targetUserId: null,

            visitTabsValue: 0,

            closestAvailableVisit: 0,
            closestAvailableVisitLoading: true,

            weeklyAvailableVisit: 0,
            weeklyAvailableVisitLoading: false,

            weekDayInputComponents: [],

            inputDataStructure: [],
            weekDayInputComponentsFulfillments: [],

            responseDialogOpen: false,
            responseErrors: [],
        };
    }

    componentDidMount() {
        this.getTargetUserId();

        this.insertWeekDayStack();

        this.handleClosestVisitRefresh();
    }

    getTargetUserId() {
        getJsonData('/account', { 'X-CSRF-TOKEN': this.state.token })
            .then((res) => {
                return res.json();
            })
            .then((data) => {
                this.setState({ targetUserId: data.id });
            });
    }

    handleVisitTabChange(e, newValue) {
        this.setState({ visitTabsValue: newValue });
    }

    handleClosestVisitRefresh(e) {
        this.setState({ closestAvailableVisitLoading: true });

        postJsonData('/visit/' + this.props.businessName + '/check', {}, { 'X-CSRF-TOKEN': this.state.token })
            .then((res) => {
                this.setState({ closestAvailableVisitLoading: false });
                return res.json();
            })
            .then((data) => {
                if (Object.hasOwnProperty.call(data, 'availableVisitTimestamp')) {
                    this.setState({ closestAvailableVisit: getFormatedDateAccordingToLocale(this.props.currentLocaleName, data.availableVisitTimestamp) });
                }
            });
    }

    handleClosestVisitSubmit(e) {
        this.setState({ closestAvailableVisitLoading: true });

        postJsonData('/visit/' + this.props.businessName, { targetUserId: this.state.targetUserId }, { 'X-CSRF-TOKEN': this.state.token })
            .then((res) => {
                this.setState({ closestAvailableVisitLoading: false });
                if (res.redirected) {
                    window.location.replace(res.url);
                }
                return res.json();
            })
            .then((data) => {
                window.location.replace('/');
            });
    }

    handleWeeklyVisitRefresh(e) {
        this.setState({ weeklyAvailableVisitLoading: true });

        if (!this.validateWeekDays()) {
            this.setState({ weeklyAvailableVisitLoading: false });
            return;
        }

        let inputDataStructure = {};

        for (let i = 0; i < this.state.inputDataStructure.length; i++) {
            const v = this.state.inputDataStructure[i];

            inputDataStructure[v.weekDay] = v.periods;
        }

        postJsonData('/visit/' + this.props.businessName + '/check', { weekDaysPeriods: inputDataStructure }, { 'X-CSRF-TOKEN': this.state.token })
            .then((res) => {
                this.setState({ weeklyAvailableVisitLoading: false });
                return res.json();
            })
            .then((data) => {
                if (Object.hasOwnProperty.call(data, 'availableVisitTimestamp')) {
                    this.setState({ weeklyAvailableVisit: getFormatedDateAccordingToLocale(this.props.currentLocaleName, data.availableVisitTimestamp) });
                } else {
                    let collectedData = collectMessagesFromResponse(data);
                    if (collectedData !== false) {
                        this.setState({ responseErrors: makeFormHelperTextComponents(collectedData), responseDialogOpen: true });
                    }
                }
            });
    }

    handleWeeklyVisitSubmit(e) {
        this.setState({ weeklyAvailableVisitLoading: true });

        if (!this.validateWeekDays()) {
            this.setState({ weeklyAvailableVisitLoading: false });
            return;
        }

        let inputDataStructure = {};

        for (let i = 0; i < this.state.inputDataStructure.length; i++) {
            const v = this.state.inputDataStructure[i];

            inputDataStructure[v.weekDay] = v.periods;
        }

        postJsonData('/visit/' + this.props.businessName, { targetUserId: this.state.targetUserId, weekDaysPeriods: inputDataStructure }, { 'X-CSRF-TOKEN': this.state.token })
            .then((res) => {
                this.setState({ weeklyAvailableVisitLoading: false });
                if (res.redirected) {
                    window.location.replace(res.url);
                }
                return res.json();
            })
            .then((data) => {
                window.location.replace('/');
            });
    }

    handleResponseDialogClose() {
        this.setState({ responseDialogOpen: false });
    }

    validateWeekDays() {
        if (this.state.weekDayInputComponentsFulfillments.length !== this.state.weekDayInputComponents.length) {
            return false;
        }

        for (let i = 0; i < this.state.weekDayInputComponentsFulfillments.length; i++) {
            const v = this.state.weekDayInputComponentsFulfillments[i];

            if (!v) {
                return false;
            }
        }
        return true;
    }

    async insertWeekDayStack(e) {
        let weekDayInputComponents = this.state.weekDayInputComponents;
        let weekDayInputComponentsFulfillments = this.state.weekDayInputComponentsFulfillments;
        let inputDataStructure = this.state.inputDataStructure;

        weekDayInputComponents.push(
            <WeekDayInputComponents
                key={this.state.weekDayInputComponents.length}
                id={this.state.weekDayInputComponents.length}

                week={this.state.week}

                onComponentFulfillment={this.handleWeekDayComponentFulfillment}
                addWeekDay={this.addWeekDay}
            />
        );

        weekDayInputComponentsFulfillments.push(false);
        inputDataStructure.push('');

        await updateState(this, { inputDataStructure: inputDataStructure, weekDayInputComponentsFulfillments: weekDayInputComponentsFulfillments, weekDayInputComponents: weekDayInputComponents });
    }

    async removeWeekDayStack(e) {
        let weekDayInputComponents = this.state.weekDayInputComponents;
        let weekDayInputComponentsFulfillments = this.state.weekDayInputComponentsFulfillments;
        let inputDataStructure = this.state.inputDataStructure;

        weekDayInputComponents.pop();
        weekDayInputComponentsFulfillments.pop();
        inputDataStructure.pop();

        await updateState(this, { inputDataStructure: inputDataStructure, weekDayInputComponentsFulfillments: weekDayInputComponentsFulfillments, weekDayInputComponents: weekDayInputComponents });
    }

    async handleWeekDayComponentFulfillment(id) {
        let weekDayInputComponentsFulfillments = this.state.weekDayInputComponentsFulfillments;

        weekDayInputComponentsFulfillments[id] = true;

        await updateState(this, { weekDayInputComponentsFulfillments: weekDayInputComponentsFulfillments });
    }

    async addWeekDay(id, weekDay) {
        let inputDataStructure = this.state.inputDataStructure;

        inputDataStructure[id] = weekDay;

        await updateState(this, { inputDataStructure: inputDataStructure });
    }

    render() {
        return (
            <>
                <Grid container spacing={1} sx={{ minHeight: '100vh' }}>
                    <Grid item xs={12} >
                        <Header
                            title={<Link to='/' style={{ textDecoration: 'none', color: 'white' }} >{translate('pages/visits/visit/title', this.props.currentLocaleName)}</ Link>}
                            currentLocaleName={this.props.currentLocaleName}
                            isLogInPage={false}
                        />
                    </Grid>
                    <Grid item xs={12} >
                        <Grid container >
                            <Grid item xs >
                            </Grid>
                            <Grid item xs={12} sm={9} md={6} >
                                <Stack
                                    direction="column"
                                    divider={<Divider orientation="horizontal" />}
                                    spacing={2}
                                >
                                    <div>{translate('pages/visits/visit/current-timezone', this.props.currentLocaleName)} {this.props.currentLocaleName === 'en' ? 'UTC' : 'Asia/Tehran'}</div>
                                    <>
                                        <Box sx={{ borderBottom: 1, borderColor: 'divider' }}>
                                            <Tabs value={this.state.visitTabsValue} onChange={this.handleVisitTabChange} variant="scrollable" scrollButtons={true} allowScrollButtonsMobile>
                                                <Tab label={translate('pages/visits/visit/closest', this.props.currentLocaleName)} id={'visit-tab-' + 0} />
                                                <Tab label={translate('pages/visits/visit/weekly-search', this.props.currentLocaleName)} id={'visit-tab-' + 1} />
                                            </Tabs>
                                        </Box>
                                        <TabPanel id={'visit-tabPanel-' + 0} value={this.state.visitTabsValue} index={0}>
                                            <Stack
                                                direction="column"
                                                divider={<Divider orientation="horizontal" />}
                                                spacing={2}
                                            >
                                                <div>{translate('pages/visits/visit/closest-visit-available', this.props.currentLocaleName)}{this.state.closestAvailableVisit}</div>

                                                <div>{
                                                    this.state.closestAvailableVisitLoading ?
                                                        <LoadingButton fullWidth loading variant="contained">loading</LoadingButton> :
                                                        <Button fullWidth variant='contained' onClick={this.handleClosestVisitRefresh}>{translate('general/refresh/single/ucFirstLetterFirstWord', this.props.currentLocaleName)}</Button>
                                                }</div>

                                                <div>{translate('pages/visits/visit/visit-accuracy-warning', this.props.currentLocaleName)}</div>

                                                <div>{
                                                    this.state.closestAvailableVisitLoading ?
                                                        <LoadingButton fullWidth loading variant="contained">loading</LoadingButton> :
                                                        <Button disabled={this.state.closestAvailableVisit === 0} fullWidth type='button' variant='contained' onClick={this.handleClosestVisitSubmit}>{translate('general/submit/single/ucFirstLetterFirstWord', this.props.currentLocaleName)}</Button>
                                                }</div>
                                            </Stack>
                                        </TabPanel>
                                        <TabPanel id={'visit-tabPanel-' + 1} value={this.state.visitTabsValue} index={1}>
                                            <Stack
                                                direction="column"
                                                divider={<Divider orientation="horizontal" />}
                                                spacing={2}
                                            >
                                                {this.state.weekDayInputComponents}

                                                <Stack
                                                    justifyContent='center'
                                                    direction="row"
                                                    spacing={2}
                                                >
                                                    <Fab size="medium" color="primary" onClick={this.insertWeekDayStack}>
                                                        <AddIcon />
                                                    </Fab>
                                                    <Fab size="medium" color="error" onClick={this.removeWeekDayStack}>
                                                        <ClearIcon />
                                                    </Fab>
                                                </Stack>

                                                <div>{translate('pages/visits/visit/weekly-visit-available', this.props.currentLocaleName)}{this.state.weeklyAvailableVisit}</div>

                                                <div>{
                                                    this.state.weeklyAvailableVisitLoading ?
                                                        <LoadingButton fullWidth loading variant="contained">loading</LoadingButton> :
                                                        <Button fullWidth type='button' variant='contained' onClick={this.handleWeeklyVisitRefresh}>{translate('general/refresh/single/ucFirstLetterFirstWord', this.props.currentLocaleName)}</Button>
                                                }</div>

                                                <div>{translate('pages/visits/visit/visit-accuracy-warning', this.props.currentLocaleName)}</div>

                                                <div>{
                                                    this.state.weeklyAvailableVisitLoading ?
                                                        <LoadingButton fullWidth loading variant="contained">loading</LoadingButton> :
                                                        <Button disabled={this.state.weeklyAvailableVisit === 0} fullWidth type='button' variant='contained' onClick={this.handleWeeklyVisitSubmit}>{translate('general/submit/single/ucFirstLetterFirstWord', this.props.currentLocaleName)}</Button>
                                                }</div>
                                            </Stack>

                                            <SlidingDialog
                                                open={this.state.responseDialogOpen}
                                                slideTrigger={<div></div>}
                                                onClose={this.handleResponseDialogClose}
                                            >
                                                {this.state.responseErrors}
                                            </SlidingDialog>

                                        </TabPanel>
                                    </>
                                </Stack>
                            </Grid>
                            <Grid item xs >
                            </Grid>
                        </Grid>
                    </Grid>
                    <Grid item xs={12} >
                        {/* <Footer /> */}
                    </Grid>
                </Grid>
            </>
        )
    }
}

export default VisitPage
