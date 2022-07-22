import React, { Component } from 'react'
import { Link } from 'react-router-dom';

import CloseIcon from '@mui/icons-material/Close';
import { Alert, Grid, IconButton, Snackbar, Tab, Tabs } from '@mui/material';
import LoadingButton from '@mui/lab/LoadingButton';

import { translate } from '../../../traslation/translate';
import SelfVisitsDataGrid from '../../Grids/Visits/SelfVisitsDataGrid';
import Header from '../../headers/Header';
import TabPanel from '../../Menus/TabPanel';
import VisitsServerDataGrid from '../../Grids/Visits/VisitsServerDataGrid';

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
        return (
            <Grid container spacing={1} sx={{ minHeight: '100vh' }} alignContent='flex-start' >
                <Grid item xs={12} >
                    <Header
                        title={<Link to='/' style={{ textDecoration: 'none', color: 'white' }} >{translate('pages/visits/visit/visit/plural/ucFirstLetterFirstWord', this.props.currentLocaleName)}</ Link>}
                        isAuthenticated={this.props.isAuthenticated}
                        isAuthenticationLoading={this.props.isAuthenticationLoading}
                        navigator={this.props.navigator}
                        currentLocaleName={this.props.currentLocaleName}
                    />
                </Grid>
                <Grid item xs={12} style={{ minHeight: '70vh' }} >
                    {!this.props.privileges
                        ? <LoadingButton loading variant='contained' fullWidth></LoadingButton>
                        : <>
                            <Tabs value={this.state.visitPageTabsValue} onChange={this.handleVisitPageTabChange} variant="scrollable" scrollButtons={true} allowScrollButtonsMobile sx={{ borderBottom: 1, borderColor: 'divider' }}>
                                {(this.props.privileges.selfLaserVisitRetrieve || this.props.privileges.selfRegularVisitRetrieve) &&
                                    <Tab label={translate('pages/visits/visit/your-visit', this.props.currentLocaleName)} />
                                }
                                {(this.props.privileges.laserVisitRetrieve || this.props.privileges.regularVisitRetrieve) &&
                                    <Tab label={translate('pages/visits/visit/others-visit', this.props.currentLocaleName)} />
                                }
                            </Tabs>
                            {(this.props.privileges.selfLaserVisitRetrieve || this.props.privileges.selfRegularVisitRetrieve) &&
                                <TabPanel value={this.state.visitPageTabsValue} index={0} style={{ height: '100%' }} >
                                    <Tabs value={this.state.selfVisitTabsValue} onChange={this.handleSelfVisitTabChange} variant="scrollable" scrollButtons={true} allowScrollButtonsMobile sx={{ borderBottom: 1, borderColor: 'divider' }}>
                                        {(this.props.privileges.selfLaserVisitRetrieve) &&
                                            <Tab label={translate('pages/visits/visit/laser-visit', this.props.currentLocaleName)} />
                                        }
                                        {(this.props.privileges.selfRegularVisitRetrieve) &&
                                            <Tab label={translate('pages/visits/visit/regular-visit', this.props.currentLocaleName)} />
                                        }
                                    </Tabs>
                                    {(this.props.privileges.selfLaserVisitRetrieve) &&
                                        <TabPanel value={this.state.selfVisitTabsValue} index={0} style={{ height: '100%' }} >
                                            <SelfVisitsDataGrid businessName='laser' account={this.props.account} privileges={this.props.privileges} currentLocaleName={this.props.currentLocaleName} />
                                        </TabPanel>
                                    }
                                    {(this.props.privileges.selfRegularVisitRetrieve) &&
                                        <TabPanel value={this.state.selfVisitTabsValue} index={1} style={{ height: '100%' }} >
                                            <SelfVisitsDataGrid businessName='regular' account={this.props.account} privileges={this.props.privileges} currentLocaleName={this.props.currentLocaleName} />
                                        </TabPanel>
                                    }
                                </TabPanel>
                            }
                            {(this.props.privileges.laserVisitRetrieve || this.props.privileges.regularVisitRetrieve) &&
                                <TabPanel value={this.state.visitPageTabsValue} index={1} style={{ height: '100%' }} >
                                    <Tabs value={this.state.visitTabsValue} onChange={this.handleVisitTabChange} variant="scrollable" scrollButtons={true} allowScrollButtonsMobile sx={{ borderBottom: 1, borderColor: 'divider' }}>
                                        {(this.props.privileges.laserVisitRetrieve) &&
                                            <Tab label={translate('pages/visits/visit/laser-visit', this.props.currentLocaleName)} />
                                        }
                                        {(this.props.privileges.regularVisitRetrieve) &&
                                            <Tab label={translate('pages/visits/visit/regular-visit', this.props.currentLocaleName)} />
                                        }
                                    </Tabs>
                                    {(this.props.privileges.laserVisitRetrieve) &&
                                        <TabPanel value={this.state.visitTabsValue} index={0} style={{ height: '100%' }} >
                                            <VisitsServerDataGrid businessName='laser' privileges={this.props.privileges} currentLocaleName={this.props.currentLocaleName} />
                                        </TabPanel>
                                    }
                                    {(this.props.privileges.regularVisitRetrieve) &&
                                        <TabPanel value={this.state.visitTabsValue} index={1} style={{ height: '100%' }} >
                                            <VisitsServerDataGrid businessName='regular' privileges={this.props.privileges} currentLocaleName={this.props.currentLocaleName} />
                                        </TabPanel>
                                    }
                                </TabPanel>
                            }
                        </>
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
