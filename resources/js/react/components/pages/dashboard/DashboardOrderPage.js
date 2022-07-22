import React, { Component } from 'react'
import { Link } from 'react-router-dom'

import CloseIcon from '@mui/icons-material/Close';
import { Alert, Grid, IconButton, Snackbar, Tab, Tabs } from '@mui/material'
import LoadingButton from '@mui/lab/LoadingButton'

import Header from '../../headers/Header'
import { translate } from '../../../traslation/translate'
import TabPanel from '../../Menus/TabPanel'
import SelfRegularOrdersDataGrid from '../../Grids/Orders/SelfRegularOrdersDataGrid'
import SelfLaserOrdersDataGrid from '../../Grids/Orders/SelfLaserOrdersDataGrid'
import LaserOrdersServerDataGrid from '../../Grids/Orders/LaserOrdersServerDataGrid';
import RegularOrdersServerDataGrid from '../../Grids/Orders/RegularOrdersServerDataGrid';

export class DashboardOrderPage extends Component {
    constructor(props) {
        super(props);

        this.handleFeedbackClose = this.handleFeedbackClose.bind(this);

        this.handleOrderPageTabChange = this.handleOrderPageTabChange.bind(this);
        this.handleSelfOrderTabChange = this.handleSelfOrderTabChange.bind(this);
        this.handleOrderTabChange = this.handleOrderTabChange.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            orderPageTabsValue: 0,
            selfOrderTabsValue: 0,
            orderTabsValue: 0,

            feedbackOpen: false,
            feedbackMessage: '',
            feedbackColor: 'info',
        };
    }

    handleOrderPageTabChange(e, newValue) {
        this.setState({ orderPageTabsValue: newValue })
    }

    handleSelfOrderTabChange(e, newValue) {
        this.setState({ selfOrderTabsValue: newValue })
    }

    handleOrderTabChange(e, newValue) {
        this.setState({ orderTabsValue: newValue })
    }

    handleFeedbackClose(event, reason) {
        this.setState({ feedbackOpen: false });
    }

    render() {
        return (
            <Grid container spacing={1} sx={{ minHeight: '100vh', }} alignContent='flex-start' >
                <Grid item xs={12} >
                    <Header
                        title={<Link to='/' style={{ textDecoration: 'none', color: 'white' }} >{translate('pages/orders/order/order/plural/ucFirstLetterFirstWord', this.props.currentLocaleName)}</ Link>}
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
                            <Tabs value={this.state.orderPageTabsValue} onChange={this.handleOrderPageTabChange} variant="scrollable" scrollButtons={true} allowScrollButtonsMobile sx={{ borderBottom: 1, borderColor: 'divider' }}>
                                {(this.props.privileges.selfLaserOrdersRead || this.props.privileges.selfRegularOrdersRead) &&
                                    <Tab label={translate('pages/orders/order/your-orders', this.props.currentLocaleName)} />
                                }
                                {(this.props.privileges.laserOrdersRead || this.props.privileges.regularOrdersRead) &&
                                    <Tab label={translate('pages/orders/order/others-orders', this.props.currentLocaleName)} />
                                }
                            </Tabs>
                            {(this.props.privileges.selfLaserOrdersRead || this.props.privileges.selfRegularOrdersRead) &&
                                <TabPanel value={this.state.orderPageTabsValue} index={0} style={{ height: '100%' }} >
                                    <Tabs value={this.state.selfOrderTabsValue} onChange={this.handleSelfOrderTabChange} variant="scrollable" scrollButtons={true} allowScrollButtonsMobile sx={{ borderBottom: 1, borderColor: 'divider' }}>
                                        {(this.props.privileges.selfLaserOrdersRead) &&
                                            <Tab label={translate('pages/orders/order/laser-orders', this.props.currentLocaleName)} />
                                        }
                                        {(this.props.privileges.selfRegularOrdersRead) &&
                                            <Tab label={translate('pages/orders/order/regular-orders', this.props.currentLocaleName)} />
                                        }
                                    </Tabs>
                                    {(this.props.privileges.selfLaserOrdersRead) &&
                                        <TabPanel value={this.state.selfOrderTabsValue} index={0} style={{ height: '100%' }} >
                                            <SelfLaserOrdersDataGrid account={this.props.account} privileges={this.props.privileges} currentLocaleName={this.props.currentLocaleName} />
                                        </TabPanel>
                                    }
                                    {(this.props.privileges.selfRegularOrdersRead) &&
                                        <TabPanel value={this.state.selfOrderTabsValue} index={1} style={{ height: '100%' }} >
                                            <SelfRegularOrdersDataGrid account={this.props.account} privileges={this.props.privileges} currentLocaleName={this.props.currentLocaleName} />
                                        </TabPanel>
                                    }
                                </TabPanel>
                            }
                            {(this.props.privileges.laserOrdersRead || this.props.privileges.regularOrdersRead) &&
                                <TabPanel value={this.state.orderPageTabsValue} index={1} style={{ height: '100%' }} >
                                    <Tabs value={this.state.orderTabsValue} onChange={this.handleOrderTabChange} variant="scrollable" scrollButtons={true} allowScrollButtonsMobile sx={{ borderBottom: 1, borderColor: 'divider' }}>
                                        {(this.props.privileges.laserOrdersRead) &&
                                            <Tab label={translate('pages/orders/order/laser-orders', this.props.currentLocaleName)} />
                                        }
                                        {(this.props.privileges.regularOrdersRead) &&
                                            <Tab label={translate('pages/orders/order/regular-orders', this.props.currentLocaleName)} />
                                        }
                                    </Tabs>
                                    {(this.props.privileges.laserOrdersRead) &&
                                        <TabPanel value={this.state.orderTabsValue} index={0} style={{ height: '100%' }} >
                                            <LaserOrdersServerDataGrid privileges={this.props.privileges} currentLocaleName={this.props.currentLocaleName} />
                                        </TabPanel>
                                    }
                                    {(this.props.privileges.regularOrdersRead) &&
                                        <TabPanel value={this.state.orderTabsValue} index={1} style={{ height: '100%' }} >
                                            <RegularOrdersServerDataGrid privileges={this.props.privileges} currentLocaleName={this.props.currentLocaleName} />
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

export default DashboardOrderPage
