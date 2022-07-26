import React, { Component } from 'react'

import PropTypes from 'prop-types';

import { Button, Modal, Paper, Stack, Tab, Tabs } from '@mui/material';
import LoadingButton from '@mui/lab/LoadingButton';

import FindAccount from '../Account/FindAccount';
import FindOrder from '../Orders/FindOrder';
import TabPanel from '../TabPanel';
import { translate } from '../../../traslation/translate';
import WeekDayInputComponents from './WeekDayInputComponents';
import { getDateTimeFormatObject, updateState } from '../../helpers';
import { fetchData } from '../../Http/fetch';
import { LocaleContext } from '../../localeContext';

/**
 * VisitCreator
 * @augments {Component<Props, State>}
 */
export class VisitCreator extends Component {
    static propTypes = {
        privileges: PropTypes.object.isRequired,
        businessName: PropTypes.string.isRequired,

        account: PropTypes.object,
        orderId: PropTypes.number,
        onSuccess: PropTypes.func,
        onFailure: PropTypes.func,
        onClose: PropTypes.func,
    }

    constructor(props) {
        super(props);

        this.handleVisitFinderTabChange = this.handleVisitFinderTabChange.bind(this);

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
            weekDaysPeriods: null,
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

    render() {
        return (
            <>
                <Modal
                    open={this.state.openAccountSearchModal}
                    onClose={this.closeAccountSearchModal}
                >
                    <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                        <FindAccount handleAccount={async (account) => { await updateState(this, { account: account }); this.closeAccountSearchModal(null, null); this.openOrderSearchModal(); }} />
                    </Paper>
                </Modal>
                <Modal
                    open={this.state.openOrderSearchModal}
                    onClose={this.closeOrderSearchModal}
                >
                    <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                        <FindOrder account={this.state.account} privileges={this.props.privileges} onSelectionModelChange={async (orderId) => { await updateState(this, { orderId: orderId }); this.closeOrderSearchModal(); this.openVisitInfoModal(null, null); }} businessName={this.props.businessName} />
                    </Paper>
                </Modal>
                <Modal
                    open={this.state.openVisitInfoModal}
                    onClose={this.closeVisitInfoModal}
                >
                    <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
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
                                        <Button variant='contained' type='button' onClick={async (e) => { await updateState(this, { weekDaysPeriods: null }); this.handleOnCreate(); }}>
                                            {translate('general/submit/single/ucFirstLetterFirstWord')}
                                        </Button>
                                    }
                                </Stack>
                            </TabPanel>
                            <TabPanel value={this.state.visitFinderTabsValue} index={1} style={{ height: '100%' }} >
                                <Stack direction='column' spacing={2} style={{ height: '100%' }} justifyContent='center' >
                                    <WeekDayInputComponents handleVisitInfo={this.handleVisitInfo} />
                                    {this.state.weeklyVisitRefresh !== null ? <p style={{ textAlign: 'center' }}>{this.state.weeklyVisitRefresh}</p> : null}
                                    {this.state.isRefreshingWeeklyVisit || (this.state.weekDaysPeriods === null) ?
                                        <LoadingButton loading fullWidth variant='contained'>
                                            {translate('general/refresh/single/ucFirstLetterFirstWord')}
                                        </LoadingButton> :
                                        <Button variant='contained' type='button' onClick={this.weeklyVisitRefresh}>
                                            {translate('general/refresh/single/ucFirstLetterFirstWord')}
                                        </Button>
                                    }
                                    {this.state.isSubmittingVisit || (this.state.weekDaysPeriods === null) ?
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
            </>
        )
    }

    async handleVisitInfo(weekDaysPeriods = null) {
        await updateState(this, { weekDaysPeriods: weekDaysPeriods });
        this.weeklyVisitRefresh(null);
    }

    async closestVisitRefresh(e) {
        this.setState({ isRefreshingClosestVisit: true });
        let data = {};
        data[this.props.businessName + 'OrderId'] = this.state.orderId;

        let closestVisitRefresh = (await fetchData('post', '/visit/' + this.props.businessName + '/check', data, { 'X-CSRF-TOKEN': this.state.token })).value;
        if (closestVisitRefresh.availableVisitTimestamp !== undefined && typeof (closestVisitRefresh.availableVisitTimestamp) === 'number') {
            const locale = LocaleContext._currentValue.currentLocale.shortName;

            this.setState({ closestVisitRefresh: getDateTimeFormatObject(locale).format(new Date(closestVisitRefresh.availableVisitTimestamp * 1000)) });
        }

        this.setState({ isRefreshingClosestVisit: false });
    }

    async weeklyVisitRefresh(e) {
        this.setState({ isRefreshingWeeklyVisit: true });
        let data = {};
        data[this.props.businessName + 'OrderId'] = this.state.orderId;

        let weekDaysPeriods = this.state.weekDaysPeriods;
        let computedWeekDaysPeriods = {};
        for (let i = 0; i < weekDaysPeriods.length; i++) {
            const weekDaysPeriod = weekDaysPeriods[i];

            computedWeekDaysPeriods[weekDaysPeriod.weekDay] = weekDaysPeriod.timePeriods;
        }

        data.weekDaysPeriods = computedWeekDaysPeriods;

        let weeklyVisitRefresh = (await fetchData('post', '/visit/' + this.props.businessName + '/check', data, { 'X-CSRF-TOKEN': this.state.token })).value;
        if (weeklyVisitRefresh.availableVisitTimestamp !== undefined && typeof (weeklyVisitRefresh.availableVisitTimestamp) === 'number') {
            const locale = LocaleContext._currentValue.currentLocale.shortName;

            this.setState({ weeklyVisitRefresh: getDateTimeFormatObject(locale).format(new Date(weeklyVisitRefresh.availableVisitTimestamp * 1000)) });
        }

        this.setState({ isRefreshingWeeklyVisit: false });
    }

    async handleOnCreate() {
        this.setState({ isSubmittingVisit: true });

        let data = {};
        data[this.props.businessName + 'OrderId'] = this.state.orderId;

        let weekDaysPeriods = this.state.weekDaysPeriods;
        if (weekDaysPeriods !== null) {
            let computedWeekDaysPeriods = {};
            for (let i = 0; i < weekDaysPeriods.length; i++) {
                const weekDaysPeriod = weekDaysPeriods[i];

                computedWeekDaysPeriods[weekDaysPeriod.weekDay] = weekDaysPeriod.timePeriods;
            }

            data.weekDaysPeriods = computedWeekDaysPeriods;
        }

        let r = await fetchData('post', '/visit/' + this.props.businessName, data, { 'X-CSRF-TOKEN': this.state.token });

        if (r.response.status === 200) {
            if (this.props.onSuccess !== undefined) {
                this.props.onSuccess();
            }
        } else {
            if (this.props.onFailure !== undefined) {
                this.props.onFailure();
            }
        }

        this.setState({ isSubmittingVisit: false });
    }
}

export default VisitCreator
