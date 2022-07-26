import React, { Component } from 'react'
import { Link } from 'react-router-dom'

import CloseIcon from '@mui/icons-material/Close';
import { Alert, Grid, IconButton, Snackbar, Tab, Tabs } from '@mui/material'
import LoadingButton from '@mui/lab/LoadingButton'

import Header from '../../headers/Header'
import { translate } from '../../../traslation/translate'
import TabPanel from '../../Menus/TabPanel'
import Account from '../../Menus/Account/Account';
import AccountsServerDataGrid from '../../Grids/Accounts/AccountsServerDataGrid';

export class DashboardAccountPage extends Component {
    constructor(props) {
        super(props);

        this.handleFeedbackClose = this.handleFeedbackClose.bind(this);

        this.handleAccountPageTabChange = this.handleAccountPageTabChange.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            feedbackOpen: false,
            feedbackMessage: '',
            feedbackColor: 'info',

            accountPageTabsValue: 0,
        };
    }

    handleAccountPageTabChange(e, newValue) {
        this.setState({ accountPageTabsValue: newValue })
    }

    handleFeedbackClose(event, reason) {
        this.setState({ feedbackOpen: false });
    }

    render() {
        return (
            <Grid container spacing={1} sx={{ minHeight: '100vh', }} alignContent='flex-start' >
                <Grid item xs={12} >
                    <Header
                        title={<Link to='/' style={{ textDecoration: 'none', color: 'white' }} >{translate('pages/account/account/account/plural/ucFirstLetterFirstWord')}</ Link>}
                        isAuthenticated={this.props.isAuthenticated}
                        isAuthenticationLoading={this.props.isAuthenticationLoading}
                        navigator={this.props.navigator}
                    />
                </Grid>
                <Grid item xs={12} style={{ minHeight: '70vh' }} >
                    {!this.props.privileges
                        ? <LoadingButton loading variant='contained' fullWidth></LoadingButton>
                        : <>
                            <Tabs value={this.state.accountPageTabsValue} onChange={this.handleAccountPageTabChange} variant="scrollable" scrollButtons={true} allowScrollButtonsMobile sx={{ borderBottom: 1, borderColor: 'divider' }}>
                                {(this.props.privileges.selfAccountRead) &&
                                    <Tab label={translate('pages/account/account/your-account')} />
                                }
                                {(this.props.privileges.accountRead) &&
                                    <Tab label={translate('pages/account/account/others-accounts')} />
                                }
                            </Tabs>
                            {(this.props.privileges.selfAccountRead) &&
                                <TabPanel value={this.state.accountPageTabsValue} index={0} style={{ height: '100%' }} >
                                    <Account isSelf={true} account={this.props.account} privileges={this.props.privileges} />
                                </TabPanel>
                            }
                            {(this.props.privileges.accountRead) &&
                                <TabPanel value={this.state.accountPageTabsValue} index={1} style={{ height: '100%' }} >
                                    <AccountsServerDataGrid account={this.props.account} privileges={this.props.privileges} />
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
        );
    }
}

export default DashboardAccountPage
