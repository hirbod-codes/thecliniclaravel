import React, { Component } from 'react'
import { Link } from 'react-router-dom'

import CloseIcon from '@mui/icons-material/Close';
import { Alert, Grid, IconButton, Snackbar, Tab, Tabs } from '@mui/material'

import Header from '../../headers/Header'
import { translate } from '../../../traslation/translate'
import TabPanel from '../../Menus/TabPanel'
import SelfRegularOrdersDataGrid from '../../Grids/Orders/SelfRegularOrdersDataGrid'
import SelfLaserOrdersDataGrid from '../../Grids/Orders/SelfLaserOrdersDataGrid'
import LaserOrdersServerDataGrid from '../../Grids/Orders/LaserOrdersServerDataGrid';
import RegularOrdersServerDataGrid from '../../Grids/Orders/RegularOrdersServerDataGrid';
import { canReadOrders, canReadSelfOrder } from '../../roles/order';
import store from '../../../../redux/store';
import { connect } from 'react-redux';

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
                    <Header title={<Link to='/' style={{ textDecoration: 'none', color: 'white' }} >{translate('pages/orders/order/order/plural/ucFirstLetterFirstWord')}</ Link>} />
                </Grid>
                <Grid item xs={12} style={{ minHeight: '70vh' }} >
                    <Tabs value={this.state.orderPageTabsValue} onChange={this.handleOrderPageTabChange} variant="scrollable" scrollButtons={true} allowScrollButtonsMobile sx={{ borderBottom: 1, borderColor: 'divider' }}>
                        {(canReadSelfOrder('laser', store) === true || canReadSelfOrder('regular', store) === true) &&
                            <Tab label={translate('pages/orders/order/your-orders')} />
                        }
                        {(canReadOrders('laser', store) === true || canReadOrders('regular', store) === true) &&
                            <Tab label={translate('pages/orders/order/others-orders')} />
                        }
                    </Tabs>
                    {(canReadSelfOrder('laser', store) === true || canReadSelfOrder('regular', store) === true) &&
                        <TabPanel value={this.state.orderPageTabsValue} index={0} style={{ height: '100%' }} >
                            <Tabs value={this.state.selfOrderTabsValue} onChange={this.handleSelfOrderTabChange} variant="scrollable" scrollButtons={true} allowScrollButtonsMobile sx={{ borderBottom: 1, borderColor: 'divider' }}>
                                {(canReadSelfOrder('laser', store) === true) &&
                                    <Tab label={translate('pages/orders/order/laser-orders')} />
                                }
                                {(canReadSelfOrder('regular', store) === true) &&
                                    <Tab label={translate('pages/orders/order/regular-orders')} />
                                }
                            </Tabs>
                            {(canReadSelfOrder('laser', store) === true) &&
                                <TabPanel value={this.state.selfOrderTabsValue} index={0} style={{ height: '100%' }} >
                                    <SelfLaserOrdersDataGrid />
                                </TabPanel>
                            }
                            {(canReadSelfOrder('regular', store) === true) &&
                                <TabPanel value={this.state.selfOrderTabsValue} index={1} style={{ height: '100%' }} >
                                    <SelfRegularOrdersDataGrid />
                                </TabPanel>
                            }
                        </TabPanel>
                    }
                    {(canReadOrders('laser', store) === true || canReadOrders('regular', store) === true) &&
                        <TabPanel value={this.state.orderPageTabsValue} index={1} style={{ height: '100%' }} >
                            <Tabs value={this.state.orderTabsValue} onChange={this.handleOrderTabChange} variant="scrollable" scrollButtons={true} allowScrollButtonsMobile sx={{ borderBottom: 1, borderColor: 'divider' }}>
                                {(canReadOrders('laser', store) === true) &&
                                    <Tab label={translate('pages/orders/order/laser-orders')} />
                                }
                                {(canReadOrders('regular', store) === true) &&
                                    <Tab label={translate('pages/orders/order/regular-orders')} />
                                }
                            </Tabs>
                            {(canReadOrders('laser', store) === true) &&
                                <TabPanel value={this.state.orderTabsValue} index={0} style={{ height: '100%' }} >
                                    <LaserOrdersServerDataGrid />
                                </TabPanel>
                            }
                            {(canReadOrders('regular', store) === true) &&
                                <TabPanel value={this.state.orderTabsValue} index={1} style={{ height: '100%' }} >
                                    <RegularOrdersServerDataGrid />
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

export default connect(null)(DashboardOrderPage)
