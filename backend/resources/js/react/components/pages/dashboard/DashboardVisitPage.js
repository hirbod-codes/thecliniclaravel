import React, { Component } from 'react'
import { Link } from 'react-router-dom';

import CloseIcon from '@mui/icons-material/Close';
import { Alert, Grid, IconButton, Snackbar, Tab, Tabs } from '@mui/material';

import { translate } from '../../../traslation/translate';
import SelfVisitsDataGrid from '../../Grids/Visits/SelfVisitsDataGrid';
import Header from '../../headers/Header';
import TabPanel from '../../Menus/TabPanel';
import VisitsServerDataGrid from '../../Grids/Visits/VisitsServerDataGrid';
import { canReadSelfVisit, canReadVisits } from '../../roles/visit';
import store from '../../../../redux/store';
import { connect } from 'react-redux';

export class DashboardVisitPage extends Component {
    constructor(props) {
        super(props);

        this.handleFeedbackClose = this.handleFeedbackClose.bind(this);

        this.handleVisitPageTabChange = this.handleVisitPageTabChange.bind(this);
        this.handleSelfVisitTabChange = this.handleSelfVisitTabChange.bind(this);
        this.handleVisitTabChange = this.handleVisitTabChange.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            isLoading: true,

            visitPageTabsValue: 0,
            selfVisitTabsValue: 0,
            visitTabsValue: 0,

            feedbackOpen: false,
            feedbackMessage: '',
            feedbackColor: 'info',
        };
    }

    handleVisitPageTabChange(e, newValue) {
        this.setState({ visitPageTabsValue: newValue })
    }

    handleSelfVisitTabChange(e, newValue) {
        this.setState({ selfVisitTabsValue: newValue })
    }

    handleVisitTabChange(e, newValue) {
        this.setState({ visitTabsValue: newValue })
    }

    handleFeedbackClose(event, reason) {
        this.setState({ feedbackOpen: false });
    }

    render() {
        let canReadSelfVisitLaser = canReadSelfVisit('laser', store);
        let canReadSelfVisitRegular = canReadSelfVisit('regular', store);
        let canReadVisitsLaser = canReadVisits('laser', store);
        let canReadVisitsRegular = canReadVisits('regular', store);
        return (
            <Grid container spacing={1} sx={{ minHeight: '100vh' }} alignContent='flex-start' >
                <Grid item xs={12} >
                    <Header title={<Link to='/' style={{ textDecoration: 'none', color: 'white' }} >{translate('pages/visits/visit/visit/plural/ucFirstLetterFirstWord')}</ Link>} />
                </Grid>
                <Grid item xs={12} style={{ minHeight: '70vh' }} >
                    <Tabs value={this.state.visitPageTabsValue} onChange={this.handleVisitPageTabChange} variant="scrollable" scrollButtons={true} allowScrollButtonsMobile sx={{ borderBottom: 1, borderColor: 'divider' }}>
                        {(canReadSelfVisitLaser || canReadSelfVisitRegular) &&
                            <Tab label={translate('pages/visits/visit/your-visit')} />
                        }
                        {(canReadVisitsLaser || canReadVisitsRegular) &&
                            <Tab label={translate('pages/visits/visit/others-visit')} />
                        }
                    </Tabs>
                    {(canReadSelfVisitLaser || canReadSelfVisitRegular) &&
                        <TabPanel value={this.state.visitPageTabsValue} index={0} style={{ height: '100%' }} >
                            <Tabs value={this.state.selfVisitTabsValue} onChange={this.handleSelfVisitTabChange} variant="scrollable" scrollButtons={true} allowScrollButtonsMobile sx={{ borderBottom: 1, borderColor: 'divider' }}>
                                {canReadSelfVisitLaser &&
                                    <Tab label={translate('pages/visits/visit/laser-visit')} />
                                }
                                {canReadSelfVisitRegular &&
                                    <Tab label={translate('pages/visits/visit/regular-visit')} />
                                }
                            </Tabs>
                            {canReadSelfVisitLaser &&
                                <TabPanel value={this.state.selfVisitTabsValue} index={0} style={{ height: '100%' }} >
                                    <SelfVisitsDataGrid businessName='laser' />
                                </TabPanel>
                            }
                            {canReadSelfVisitRegular &&
                                <TabPanel value={this.state.selfVisitTabsValue} index={1} style={{ height: '100%' }} >
                                    <SelfVisitsDataGrid businessName='regular' />
                                </TabPanel>
                            }
                        </TabPanel>
                    }
                    {(canReadVisitsLaser || canReadVisitsRegular) &&
                        <TabPanel value={this.state.visitPageTabsValue} index={1} style={{ height: '100%' }} >
                            <Tabs value={this.state.visitTabsValue} onChange={this.handleVisitTabChange} variant="scrollable" scrollButtons={true} allowScrollButtonsMobile sx={{ borderBottom: 1, borderColor: 'divider' }}>
                                {canReadVisitsLaser &&
                                    <Tab label={translate('pages/visits/visit/laser-visit')} />
                                }
                                {canReadVisitsRegular &&
                                    <Tab label={translate('pages/visits/visit/regular-visit')} />
                                }
                            </Tabs>
                            {canReadVisitsLaser &&
                                <TabPanel value={this.state.visitTabsValue} index={0} style={{ height: '100%' }} >
                                    <VisitsServerDataGrid businessName='laser' />
                                </TabPanel>
                            }
                            {canReadVisitsRegular &&
                                <TabPanel value={this.state.visitTabsValue} index={1} style={{ height: '100%' }} >
                                    <VisitsServerDataGrid businessName='regular' />
                                </TabPanel>
                            }
                        </TabPanel>
                    }

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
                </Grid>
                <Grid item xs={12} sx={{ mb: 0 }}>
                    {/* <Footer /> */}
                </Grid>
            </Grid>
        )
    }
}

export default connect(null)(DashboardVisitPage)
