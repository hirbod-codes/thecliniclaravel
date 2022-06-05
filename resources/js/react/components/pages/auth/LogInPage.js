import React, { Component } from 'react'

import LogInHeader from '../../headers/LogInHeader.js';
import LogInForm from '../../../forms/auth/LogInForm.js';

import { Grid } from '@mui/material';

export class LogInPage extends Component {
    render() {
        return (
            <>
                <Grid container spacing={1} >
                    <Grid item xs={12} >
                        <LogInHeader />
                    </Grid>
                    <Grid item xs={12} >
                        <Grid container >
                            <Grid item xs >
                            </Grid>
                            <Grid item xs={12} sm={9} md={6} >
                                <LogInForm />
                            </Grid>
                            <Grid item xs >
                            </Grid>
                        </Grid>
                    </Grid>
                    <Grid item xs={12} >
                        {/* <Footer /> */}
                    </Grid>
                </Grid>
            </>
        )
    }
}

export default LogInPage
