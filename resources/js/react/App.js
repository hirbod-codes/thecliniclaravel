import React, { Component } from 'react';
import { BrowserRouter, Route, Routes } from 'react-router-dom';

import '../../css/app.css';

import '@fontsource/roboto/300.css';
import '@fontsource/roboto/400.css';
import '@fontsource/roboto/500.css';
import '@fontsource/roboto/700.css';

import { ThemeContext, resolveTheme } from './components/themeContenxt.js';
import { LocaleContext } from './components/localeContext.js';

import WelcomePage from "./components/pages/WelcomePage.js";
import LogInPage from "./components/pages/auth/LogInPage.js";
import SignUpPage from "./components/pages/auth/SignUpPage.js";

import { createTheme } from '@mui/material/styles';
import { ThemeProvider } from '@emotion/react';
import { GlobalStyles } from '@mui/material';
import { getJsonData, postJsonData, putJsonData } from './components/Http/fetch';
import DashboardOrderPage from './components/pages/dashboard/DashboardOrderPage';
import DashboardVisitPage from './components/pages/dashboard/DashboardVisitPage';
import UserIconNavigator from './components/UserIconNavigator';
import { updateState } from './components/helpers';
import { PrivilegesContext } from './components/privilegesContext';

export class App extends Component {
    constructor(props) {
        super(props);

        this.getDataSynchronously = this.getDataSynchronously.bind(this);

        this.changeLocale = (name) => {
            putJsonData('/locale', { 'locale': name }, { 'X-CSRF-TOKEN': this.state.token, 'Content-type': '*/*' })
                .then((res) => {
                    if (res.status === 200) {
                        document.location.reload();
                    }

                    return res.text();
                });
        };

        this.changeTheme = (name) => {
            postJsonData('/theme', { theme: name }, { 'X-CSRF-TOKEN': this.state.token })
                .then((res) => {
                    if (res.status === 200) {
                        document.location.reload();
                    }

                    return res.json();
                });
        };

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            finishStatus: {
                locales: false,
                locale: false,
                themeData: false,
            },

            isAuthenticationLoading: true,
            isAuthenticated: false,
            isAccountLoading: true,
            account: null,
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

    // For perfomance reasons we are not using asynchronous programming for this method
    getDataSynchronously() {
        getJsonData('/isAuthenticated', { 'X-CSRF-TOKEN': this.state.token }).then((res) => res.json())
            .then((isAuthenticated) => {
                updateState(this, (state) => {
                    state.isAuthenticationLoading = false;
                    state.isAuthenticated = isAuthenticated.authenticated;
                    return state;
                });
            });

        getJsonData('/account', { 'X-CSRF-TOKEN': this.state.token }).then((res) => { return res.status === 200 ? res.json() : null })
            .then((account) => {
                if (account !== null) {
                    updateState(this, (state) => {
                        state.isAccountLoading = false;
                        state.account = account;
                        return state;
                    });

                    getJsonData('/avatar?accountId=' + account.id, { 'X-CSRF-TOKEN': this.state.token }).then((res) => { return res.status === 200 ? res.text() : null })
                        .then((avatar) => {
                            if (avatar !== null) {
                                updateState(this, (state) => {
                                    state.isAvatarLoading = false;
                                    state.image = 'data:image/png;base64,' + avatar;
                                    return state;
                                });
                            }
                        });

                    getJsonData('/privileges/show?accountId=' + account.id, { 'X-CSRF-TOKEN': this.state.token }).then((res) => { return res.status === 200 ? res.json() : null })
                        .then((privileges) => {
                            if (privileges !== null) {
                                updateState(this, (state) => {
                                    state.privileges = privileges;
                                    return state;
                                });
                            }
                        });
                } else {
                    updateState(this, (state) => {
                        state.isAccountLoading = false;
                        return state;
                    });
                }
            });

        getJsonData('/isEmailVerified', { 'X-CSRF-TOKEN': this.state.token }).then((res) => { return res.status === 200 ? res.json() : null })
            .then((isEmailVerified) => {
                if (isEmailVerified !== null) {
                    updateState(this, (state) => {
                        state.isEmailVerified = isEmailVerified.verified;
                        return state;
                    });
                }
            });

        getJsonData('/locale', { 'X-CSRF-TOKEN': this.state.token }).then((res) => res.json())
            .then((locale) => {
                updateState(this, (state) => {
                    state.finishStatus.locale = true;
                    state.localeContext.currentLocale = locale;
                    state.localeContext.isLocaleLoading = false;
                    return state;
                });
                document.dir = locale.direction;

                getJsonData('/theme', { 'X-CSRF-TOKEN': this.state.token }).then((res) => res.json())
                    .then((themeData) => {
                        updateState(this, (state) => {
                            state.finishStatus.themeData = true;
                            state.themeContext.theme = createTheme(resolveTheme(themeData.theme + '-' + locale.direction));
                            state.themeContext.currentTheme = themeData.theme;
                            state.themeContext.isThemeLoading = false;
                            return state;
                        });
                        document.dir = locale.direction;
                    });
            });

        getJsonData('/locales', { 'X-CSRF-TOKEN': this.state.token }).then((res) => res.json())
            .then((locales) => {
                updateState(this, (state) => {
                    state.finishStatus.locales = true;
                    state.localeContext.locales = locales;
                    return state;
                });
            });
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
            account: this.state.account,
            privileges: this.state.privileges,
        };

        let navigator = null;
        let currentLocaleName = null;
        if (!this.state.localeContext.isLocaleLoading && !this.state.themeContext.isThemeLoading) {
            currentLocaleName = this.state.localeContext.currentLocale.shortName;
            navigator = <UserIconNavigator image={this.state.image} isAvatarLoading={this.state.isAvatarLoading} isEmailVerified={this.state.isEmailVerified} currentLocaleName={currentLocaleName} />;
        }

        return (
            this.state.localeContext.isLocaleLoading || this.state.themeContext.isThemeLoading || this.state.isAccountLoading ?
                <>
                    {inputGlobalStyles}
                    < div ></div >
                </> :
                <LocaleContext.Provider value={this.state.localeContext}>
                    <ThemeContext.Provider value={this.state.themeContext}>
                        <ThemeProvider theme={this.state.themeContext.theme}>
                            <PrivilegesContext.Provider value={this.state.privileges}>
                                {inputGlobalStyles}
                                <BrowserRouter>
                                    <Routes>
                                        <Route path='/' element={<WelcomePage navigator={navigator} currentLocaleName={currentLocaleName} {...authProps} />} />
                                        <Route path='/login' element={<LogInPage navigator={navigator} currentLocaleName={currentLocaleName} {...authProps} />} />
                                        <Route path='/register' element={<SignUpPage navigator={navigator} currentLocaleName={currentLocaleName} {...authProps} />} />

                                        <Route path='/dashboard/visit' element={<DashboardVisitPage navigator={navigator} currentLocaleName={currentLocaleName} {...authProps} />} />
                                        <Route path='/dashboard/order' element={<DashboardOrderPage navigator={navigator} currentLocaleName={currentLocaleName} {...authProps} />} />
                                    </Routes>
                                </BrowserRouter>
                            </PrivilegesContext.Provider>
                        </ThemeProvider>
                    </ThemeContext.Provider>
                </LocaleContext.Provider>
        );
    }
}

export default App
