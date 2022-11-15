import React, { Component } from 'react'

import PropTypes from 'prop-types';

import CloseIcon from '@mui/icons-material/Close';
import { Alert, Button, IconButton, Modal, Paper, Snackbar, Stack, Tab, Tabs } from '@mui/material';
import LoadingButton from '@mui/lab/LoadingButton';

import FindAccount from '../Account/FindAccount';
import FindOrder from '../Orders/FindOrder';
import TabPanel from '../TabPanel';
import { translate } from '../../../traslation/translate';
import WeekDayInputComponents from './WeekDayInputComponents';
import { localizeDate, updateState } from '../../helpers';
import { LocaleContext } from '../../localeContext';
import { PrivilegesContext } from '../../privilegesContext';
import { DateTime } from 'luxon';
import { post_visit_laser, post_visit_laser_check, post_visit_regular, post_visit_regular_check } from '../../Http/Api/visits';
import { get_work_schedule } from '../../Http/Api/general';

/**
 * VisitCreator
 * @augments {Component<Props, State>}
 */
export class VisitCreator extends Component {
    static contextType = PrivilegesContext;

    static propTypes = {
        businessName: PropTypes.string.isRequired,
        targetRoleName: PropTypes.string.isRequired,

        account: PropTypes.object,
        orderId: PropTypes.number,
        onSuccess: PropTypes.func,
        onFailure: PropTypes.func,
        onClose: PropTypes.func,
    }

