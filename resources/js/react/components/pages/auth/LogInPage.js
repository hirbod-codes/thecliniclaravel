import React, { Component } from 'react'
import { Link } from 'react-router-dom';

import LogInForm from '../../../forms/auth/LogInForm.js';
import Header from '../../headers/Header.js';

import Button from '@mui/material/Button';
import { Grid } from '@mui/material';
import { translate } from '../../../traslation/translate.js';

export class LogInPage extends Component {
    render() {
        return (
            <>
                <Grid container spacing={1} >
                    <Grid item xs={12} >
                        <Header
                            title={<Link to='/' style={{ textDecoration: 'none', color: 'white' }} >{translate('general/log-in/ucFirstLetterAllWords', this.props.currentLocaleName)}</ Link>}
                            rightSide={
                                <Button type='button' sx={{ a: { textDecoration: 'none', color: 'white' }, m: 1 }}>
                                    <Link to="/register" >{translate('general/sign-up/ucFirstLetterAllWords', this.props.currentLocaleName)}</Link>
                                </Button>
                            }
                        />
                    </Grid>
                    <Grid item xs={12} >
                        <Grid container >
                            <Grid item xs >
                            </Grid>
                            <Grid item xs={12} sm={9} md={6} >
                                <LogInForm currentLocaleName={this.props.currentLocaleName} />
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
