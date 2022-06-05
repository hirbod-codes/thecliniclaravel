import React, { Component } from 'react';

import SignUpHeader from '../../headers/SignUpHeader.js';
import SignUpForm from '../../../forms/auth/SignUpForm.js';

import Grid from '@mui/material/Grid';

export class SignUpPage extends Component {
    render() {
        return (
            <>
                <Grid container spacing={1} >
                    <Grid item xs={12} >
                        <SignUpHeader />
                    </Grid>
                    <Grid item xs={12} >
                        <Grid container >
                            <Grid item xs >
                            </Grid>
                            <Grid item xs={12} sm={9} md={6} >
                                <SignUpForm />
                            </Grid>
                            <Grid item xs >
                            </Grid>
                        </Grid>
                    </Grid>
                    <Grid item xs={12} >footer
                        {/* <Footer /> */}
                    </Grid>
                </Grid>
            </>
        )
    }
}

export default SignUpPage
