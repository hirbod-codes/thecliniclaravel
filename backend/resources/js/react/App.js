import React, { Component } from 'react';
import { BrowserRouter, Route, Routes } from 'react-router-dom';

import '../../css/app.css';

import '@fontsource/roboto/300.css';
import '@fontsource/roboto/400.css';
import '@fontsource/roboto/500.css';
import '@fontsource/roboto/700.css';

import CloseIcon from '@mui/icons-material/Close';
import { Alert, Backdrop, CircularProgress, CssBaseline, GlobalStyles, IconButton, Snackbar } from '@mui/material';

import { prefixer } from "stylis";
import rtlPlugin from "stylis-plugin-rtl";
import createCache from "@emotion/cache";
import { CacheProvider } from "@emotion/react";
import { createTheme, ThemeProvider } from '@mui/material/styles';

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

            isAuthenticated: false,
            account: null,
            privileges: null,
            isEmailVerified: false,
            image: null,

            theme: null,
            isResolved: false
        };
    }

    async componentDidMount() {
        await this.getDataSynchronously();
        await updateState(this, { isResolved: true });

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
        return new Promise(async (resolve) => {
            let finishArray = [];
            const targetLength = 3;

            fetchData('get', '/isAuthenticated', {}, { 'X-CSRF-TOKEN': this.state.token, 'Accept': 'application/json' }).then(async (isAuthenticatedResponse) => {
                if (isAuthenticatedResponse.response.status !== 200 || isAuthenticatedResponse.value.authenticated !== true) {
                    return;
                }

                this.props.dispatch(isAuthenticated());

                await this.updateAccount();

                const privilegesResponse = await fetchData('get', '/role', {}, { 'X-CSRF-TOKEN': this.state.token, 'Accept': 'application/json' });
                if (privilegesResponse.response.status === 200) {
                    this.props.dispatch(gotRoles(privilegesResponse.value));
                }

                const emailResponse = await fetchData('get', '/isEmailVerified', {}, { 'X-CSRF-TOKEN': this.state.token, 'Accept': 'application/json' });
                if (emailResponse.response.status === 200) {
                    this.props.dispatch(isEmailVerified(emailResponse.value));
                }
            }).then(() => {
                finishArray.push(finishArray.length);
                if (finishArray.length >= targetLength) { resolve(); }
            });

            fetchData('get', '/locale', {}, { 'X-CSRF-TOKEN': this.state.token, 'Accept': 'application/json' }, [], false).then(async (localeResponse) => {
                let locale = localeResponse.value;
                if (localeResponse.response.status !== 200) {
                    return;
                }
                this.props.dispatch(gotLocal(locale));

                document.dir = locale.direction;
                document.body.setAttribute('dir', locale.direction);

                let themeResponse = await fetchData('get', '/theme', {}, { 'X-CSRF-TOKEN': this.state.token, 'Accept': 'application/json' }, [], false);
                let themeData = themeResponse.value;
                themeData.locale = locale;

                if (themeResponse.response.status !== 200) {
                    return;
                }

                this.props.dispatch(gotTheme(themeData.theme));

                await updateState(this, (state) => {
                    state.theme = this.createTheme(themeData.theme, locale);
                    return state;
                });
            }).then(() => {
                finishArray.push(finishArray.length);
                if (finishArray.length >= targetLength) { resolve(); }
            });

            fetchData('get', '/locales', {}, { 'X-CSRF-TOKEN': this.state.token, 'Accept': 'application/json' }, [], true).then(async (localesResponse) => {
                let locales = localesResponse.value;
                if (localesResponse.response.status === 200) {
                    this.props.dispatch(gotLocals(locales));
                }
            }).then(() => {
                finishArray.push(finishArray.length);
                if (finishArray.length >= targetLength) { resolve(); }
            });
        });
    }

    async updateAccount() {
        return new Promise(async (resolve) => {
            fetchData('get', '/account', {}, { 'X-CSRF-TOKEN': this.state.token, 'Accept': 'application/json' }).then(async (accountResponse) => {
                if (accountResponse.response.status !== 200) {
                    resolve();
                    return;
                }
                this.props.dispatch(gotAccount(accountResponse.value));

                fetchData('get', '/avatar?accountId=' + accountResponse.value.id, {}, { 'X-CSRF-TOKEN': this.state.token, 'Accept': 'application/json' }).then(async (avatarResponse) => {
                    if (avatarResponse.response.status === 200) {
                        this.props.dispatch(gotAvatar('data:image/png;base64,' + avatarResponse.value));
                    } else {
                        // In case there is actually no avatar for this user we send undefined so that it doesn't stop the render method.
                        this.props.dispatch(gotAvatar(undefined));
                    }
                    resolve();
                }, () => {
                    resolve();
                });
            }, () => {
                resolve();
            });
        });
    }

    async onThemeChange() {
        let locale = store.getState().local.local;
        let theme = store.getState().theme.theme;

        await updateState(this, { theme: this.createTheme(theme, locale) });
        await fetchData('post', '/theme', { theme: theme }, { 'X-CSRF-TOKEN': this.state.token, 'Accept': 'application/json', 'Content-type': 'application/json' });
    }

    createTheme(theme, locale) {
        return createTheme(resolveTheme(theme + '_' + locale.direction), resolveLocalization(locale.shortName));
    }

    async onLocalChange() {
        let locale = store.getState().local.localName;

        let r = await fetchData('put', '/locale', { 'locale': locale }, { 'X-CSRF-TOKEN': this.state.token, 'Accept': 'application/json', 'Content-type': 'application/json' });
        if (r.response.status !== 200) {
            return;
        }
        r = null;

        r = await fetchData('get', '/locale', {}, { 'X-CSRF-TOKEN': this.state.token, 'Accept': 'application/json', 'Content-type': 'application/json' });
        if (r.response.status !== 200) {
            return;
        }

        document.dir = r.value.direction;
        document.body.setAttribute('dir', r.value.direction);

        await this.props.dispatch(gotLocal(r.value));
        this.forceUpdate();
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

    async onLogout() {
        await this.resetAuthInfo();
        window.location.replace('/');
    }

    async onLogin() {
        await this.resetAuthInfo();
        await this.reload();
    }

    async reload() {
        return new Promise(async (resolve) => {
            let finishStatus = this.state.finishStatus;
            for (const k in finishStatus) {
                finishStatus[k] = true;
            }

            await updateState(this, { isResolved: false });
            await this.getDataSynchronously();
            await updateState(this, { isResolved: true });

            resolve();
        })
    }

    resetAuthInfo() {
        return new Promise(async (resolve) => {
            this.props.dispatch(isNotAuthenticated());
            this.props.dispatch(gotAvatar(null));
            this.props.dispatch(gotRoles(null));
            this.props.dispatch(gotAvatar(null));
            this.props.dispatch(isEmailVerified(false));

            resolve();
        })
    }

    render() {
        if (this.state.isResolved === false) {
            return (
                <Backdrop
                    sx={{ color: '#fff', zIndex: (theme) => theme.zIndex.drawer + 1 }}
                    open={true}
                >
                    <CircularProgress color="inherit" />
                </Backdrop>
            );
        }

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

        return (
            <ThemeProvider theme={this.state.theme}>
                <CacheProvider value={store.getState().local.local.direction === 'rtl' ? cacheRtl : cacheLtr}>
                    <CssBaseline />
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
        );
    }
}

const mapStateToProps = state => ({
    redux: state
});

export default connect(mapStateToProps)(App);