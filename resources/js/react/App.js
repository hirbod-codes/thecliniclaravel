import React, { Component } from 'react';
import { BrowserRouter, Route, Routes } from 'react-router-dom';

import '../../css/app.css';

import '@fontsource/roboto/300.css';
import '@fontsource/roboto/400.css';
import '@fontsource/roboto/500.css';
import '@fontsource/roboto/700.css';

import CloseIcon from '@mui/icons-material/Close';
import { createTheme } from '@mui/material/styles';
import { ThemeProvider } from '@emotion/react';
import { Alert, GlobalStyles, IconButton, Snackbar } from '@mui/material';
import rtlPlugin from "stylis-plugin-rtl";
import { CacheProvider } from "@emotion/react";
import createCache from "@emotion/cache";
import { prefixer } from "stylis";


import { ThemeContext, resolveTheme, resolveLocalization } from './components/themeContenxt.js';
import { LocaleContext } from './components/localeContext.js';

import WelcomePage from "./components/pages/WelcomePage.js";
import LogInPage from "./components/pages/auth/LogInPage.js";
import SignUpPage from "./components/pages/auth/SignUpPage.js";
import DashboardAccountPage from './components/pages/dashboard/DashboardAccountPage';

import { fetchData } from './components/Http/fetch';
import DashboardOrderPage from './components/pages/dashboard/DashboardOrderPage';
import DashboardVisitPage from './components/pages/dashboard/DashboardVisitPage';
import UserIconNavigator from './components/UserIconNavigator';
import { updateState } from './components/helpers';
import { formatPrivileges, PrivilegesContext } from './components/privilegesContext';

