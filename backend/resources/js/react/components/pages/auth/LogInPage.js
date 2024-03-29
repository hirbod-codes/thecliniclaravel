import React, { Component } from 'react'
import { Link } from 'react-router-dom';

import LogInForm from '../../../forms/auth/LogInForm.js';
import Header from '../../headers/Header.js';

import { Grid } from '@mui/material';
import { translate } from '../../../traslation/translate.js';
import { connect } from 'react-redux';

export class LogInPage extends Component {
    render() {
        return (
            <>
                <Grid container spacing={1} sx={{ minHeight: '100vh' }}>
                    <Grid item xs={12} >
                        <Header title={<Link to='/' style={{ textDecoration: 'none', color: 'white' }} >{translate('general/log-in/single/ucFirstLetterAllWords')}</ Link>} />
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

const mapStateToProps = state => ({
    redux: state
})

export default connect(mapStateToProps)(LogInPage)
