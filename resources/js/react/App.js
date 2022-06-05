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
import { Box } from '@mui/system';
import { GlobalStyles } from '@mui/material';

export class App extends Component {
    constructor(props) {
        super(props);

        this.changeTheme = (name) => {
            this.setState((state) => {
                state.themeContext.theme = createTheme(resolveTheme(name));
                state.themeContext.currentTheme = name.slice(0, name.indexOf('-'));
                return state;
            });
        };

        this.changeLocale = (name) => {
            this.setState((state) => {
                state.localeContext.currentLocale = name;
                return state;
            });
        };

        this.state = {
            themeContext: {
                theme: createTheme(resolveTheme('light-ltr')),
                changeTheme: this.changeTheme,
                currentTheme: 'light'
            },
            localeContext: {
                currentLocale: 'en',
                changeLocale: this.changeLocale
            }
        };
    }

    render() {
        const inputGlobalStyles = <GlobalStyles styles={theme => ({
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

        return (
            <LocaleContext.Provider value={this.state.localeContext}>
                <ThemeContext.Provider value={this.state.themeContext}>
                    <ThemeProvider theme={this.state.themeContext.theme}>
                        {inputGlobalStyles}
                        <BrowserRouter>
                            <Routes>
                                <Route path='/' element={<WelcomePage />} />
                                <Route path='/login' element={<LogInPage />} />
                                <Route path='/register' element={<SignUpPage />} />
                                <Route path='/email/verification-notification' element={<EmailVerificationNotice />} />
                                <Route path='/forgot-password/:redirecturl' element={<ForgotPassword />} />
                            </Routes>
                        </BrowserRouter>
                    </ThemeProvider>
                </ThemeContext.Provider>
            </LocaleContext.Provider>
        )
    }
}

export default App