class App extends Component {
    constructor(props) {
        super(props);

        this.getDataSynchronously = this.getDataSynchronously.bind(this);

        this.handleFeedbackClose = this.handleFeedbackClose.bind(this);

        this.onLogout = this.onLogout.bind(this);
        this.onLogin = this.onLogin.bind(this);
        this.onRegister = this.onRegister.bind(this);
        this.resetAuthInfo = this.resetAuthInfo.bind(this);

        this.changeLocale = async (name) => {
            let r = await fetchData('put', '/locale', { 'locale': name }, { 'X-CSRF-TOKEN': this.state.token, 'Content-type': '*/*' });
            if (r.response.status === 200) {
                document.location.reload();
            }
        };

        this.changeTheme = async (name) => {
            let r = await fetchData('post', '/theme', { theme: name }, { 'X-CSRF-TOKEN': this.state.token });
            if (r.response.status === 200) {
                document.location.reload();
            }
        };

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            feedbackMessages: [],

            finishStatus: {
                locales: false,
                locale: false,
                themeData: false,
            },

            isAuthenticationLoading: true,
            isAuthenticated: false,
            isAccountLoading: true,
            account: null,
            arePrivilegesLoading: true,
            privileges: null,

            isEmailVerified: false,
            isAvatarLoading: true,
            image: null,

            themeContext: {
                theme: {},
                changeTheme: this.changeTheme,
                currentTheme: '',
                isThemeLoading: true,
            },
            localeContext: {
                locales: {},
                currentLocale: null,
                isLocaleLoading: true,
                changeLocale: this.changeLocale
            }
        };
    }

    shouldComponentUpdate() {
        for (const k in this.state.finishStatus) {
            if (Object.hasOwnProperty.call(this.state.finishStatus, k)) {
                const v = this.state.finishStatus[k];

                if (!v) {
                    return false;
                }
            }
        }
        return true;
    }

    componentDidMount() {
        this.getDataSynchronously();
    }

    handleFeedbackClose(event, reason, key) {
        if (reason === 'clickaway') {
            return;
        }

        let feedbackMessages = this.state.feedbackMessages;
        feedbackMessages[key].open = false;
        this.setState({ feedbackMessages: feedbackMessages });
    }

    getDataSynchronously() {
        return new Promise((resolve) => {
            fetchData('get', '/isAuthenticated', {}, { 'X-CSRF-TOKEN': this.state.token }).then((isAuthenticatedResponse) => {
                if (isAuthenticatedResponse.response.status !== 200 || isAuthenticatedResponse.value.authenticated !== true) {
                    return;
                }

                updateState(this, { isAuthenticationLoading: false, isAuthenticated: isAuthenticatedResponse.value.authenticated });

                fetchData('get', '/account', {}, { 'X-CSRF-TOKEN': this.state.token }).then((accountResponse) => {
                    updateState(this, { isAccountLoading: false });

                    if (accountResponse.response.status !== 200) {
                        return;
                    }
                    updateState(this, { account: accountResponse.value });

                    fetchData('get', '/avatar?accountId=' + accountResponse.value.id, {}, { 'X-CSRF-TOKEN': this.state.token }).then((avatarResponse) => {
                        if (avatarResponse.response.status === 200) {
                            updateState(this, { isAvatarLoading: false, image: 'data:image/png;base64,' + avatarResponse.value });
                        }
                    });
                });

                fetchData('get', '/role', {}, { 'X-CSRF-TOKEN': this.state.token }).then((privilegesResponse) => {
                    if (privilegesResponse.response.status === 200) {
                        updateState(this, { privileges: privilegesResponse.value, arePrivilegesLoading: false });
                    }
                    updateState(this, { arePrivilegesLoading: false });
                });

                fetchData('get', '/isEmailVerified', {}, { 'X-CSRF-TOKEN': this.state.token }).then((emailResponse) => {
                    if (emailResponse.response.status === 200) {
                        updateState(this, { isEmailVerified: emailResponse.value });
                    }
                });
            });

            fetchData('get', '/locale', {}, { 'X-CSRF-TOKEN': this.state.token }).then((localeResponse) => {
                let locale = localeResponse.value;
                if (localeResponse.response.status !== 200) {
                    return;
                }
                updateState(this, (state) => {
                    state.finishStatus.locale = true;
                    state.localeContext.currentLocale = locale;
                    state.localeContext.isLocaleLoading = false;
                    return state;
                });

                document.dir = locale.direction;
                document.body.setAttribute('dir', locale.direction);

                fetchData('get', '/theme', {}, { 'X-CSRF-TOKEN': this.state.token }).then((themeResponse) => {
                    let themeData = themeResponse.value;
                    if (themeResponse.response.status === 200) {
                        updateState(this, (state) => {
                            state.finishStatus.themeData = true;
                            state.themeContext.theme = createTheme(resolveTheme(themeData.theme + '-' + locale.direction), resolveLocalization(locale.shortName));
                            state.themeContext.currentTheme = themeData.theme;
                            state.themeContext.isThemeLoading = false;
                            return state;
                        });
                    }
                });
            });

            fetchData('get', '/locales', {}, { 'X-CSRF-TOKEN': this.state.token }).then((localesResponse) => {
                let locales = localesResponse.value;
                if (localesResponse.response.status === 200) {
                    updateState(this, (state) => {
                        state.finishStatus.locales = true;
                        state.localeContext.locales = locales;
                        return state;
                    });
                }
            });

            resolve();
        });
    }

    onLogout() {
        this.resetAuthInfo()
    }

    resetAuthInfo() {
        this.setState({
            isAuthenticated: false,
            account: null,
            privileges: null,
            isEmailVerified: null,
            image: null,
        });
    }

    async onLogin() {
        this.resetAuthInfo();
        await this.getDataSynchronously();
    }

    async onRegister() {
        this.resetAuthInfo();
        await this.getDataSynchronously();
    }

    render() {
        const inputGlobalStyles = <GlobalStyles styles={theme => ({
            body: {
                overflowX: 'hidden'
            },
            '*::-webkit-scrollbar': {
                width: '0.4em',
            },
            '*::-webkit-scrollbar-track': {
                webkitBoxShadow: 'inset 0 0 6px rgba(0,0,0,0.00)',
            },
            '*::-webkit-scrollbar-thumb': {
                backgroundColor: theme.palette.primary.main,
                outline: '1px solid slategrey',
            }
        })} />;

        let authProps = {
            isAuthenticationLoading: this.state.isAuthenticationLoading,
            isAuthenticated: this.state.isAuthenticated,
            onLogout: this.onLogout,
            account: this.state.account,
            privileges: this.state.privileges,
        };

        let navigator = null;
        if (!this.state.localeContext.isLocaleLoading && !this.state.themeContext.isThemeLoading) {
            navigator = <UserIconNavigator image={this.state.image} isAvatarLoading={this.state.isAvatarLoading} isEmailVerified={this.state.isEmailVerified} />;
        }

        const cacheLtr = createCache({ key: "muiltr" });

        const cacheRtl = createCache({ key: "muirtl", stylisPlugins: [prefixer, rtlPlugin] });

        console.log(this.state);
        return (
            (this.state.localeContext.isLocaleLoading || this.state.themeContext.isThemeLoading || (this.state.isAuthenticated && (this.state.isAccountLoading || this.state.arePrivilegesLoading))) ?
                <>
                    {inputGlobalStyles}
                    < div >!!!</div >
                </> :
                <CacheProvider value={this.state.localeContext.currentLocale.direction === 'rtl' ? cacheRtl : cacheLtr}>
                    <LocaleContext.Provider value={this.state.localeContext}>
                        <ThemeContext.Provider value={this.state.themeContext}>
                            <ThemeProvider theme={this.state.themeContext.theme}>
                                <PrivilegesContext.Provider value={formatPrivileges(this.state.privileges)}>
                                    {inputGlobalStyles}
                                    <BrowserRouter>
                                        <Routes>
                                            <Route path='/' element={<WelcomePage navigator={navigator} {...authProps} />} />
                                            <Route path='/login' element={<LogInPage navigator={navigator} {...authProps} onLogin={this.onLogin} />} />
                                            <Route path='/register' element={<SignUpPage navigator={navigator} {...authProps} onRegister={this.onRegister} />} />

                                            <Route path='/dashboard/account' element={<DashboardAccountPage navigator={navigator} {...authProps} />} />
                                            <Route path='/dashboard/order' element={<DashboardOrderPage navigator={navigator} {...authProps} />} />
                                            <Route path='/dashboard/visit' element={<DashboardVisitPage navigator={navigator} {...authProps} />} />
                                        </Routes>
                                    </BrowserRouter>

                                    {this.state.feedbackMessages.map((m, i) =>
                                        <Snackbar
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
                                        </Snackbar>
                                    )}
                                </PrivilegesContext.Provider>
                            </ThemeProvider>
                        </ThemeContext.Provider>
                    </LocaleContext.Provider>
                </CacheProvider>
        );
    }
}

export default App
