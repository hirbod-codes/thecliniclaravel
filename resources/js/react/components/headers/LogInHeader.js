import React, { Component } from 'react'
import { Link } from 'react-router-dom';

import Header from './Header.js';

import Button from '@mui/material/Button';

export class LogInHeader extends Component {
    render() {
        return (
            <>
                <Header
                    title='Log In'
                    rightSide={
                        <Button type='button' sx={{ a: { textDecoration: 'none', color: 'white' }, m: 1 }}>
                            <Link to="/register" >Sign Up</Link>
                        </Button>
                    }
                />
            </>
        )
    }
}

export default LogInHeader
