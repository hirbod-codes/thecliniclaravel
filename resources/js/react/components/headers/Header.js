import React, { Component } from 'react';
import { Link } from 'react-router-dom';

import ThemeButton from '../buttons/ThemeButton.js';
import AppLocalButton from '../buttons/AppLocalButton.js';

import AppBar from '@mui/material/AppBar';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Toolbar from '@mui/material/Toolbar';
import Typography from '@mui/material/Typography';

export class Header extends Component {
    render() {
        let authenticated = false;

        if (this.props.authenticated) {
            authenticated = true;
        }

        return (
            <>
                <Box sx={{ flexGrow: 1 }}>
                    <AppBar position="fixed">
                        <Toolbar>
                            {this.props.leftSide}
                            <Typography variant="h6" component="div" sx={{ flexGrow: 1 }}>
                                {this.props.title}
                            </Typography>
                            {this.props.rightSide}
                            {authenticated &&
                                <Button type='button' sx={{ a: { textDecoration: 'none', color: 'white' }, m: 1 }} >
                                    <Link to='/logout'>
                                        Log Out
                                    </Link>
                                </Button>}
                            <ThemeButton buttonProps={{ sx: { m: 1 } }} />
                            <AppLocalButton buttonProps={{ sx: { m: 1 } }} />
                        </Toolbar>
                    </AppBar>
                    <Toolbar />
                </Box>
            </>
        )
    }
}

export default Header
