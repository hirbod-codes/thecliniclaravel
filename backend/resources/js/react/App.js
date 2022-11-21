import React, { Component } from 'react';
import { BrowserRouter, Route, Routes } from 'react-router-dom';

import '../../css/app.css';

import '@fontsource/roboto/300.css';
import '@fontsource/roboto/400.css';
import '@fontsource/roboto/500.css';
import '@fontsource/roboto/700.css';

import CloseIcon from '@mui/icons-material/Close';
import { Alert, GlobalStyles, IconButton, Snackbar } from '@mui/material';

import { prefixer } from "stylis";
import rtlPlugin from "stylis-plugin-rtl";
import createCache from "@emotion/cache";
import { CacheProvider } from "@emotion/react";

import { createTheme } from '@mui/material/styles';

import { ThemeProvider } from '@emotion/react';
import { resolveTheme, resolveLocalization } from './components/themeContenxt.js';

import WelcomePage from "./components/pages/WelcomePage.js";
import LogInPage from "./components/pages/auth/LogInPage.js";
import SignUpPage from "./components/pages/auth/SignUpPage.js";
import DashboardAccountPage from './components/pages/dashboard/DashboardAccountPage';

import { fetchData } from './components/Http/fetch';
import DashboardOrderPage from './components/pages/dashboard/DashboardOrderPage';
import DashboardVisitPage from './components/pages/dashboard/DashboardVisitPage';
import { updateState } from './components/helpers';

import store, { observeStore } from '../redux/store';
import { gotAccount, gotAvatar, isAuthenticated, isEmailVerified, isNotAuthenticated } from '../redux/reducers/auth';
import { connect } from 'react-redux';
import { gotLocal, gotLocals } from '../redux/reducers/local';
import { gotTheme } from '../redux/reducers/theme';
import { gotRoles } from '../redux/reducers/role';

