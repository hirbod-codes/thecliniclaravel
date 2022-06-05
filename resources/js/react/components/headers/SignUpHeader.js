import React, { Component } from 'react'
import { Link } from 'react-router-dom';

import Header from './Header'

import Button from '@mui/material/Button';

export class SignUpHeader extends Component {
    render() {
        return (
            <Header
                title='Sign Up'
                rightSide={
                    <Button type='button' sx={{ a: { textDecoration: 'none', color: 'white' }, m: 1 }}>
                        <Link to="/login" >Log In</Link>
                    </Button>
                }
            />
        )
    }
}

export default SignUpHeader
