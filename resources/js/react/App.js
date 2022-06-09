import React, { Component } from 'react';
import { BrowserRouter, Route, Routes } from 'react-router-dom';

import '../../css/app.css';

import '@fontsource/roboto/300.css';
import '@fontsource/roboto/400.css';
import '@fontsource/roboto/500.css';
import '@fontsource/roboto/700.css';

import { ThemeContext, resolveTheme } from './components/themeContenxt.js';
import { LocaleContext } from './components/localeContext.js';

import { WelcomePage } from "./components/pages/WelcomePage.js";
import { LogInPage } from "./components/pages/auth/LogInPage.js";
import { SignUpPage } from "./components/pages/auth/SignUpPage.js";
import { EmailVerificationNotice } from "./components/pages/auth/EmailVerificationNotice.js";
import { ForgotPassword } from "./components/pages/auth/ForgotPassword.js";

import { createTheme } from '@mui/material/styles';
import { ThemeProvider } from '@emotion/react';
import { GlobalStyles } from '@mui/material';
import { backendURL, getJsonData, postJsonData, putJsonData } from './components/Http/fetch';

export class App extends Component {
    constructor(props) {
        super(props);

        this.changeLocale = (name) => {
            console.log(name);
            putJsonData(backendURL() + '/locale', { 'locale': name }, { 'X-CSRF-TOKEN': this.state.token, 'Content-type': '*/*' })
                .then((res) => {
                    if (res.status === 200) {
                        document.location.reload();
                    }

                    return res.text();
                });
        };

        this.changeTheme = (name) => {
            console.log(name);
            postJsonData(backendURL() + '/theme', { theme: name }, { 'X-CSRF-TOKEN': this.state.token })
                .then((res) => {
                    if (res.status === 200) {
                        document.location.reload();
                    }

                    return res.json();
                });
        };

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            isComponentLoading: true,
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
        return !this.state.isComponentLoading;
    }

    componentDidMount() {
        let state = this.state;

        this.getLocales()
            .then((res) => {
                return res.json();
            })
            .then((data) => {
                state.localeContext.locales = data;

                this.getCurrentLocale()
                    .then((res) => {
                        return res.json();
                    })
                    .then((data) => {
                        state.localeContext.currentLocale = data;
                        state.localeContext.isLocaleLoading = false;

                        this.getCurrentTheme()
                            .then((res) => {
                                return res.json();
                            })
                            .then((data) => {
                                state.themeContext.theme = createTheme(resolveTheme(data.theme + '-' + state.localeContext.currentLocale.direction));
                                state.themeContext.currentTheme = data.theme;
                                state.themeContext.isThemeLoading = false;

                                state.isComponentLoading = false;

                                document.dir = state.localeContext.currentLocale.direction;

                                this.setState(state);
                            });
                    });
            });
    }

    async getLocales() {
        const res = await getJsonData(backendURL() + '/locales', { 'X-CSRF-TOKEN': this.state.token })
        return res;
    }

    async getCurrentLocale() {
        const res = await getJsonData(backendURL() + '/locale', { 'X-CSRF-TOKEN': this.state.token })
        return res;
    }


    async getCurrentTheme() {
        const res = await getJsonData(backendURL() + '/theme', { 'X-CSRF-TOKEN': this.state.token });
        return res;
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

        if (this.state.localeContext.isLocaleLoading || this.state.themeContext.isThemeLoading || this.state.isComponentLoading) {
            return (
                <>
                    {inputGlobalStyles}
                    < div >!!</div >
                </>
            );
        }

        return (
            <LocaleContext.Provider value={this.state.localeContext}>
                <ThemeContext.Provider value={this.state.themeContext}>
                    <ThemeProvider theme={this.state.themeContext.theme}>
                        {inputGlobalStyles}
                        <BrowserRouter>
                            <Routes>
                                <Route path='/' element={<WelcomePage currentLocaleName={this.state.localeContext.currentLocale.shortName} />} />
                                <Route path='/login' element={<LogInPage currentLocaleName={this.state.localeContext.currentLocale.shortName} />} />
                                <Route path='/register' element={<SignUpPage currentLocaleName={this.state.localeContext.currentLocale.shortName} />} />
                                <Route path='/email/verification-notification' element={<EmailVerificationNotice currentLocaleName={this.state.localeContext.currentLocale.shortName} />} />
                                <Route path='/forgot-password/:redirecturl' element={<ForgotPassword currentLocaleName={this.state.localeContext.currentLocale.shortName} />} />
                            </Routes>
                        </BrowserRouter>
                    </ThemeProvider>
                </ThemeContext.Provider>
            </LocaleContext.Provider>
        )
    }
}

export default App
