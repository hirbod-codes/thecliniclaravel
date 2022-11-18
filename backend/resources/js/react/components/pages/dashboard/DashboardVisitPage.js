import React, { Component } from 'react'
import { Link } from 'react-router-dom';

import CloseIcon from '@mui/icons-material/Close';
import { Alert, Grid, IconButton, Snackbar, Tab, Tabs } from '@mui/material';

import { translate } from '../../../traslation/translate';
import SelfVisitsDataGrid from '../../Grids/Visits/SelfVisitsDataGrid';
import Header from '../../headers/Header';
import TabPanel from '../../Menus/TabPanel';
import VisitsServerDataGrid from '../../Grids/Visits/VisitsServerDataGrid';
import { PrivilegesContext } from '../../privilegesContext';

export class DashboardVisitPage extends Component {
    static contextType = PrivilegesContext;

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
        let retrieveVisit = this.context.retrieveVisit;
        let retrieveVisitKeys = Object.keys(retrieveVisit ? retrieveVisit : {});
        let canRetrieveSelfVisit, canRetrieveVisit = false;
        if (retrieveVisit && retrieveVisitKeys.length > 0) {
            retrieveVisitKeys.some((k) => {
                let r = retrieveVisit[k].some((v) => {
                    if (v === 'self') {
                        canRetrieveSelfVisit = true;
                    } else {
                        canRetrieveVisit = true;
                    }

                    if (canRetrieveSelfVisit === true && canRetrieveVisit === true) { return true; }
                    return false;
                });

                if (r === true) { return true; }
                return false;
            });
        }
        return (
            <Grid container spacing={1} sx={{ minHeight: '100vh' }} alignContent='flex-start' >
                <Grid item xs={12} >
                    <Header
                        title={<Link to='/' style={{ textDecoration: 'none', color: 'white' }} >{translate('pages/visits/visit/visit/plural/ucFirstLetterFirstWord')}</ Link>}
                        onLogout={this.props.onLogout}
                        isAuthenticated={this.props.isAuthenticated}
                        isAuthenticationLoading={this.props.isAuthenticationLoading}
                        navigator={this.props.navigator}
                    />
                </Grid>
                <Grid item xs={12} style={{ minHeight: '70vh' }} >
                    <Tabs value={this.state.visitPageTabsValue} onChange={this.handleVisitPageTabChange} variant="scrollable" scrollButtons={true} allowScrollButtonsMobile sx={{ borderBottom: 1, borderColor: 'divider' }}>
                        {canRetrieveSelfVisit &&
                            <Tab label={translate('pages/visits/visit/your-visit')} />
                        }
                        {canRetrieveVisit &&
                            <Tab label={translate('pages/visits/visit/others-visit')} />
                        }
                    </Tabs>
                    {canRetrieveSelfVisit &&
                        <TabPanel value={this.state.visitPageTabsValue} index={0} style={{ height: '100%' }} >
                            <Tabs value={this.state.selfVisitTabsValue} onChange={this.handleSelfVisitTabChange} variant="scrollable" scrollButtons={true} allowScrollButtonsMobile sx={{ borderBottom: 1, borderColor: 'divider' }}>
                                {retrieveVisit.laser.indexOf('self') !== -1 &&
                                    <Tab label={translate('pages/visits/visit/laser-visit')} />
                                }
                                {retrieveVisit.regular.indexOf('self') !== -1 &&
                                    <Tab label={translate('pages/visits/visit/regular-visit')} />
                                }
                            </Tabs>
                            {retrieveVisit.laser.indexOf('self') !== -1 &&
                                <TabPanel value={this.state.selfVisitTabsValue} index={0} style={{ height: '100%' }} >
                                    <SelfVisitsDataGrid businessName='laser' account={this.props.account} />
                                </TabPanel>
                            }
                            {retrieveVisit.regular.indexOf('self') !== -1 &&
                                <TabPanel value={this.state.selfVisitTabsValue} index={1} style={{ height: '100%' }} >
                                    <SelfVisitsDataGrid businessName='regular' account={this.props.account} />
                                </TabPanel>
                            }
                        </TabPanel>
                    }
                    {canRetrieveVisit &&
                        <TabPanel value={this.state.visitPageTabsValue} index={1} style={{ height: '100%' }} >
                            <Tabs value={this.state.visitTabsValue} onChange={this.handleVisitTabChange} variant="scrollable" scrollButtons={true} allowScrollButtonsMobile sx={{ borderBottom: 1, borderColor: 'divider' }}>
                                {((retrieveVisit.laser.indexOf('self') === -1 && retrieveVisit.laser.length > 0) || (retrieveVisit.laser.indexOf('self') !== -1 && retrieveVisit.laser.length > 1)) &&
                                    <Tab label={translate('pages/visits/visit/laser-visit')} />
                                }
                                {((retrieveVisit.regular.indexOf('self') === -1 && retrieveVisit.regular.length > 0) || (retrieveVisit.regular.indexOf('self') !== -1 && retrieveVisit.regular.length > 1)) &&
                                    <Tab label={translate('pages/visits/visit/regular-visit')} />
                                }
                            </Tabs>
                            {((retrieveVisit.laser.indexOf('self') === -1 && retrieveVisit.laser.length > 0) || (retrieveVisit.laser.indexOf('self') !== -1 && retrieveVisit.laser.length > 1)) &&
                                <TabPanel value={this.state.visitTabsValue} index={0} style={{ height: '100%' }} >
                                    <VisitsServerDataGrid businessName='laser' />
                                </TabPanel>
                            }
                            {((retrieveVisit.regular.indexOf('self') === -1 && retrieveVisit.regular.length > 0) || (retrieveVisit.regular.indexOf('self') !== -1 && retrieveVisit.regular.length > 1)) &&
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

export default DashboardVisitPage
