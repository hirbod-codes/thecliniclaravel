import React, { Component } from 'react'
import { Link } from 'react-router-dom'

import CloseIcon from '@mui/icons-material/Close';
import { Alert, Grid, IconButton, Snackbar, Tab, Tabs } from '@mui/material'

import Header from '../../headers/Header'
import { translate } from '../../../traslation/translate'
import TabPanel from '../../Menus/TabPanel'
import Account from '../../Menus/Account/Account';
import AccountsServerDataGrid from '../../Grids/Accounts/AccountsServerDataGrid';
import { PrivilegesContext } from '../../privilegesContext';

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
        this.setState({ accountPageTabsValue: newValue });
    }

    handleFeedbackClose(event, reason) {
        this.setState({ feedbackOpen: false });
    }

    render() {
        return (
            <PrivilegesContext.Consumer >
                {(p) =>
                    <Grid container spacing={1} sx={{ minHeight: '100vh', }} alignContent='flex-start' >
                        <Grid item xs={12} >
                            <Header
                                title={<Link to='/' style={{ textDecoration: 'none', color: 'white' }} >{translate('pages/account/account/account/plural/ucFirstLetterFirstWord')}</ Link>}
                                onLogout={this.props.onLogout}
                                isAuthenticated={this.props.isAuthenticated}
                                isAuthenticationLoading={this.props.isAuthenticationLoading}
                                navigator={this.props.navigator}
                            />
                        </Grid>
                        <Grid item xs={12} style={{ minHeight: '70vh' }} >
                            <Tabs value={this.state.accountPageTabsValue} onChange={this.handleAccountPageTabChange} variant="scrollable" scrollButtons={true} allowScrollButtonsMobile sx={{ borderBottom: 1, borderColor: 'divider' }}>
                                {p.retrieveUser.indexOf('self') !== -1 &&
                                    <Tab label={translate('pages/account/account/your-account')} />
                                }
                                {((p.retrieveUser.length > 1) || (p.retrieveUser.length === 1 && p.retrieveUser[0] !== 'self')) &&
                                    <Tab label={translate('pages/account/account/others-accounts')} />
                                }
                            </Tabs>
                            {p.retrieveUser.indexOf('self') !== -1 &&
                                <TabPanel value={this.state.accountPageTabsValue} index={0} style={{ height: '100%' }} >
                                    <Account isSelf={true} account={this.props.account} accountRole={p.role} />
                                </TabPanel>
                            }
                            {((p.retrieveUser.length > 1) || (p.retrieveUser.length === 1 && p.retrieveUser[0] !== 'self')) &&
                                <TabPanel value={this.state.accountPageTabsValue} index={1} style={{ height: '100%' }} >
                                    <AccountsServerDataGrid roles={p.retrieveUser.map((v) => { return v !== 'self' ? v : p.role; })} account={this.props.account} />
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
                }
            </PrivilegesContext.Consumer>
        );
    }
}

export default DashboardAccountPage