class App extends Component {
    constructor(props) {
        super(props);

        this.getDataSynchronously = this.getDataSynchronously.bind(this);

        this.handleFeedbackClose = this.handleFeedbackClose.bind(this);

        this.onLocalChange = this.onLocalChange.bind(this);
        this.onThemeChange = this.onThemeChange.bind(this);
        this.onLogUserLog = this.onLogUserLog.bind(this);
        this.onLogout = this.onLogout.bind(this);
        this.onLogin = this.onLogin.bind(this);
        this.resetAuthInfo = this.resetAuthInfo.bind(this);

        this.updateAccount = this.updateAccount.bind(this);

        this.reload = this.reload.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            feedbackMessages: [],

            finishStatus: {
                isAuthenticationLoading: true,
                isAccountLoading: true,
                isAvatarLoading: true,
                isThemeLoading: true,
                isLocaleLoading: true,
                areLocalesLoading: true,
                arePrivilegesLoading: true,
            },
            forceUpdate: false,

            isAuthenticated: false,
            account: null,
            privileges: null,
            isEmailVerified: false,
            image: null,

            theme: null,
        };
    }

    async shouldComponentUpdate() {
        if (this.state.forceUpdate === true) {
            await updateState(this, { forceUpdate: false });
            return true;
        }
        return false;
    }

    async componentDidMount() {
        await this.getDataSynchronously();
        await updateState(this, { forceUpdate: true });

        setTimeout(() => {
            console.log('this.props', this.props);
            console.log('timeout redux state: ', store.getState());
        }, 3000);

        observeStore(store, state => state.theme.theme, store.getState().theme.theme, this.onThemeChange);
        observeStore(store, state => state.local.localName, store.getState().local.localName, this.onLocalChange);
        observeStore(store, state => state.auth.userLogged, store.getState().auth.userLogged, this.onLogUserLog);
        observeStore(store, state => state.auth.userLogged, store.getState().auth.userLogged, this.onLogout);
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
                updateState(this, (state) => {
                    state.finishStatus.isAuthenticationLoading = false;
                    return state;
                });

                if (isAuthenticatedResponse.response.status !== 200 || isAuthenticatedResponse.value.authenticated !== true) {
                    return;
                }

                this.props.dispatch(isAuthenticated());

                this.updateAccount();

                fetchData('get', '/role', {}, { 'X-CSRF-TOKEN': this.state.token }).then((privilegesResponse) => {
                    updateState(this, (state) => {
                        state.finishStatus.arePrivilegesLoading = false;
                        return state;
                    });

                    if (privilegesResponse.response.status === 200) {
                        this.props.dispatch(gotRoles(privilegesResponse.value));
                    }
                });

                fetchData('get', '/isEmailVerified', {}, { 'X-CSRF-TOKEN': this.state.token }).then((emailResponse) => {
                    if (emailResponse.response.status === 200) {
                        this.props.dispatch(isEmailVerified(emailResponse.value));
                    }
                });
            });

            fetchData('get', '/locale', {}, { 'X-CSRF-TOKEN': this.state.token }, [], false).then((localeResponse) => {
                updateState(this, (state) => {
                    state.finishStatus.isLocaleLoading = false;
                    return state;
                });

                let locale = localeResponse.value;
                if (localeResponse.response.status !== 200) {
                    return;
                }
                this.props.dispatch(gotLocal(locale));

                document.dir = locale.direction;
                document.body.setAttribute('dir', locale.direction);

                fetchData('get', '/theme', {}, { 'X-CSRF-TOKEN': this.state.token }, [], false).then((themeResponse) => {
                    updateState(this, (state) => {
                        state.finishStatus.isThemeLoading = false;
                        return state;
                    });

                    let themeData = themeResponse.value;
                    themeData.locale = locale;
                    if (themeResponse.response.status === 200) {
                        this.props.dispatch(gotTheme(themeData.theme));
                    }
                });
            });

            fetchData('get', '/locales', {}, { 'X-CSRF-TOKEN': this.state.token }, [], true).then((localesResponse) => {
                updateState(this, (state) => {
                    state.finishStatus.areLocalesLoading = false;
                    return state;
                });

                let locales = localesResponse.value;
                if (localesResponse.response.status === 200) {
                    this.props.dispatch(gotLocals(locales));
                }
            });

            resolve();
        });
    }

    updateAccount() {
        updateState(this, (state) => {
            state.finishStatus.isAccountLoading = true;
            state.finishStatus.isAvatarLoading = true;
            return state;
        });

        fetchData('get', '/account', {}, { 'X-CSRF-TOKEN': this.state.token }).then((accountResponse) => {
            updateState(this, (state) => {
                state.finishStatus.isAccountLoading = false;
                return state;
            });

            if (accountResponse.response.status !== 200) {
                return;
            }
            this.props.dispatch(gotAccount(accountResponse.value));

            fetchData('get', '/avatar?accountId=' + accountResponse.value.id, {}, { 'X-CSRF-TOKEN': this.state.token }).then((avatarResponse) => {
                updateState(this, (state) => {
                    state.finishStatus.isAvatarLoading = false;
                    return state;
                });

                if (avatarResponse.response.status === 200) {
                    this.props.dispatch(gotAvatar('data:image/png;base64,' + avatarResponse.value));
                } else {
                    // In case there is actually no avatar for this user we send undefined so that it doesn't stop the render method.
                    this.props.dispatch(gotAvatar(undefined));
                }
            });
        });
    }

    onThemeChange() {
        let locale = store.getState().local.local;
        let theme = store.getState().theme.theme;

        updateState(this, { theme: createTheme(resolveTheme(theme + '-' + locale.direction), resolveLocalization(locale.shortName)) });
        fetchData('post', '/theme', { theme: theme }, { 'X-CSRF-TOKEN': this.state.token, 'Content-type': 'application/json', 'Accept': 'application/json' });
    }

    async onLocalChange() {
        let locale = store.getState().local.localName;

        let r = await fetchData('put', '/locale', { 'locale': locale }, { 'X-CSRF-TOKEN': this.state.token, 'Content-type': 'application/json', 'Accept': 'application/json' });
        if (r.response.status !== 200) {
            return;
        }
        r = null;

        r = await fetchData('get', '/locale', {}, { 'X-CSRF-TOKEN': this.state.token, 'Content-type': 'application/json', 'Accept': 'application/json' });
        if (r.response.status !== 200) {
            return;
        }

        this.props.dispatch(gotLocal(r.value));

        document.dir = r.value.direction;
        document.body.setAttribute('dir', r.value.direction);

        this.setState(this.state);
    }

    onLogUserLog() {
        let userLogged = store.getState().auth.userLogged;

        if (userLogged === 'in') {
            this.onLogin();
        } else {
            if (userLogged === 'out') {
                this.onLogout();
            }
        }
    }

    onLogout() {
        this.resetAuthInfo();
    }

    onLogin() {
        this.resetAuthInfo();
        this.reload();
    }

    async reload() {
        let finishStatus = this.state.finishStatus;
        for (const k in finishStatus) {
            console.log('state k', k);
            finishStatus[k] = true;
        }

        console.log('finishStatus', finishStatus);
        await updateState(this, { finishStatus: finishStatus });
        await this.getDataSynchronously();
        await updateState(this, { forceUpdate: true });
    }

    resetAuthInfo() {
        this.props.dispatch(isNotAuthenticated());
        this.props.dispatch(gotAvatar(null));
        this.props.dispatch(gotRoles(null));
        this.props.dispatch(gotAvatar(null));
        this.props.dispatch(isEmailVerified(false));
    }

    render() {
        const inputGlobalStyles = <GlobalStyles styles={theme => ({
            body: {
                overflowX: 'hidden'
            },
            '*::-webkit-scrollbar': {
                width: '0.2em',
            },
            '*::-webkit-scrollbar-track': {
                webkitBoxShadow: 'inset 0 0 6px rgba(0,0,0,0.00)',
            },
            '*::-webkit-scrollbar-thumb': {
                backgroundColor: theme.palette.primary.main,
                outline: '1px solid slategrey',
            }
        })} />;

        const cacheLtr = createCache({ key: "muiltr" });

        const cacheRtl = createCache({ key: "muirtl", stylisPlugins: [prefixer, rtlPlugin] });

        const reduxState = store.getState();

        return (
            (reduxState.local.local === null || reduxState.theme.theme === null || (reduxState.auth.isAuthenticated === true && (reduxState.auth.account === null || reduxState.role.roles === null || reduxState.auth.avatar === null))) ?
                <>
                    {inputGlobalStyles}
                    < div >!!!</div >
                </> :
                <>
                    <ThemeProvider theme={this.state.theme}>
                        <CacheProvider value={store.getState().local.local.direction === 'rtl' ? cacheRtl : cacheLtr}>
                            {inputGlobalStyles}
                            <BrowserRouter>
                                <Routes>
                                    <Route path='/' element={<WelcomePage />} />
                                    <Route path='/login' element={<LogInPage />} />
                                    <Route path='/register' element={<SignUpPage />} />

                                    <Route path='/dashboard/account' element={<DashboardAccountPage />} />
                                    <Route path='/dashboard/order' element={<DashboardOrderPage />} />
                                    <Route path='/dashboard/visit' element={<DashboardVisitPage />} />
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
                        </CacheProvider>
                    </ThemeProvider>
                </>
        );
    }
}

export default connect(null)(App);