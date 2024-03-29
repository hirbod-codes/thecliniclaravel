import React, { Component } from 'react';
import { Link } from 'react-router-dom';

import SignUpForm from '../../../forms/auth/SignUpForm.js';
import Header from '../../headers/Header.js';

import Grid from '@mui/material/Grid';
import { translate } from '../../../traslation/translate.js';
import { connect } from 'react-redux';

export class SignUpPage extends Component {
    render() {
        return (
            <>
                <Grid container spacing={1} >
                    <Grid item xs={12} >
                        <Header title={<Link to='/' style={{ textDecoration: 'none', color: 'white' }} >{translate('general/sign-up/single/ucFirstLetterAllWords')}</ Link>} />
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

export default connect(mapStateToProps)(SignUpPage)