    constructor(props) {
        super(props);

        this.handleFeedbackClose = this.handleFeedbackClose.bind(this);

        this.handleVisitFinderTabChange = this.handleVisitFinderTabChange.bind(this);

        this.getWorkSchdule = this.getWorkSchdule.bind(this);

        this.closeAccountSearchModal = this.closeAccountSearchModal.bind(this);
        this.openAccountSearchModal = this.openAccountSearchModal.bind(this);
        this.closeOrderSearchModal = this.closeOrderSearchModal.bind(this);
        this.openOrderSearchModal = this.openOrderSearchModal.bind(this);
        this.closeVisitInfoModal = this.closeVisitInfoModal.bind(this);
        this.openVisitInfoModal = this.openVisitInfoModal.bind(this);

        this.closestVisitRefresh = this.closestVisitRefresh.bind(this);

        this.handleVisitInfo = this.handleVisitInfo.bind(this);
        this.weeklyVisitRefresh = this.weeklyVisitRefresh.bind(this);

        this.handleOnCreate = this.handleOnCreate.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            feedbackMessages: [{ open: true, message: 'hiii', color: 'success' }],

            workSchdule: {},

            openAccountSearchModal: false,
            openOrderSearchModal: false,
            openVisitInfoModal: false,

            account: null,
            orderId: null,

            visitFinderTabsValue: 0,

            isSubmittingVisit: false,

            isRefreshingClosestVisit: false,
            closestVisitRefresh: null,

            isRefreshingWeeklyVisit: false,
            weeklyVisitRefresh: null,
            weeklyTImePatterns: null,

            locale: LocaleContext._currentValue.currentLocale.shortName,
        };
    }

    async componentDidMount() {
        if (this.props.orderId !== undefined) {
            await updateState(this, { orderId: this.props.orderId });
            this.openVisitInfoModal();
        } else {
            if (this.props.account !== undefined) {
                await updateState(this, { account: this.props.account });
                this.openOrderSearchModal();
            } else {
                this.openAccountSearchModal();
            }
        }

        this.getWorkSchdule();
    }

    async getWorkSchdule() {
        let r = await get_work_schedule(this.state.token);
        if (r.response.status === 200) {
            this.setState({ workSchdule: r.value });
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

    closeAccountSearchModal(e, reason) {
        if (reason === 'backdropClick') {
            this.setState({ openAccountSearchModal: false });
            if (this.props.onClose !== undefined) {
                this.props.onClose();
            }
            return;
        }

        this.setState({ openAccountSearchModal: false });
    }

    openAccountSearchModal(e) {
        this.setState({ openAccountSearchModal: true });
    }

    closeOrderSearchModal(event, reason) {
        if (reason === 'backdropClick') {
            this.setState({ openOrderSearchModal: false });
            if (this.props.onClose !== undefined) {
                this.props.onClose();
            }
            return;
        }

        this.setState({ openOrderSearchModal: false });
    }

    openOrderSearchModal() {
        this.setState({ openOrderSearchModal: true });
    }

    closeVisitInfoModal(e, reason) {
        if (reason === 'backdropClick') {
            this.setState({ openOrderSearchModal: false });
            if (this.props.onClose !== undefined) {
                this.props.onClose();
            }
            return;
        }

        this.setState({ openVisitInfoModal: false });
    }

    openVisitInfoModal(e) {
        this.setState({ openVisitInfoModal: true });
    }

    handleVisitFinderTabChange(e, newValue) {
        this.setState({ visitFinderTabsValue: newValue });
    }

    buildFeedbacks(m, i) {
        return <Snackbar
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
        </Snackbar>;
    }

    render() {
        return (
            <>
                {this.state.feedbackMessages.map((m, i) => this.buildFeedbacks(m, i))}

                {(this.context.retrieveUser !== undefined && this.context.retrieveUser.indexOf(this.props.targetRoleName) !== -1) &&
                    <Modal
                        open={this.state.openAccountSearchModal}
                        onClose={this.closeAccountSearchModal}
                    >
                        <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                            {this.state.feedbackMessages.map((m, i) => this.buildFeedbacks(m, i))}
                            <FindAccount handleAccount={async (account) => { await updateState(this, { account: account }); this.closeAccountSearchModal(null, null); this.openOrderSearchModal(); }} />
                        </Paper>
                    </Modal>
                }

                {(this.context.retrieveOrder !== undefined && this.context.retrieveOrder[this.props.businessName] !== undefined && this.context.retrieveOrder[this.props.businessName].indexOf(this.props.targetRoleName) !== -1) &&
                    <Modal
                        open={this.state.openOrderSearchModal}
                        onClose={this.closeOrderSearchModal}
                    >
                        <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                            {this.state.feedbackMessages.map((m, i) => this.buildFeedbacks(m, i))}
                            <FindOrder account={this.state.account === null ? {} : this.state.account} onSelectionModelChange={async (orderId) => { await updateState(this, { orderId: orderId }); this.closeOrderSearchModal(); this.openVisitInfoModal(null, null); }} businessName={this.props.businessName} />
                        </Paper>
                    </Modal>
                }

                {(this.context.createOrder !== undefined && this.context.createOrder[this.props.businessName] !== undefined && this.context.createOrder[this.props.businessName].indexOf(this.props.targetRoleName) !== -1) &&
                    <Modal
                        open={this.state.openVisitInfoModal}
                        onClose={this.closeVisitInfoModal}
                    >
                        <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                            {this.state.feedbackMessages.map((m, i) => this.buildFeedbacks(m, i))}
                            <Stack direction='column' spacing={2} style={{ height: '100%' }} >
                                <Tabs value={this.state.visitFinderTabsValue} onChange={this.handleVisitFinderTabChange} variant="scrollable" scrollButtons={true} allowScrollButtonsMobile sx={{ borderBottom: 1, borderColor: 'divider' }}>
                                    <Tab label={translate('pages/visits/visit/closest-visit-available')} />
                                    <Tab label={translate('pages/visits/visit/weekly-visit-available')} />
                                </Tabs>
                                <TabPanel value={this.state.visitFinderTabsValue} index={0} style={{ height: '100%' }} >
                                    <Stack direction='column' spacing={2} style={{ height: '100%' }} justifyContent='center' >
                                        {this.state.closestVisitRefresh !== null ? <p style={{ textAlign: 'center' }}>{this.state.closestVisitRefresh}</p> : null}
                                        {this.state.isRefreshingClosestVisit ?
                                            <LoadingButton loading fullWidth variant='contained'>
                                                {translate('general/refresh/single/ucFirstLetterFirstWord')}
                                            </LoadingButton> :
                                            <Button variant='contained' type='button' onClick={this.closestVisitRefresh}>
                                                {translate('general/refresh/single/ucFirstLetterFirstWord')}
                                            </Button>
                                        }
                                        {this.state.isSubmittingVisit ?
                                            <LoadingButton loading fullWidth variant='contained'>
                                                {translate('general/submit/single/ucFirstLetterFirstWord')}
                                            </LoadingButton> :
                                            <Button variant='contained' type='button' onClick={async (e) => { await updateState(this, { weeklyTImePatterns: null }); this.handleOnCreate(); }}>
                                                {translate('general/submit/single/ucFirstLetterFirstWord')}
                                            </Button>
                                        }
                                    </Stack>
                                </TabPanel>
                                <TabPanel value={this.state.visitFinderTabsValue} index={1} style={{ height: '100%' }} >
                                    <Stack direction='column' spacing={2} style={{ height: '100%' }} justifyContent='center' >
                                        <WeekDayInputComponents workSchdule={this.state.workSchdule} handleVisitInfo={this.handleVisitInfo} />
                                        {this.state.weeklyVisitRefresh !== null ? <p style={{ textAlign: 'center' }}>{this.state.weeklyVisitRefresh}</p> : null}
                                        {this.state.isRefreshingWeeklyVisit || (this.state.weeklyTImePatterns === null) ?
                                            <LoadingButton loading fullWidth variant='contained'>
                                                {translate('general/refresh/single/ucFirstLetterFirstWord')}
                                            </LoadingButton> :
                                            <Button variant='contained' type='button' onClick={this.weeklyVisitRefresh}>
                                                {translate('general/refresh/single/ucFirstLetterFirstWord')}
                                            </Button>
                                        }
                                        {this.state.isSubmittingVisit || (this.state.weeklyTImePatterns === null) ?
                                            <LoadingButton loading fullWidth variant='contained'>
                                                {translate('general/submit/single/ucFirstLetterFirstWord')}
                                            </LoadingButton> :
                                            <Button variant='contained' type='button' onClick={this.handleOnCreate}>
                                                {translate('general/submit/single/ucFirstLetterFirstWord')}
                                            </Button>
                                        }
                                    </Stack>
                                </TabPanel>
                            </Stack>
                        </Paper>
                    </Modal>
                }
            </>
        )
    }

    async handleVisitInfo(weeklyTImePatterns = null) {
        await updateState(this, { weeklyTImePatterns: weeklyTImePatterns });
        this.weeklyVisitRefresh(null);
    }

    async closestVisitRefresh(e) {
        this.setState({ isRefreshingClosestVisit: true });

        let closestVisitRefresh = null;
        switch (this.props.businessName) {
            case 'laser':
                closestVisitRefresh = (await post_visit_laser_check(this.state.orderId, null, this.state.token));
                break;

            case 'regular':
                closestVisitRefresh = (await post_visit_regular_check(this.state.orderId, null, this.state.token));
                break;

            default:
                break;
        }

        if (closestVisitRefresh.response.status === 200) {
            this.setState({ closestVisitRefresh: localizeDate('utc', DateTime.fromSeconds(Number(closestVisitRefresh.value.availableVisitTimestamp), { zone: 'utc' }).toISO(), this.state.locale, true) });
        } else {
            let value = null;
            if (Array.isArray(closestVisitRefresh.value)) { value = closestVisitRefresh.value; } else { value = [closestVisitRefresh.value]; }
            value = value.map((v, i) => { return { open: true, message: v, color: closestVisitRefresh.response.status === 200 ? 'success' : 'error' } });
            this.setState({ feedbackMessages: value });
        }

        this.setState({ isRefreshingClosestVisit: false });
    }

    async weeklyVisitRefresh(e) {
        this.setState({ isRefreshingWeeklyVisit: true });

        let weeklyTImePatterns = this.state.weeklyTImePatterns;

        let computedWeeklyTImePatterns = {};
        for (let i = 0; i < weeklyTImePatterns.length; i++) {
            const weeklyTImePattern = weeklyTImePatterns[i];

            computedWeeklyTImePatterns[weeklyTImePattern.weekDay] = weeklyTImePattern.timePeriods;
        }

        weeklyTImePatterns = computedWeeklyTImePatterns;

        let weeklyVisitRefresh = null;
        switch (this.props.businessName) {
            case 'laser':
                weeklyVisitRefresh = (await post_visit_laser_check(this.state.orderId, weeklyTImePatterns, this.state.token));
                break;

            case 'regular':
                weeklyVisitRefresh = (await post_visit_regular_check(this.state.orderId, weeklyTImePatterns, this.state.token));
                break;

            default:
                break;
        }

        if (weeklyVisitRefresh.response.status === 200) {
            this.setState({ weeklyVisitRefresh: localizeDate('utc', DateTime.fromSeconds(Number(weeklyVisitRefresh.value.availableVisitTimestamp), { zone: 'utc' }).toISO(), this.state.locale, true) });
        } else {
            let value = null;
            if (Array.isArray(weeklyVisitRefresh.value)) { value = weeklyVisitRefresh.value; } else { value = [weeklyVisitRefresh.value]; }
            value = value.map((v, i) => { return { open: true, message: v, color: weeklyVisitRefresh.response.status === 200 ? 'success' : 'error' } });
            await updateState(this, { feedbackMessages: value });
        }

        await updateState(this, { isRefreshingWeeklyVisit: false });
    }

    async handleOnCreate() {
        this.setState({ isSubmittingVisit: true });

        let data = {};
        data[this.props.businessName + 'OrderId'] = this.state.orderId;

        let weeklyTImePatterns = this.state.weeklyTImePatterns;
        if (weeklyTImePatterns !== null) {
            let computedWeeklyTImePatterns = {};
            for (let i = 0; i < weeklyTImePatterns.length; i++) {
                const weeklyTImePattern = weeklyTImePatterns[i];

                computedWeeklyTImePatterns[weeklyTImePattern.weekDay] = weeklyTImePattern.timePeriods;
            }

            weeklyTImePatterns = computedWeeklyTImePatterns;
        } else {
            weeklyTImePatterns = null;
        }

        let r = null;
        switch (this.props.businessName) {
            case 'laser':
                r = (await post_visit_laser(this.state.orderId, weeklyTImePatterns, this.state.token));
                break;

            case 'regular':
                r = (await post_visit_regular(this.state.orderId, weeklyTImePatterns, this.state.token));
                break;

            default:
                throw new Error('Insufficient information for sending visit creation request');
        }

        if (r.response.status === 200) {
            if (this.props.onSuccess !== undefined) {
                this.props.onSuccess();
            }
        } else {
            let value = null;
            if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
            value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
            await updateState(this, { feedbackMessages: value });
            if (this.props.onFailure !== undefined) {
                this.props.onFailure();
            }
        }

        await updateState(this, { isSubmittingVisit: false });
    }
}

export default VisitCreator
