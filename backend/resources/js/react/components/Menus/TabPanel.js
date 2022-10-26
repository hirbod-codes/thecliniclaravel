import React, { Component } from 'react'

import { Box } from '@mui/material';

export class TabPanel extends Component {
    render() {
        const { children, value, index, id, ...other } = this.props;
        return (
            <Box
                role="tabpanel"
                hidden={value !== index}
                id={id}
                {...other}
            >
                {value === index && children}
            </Box>
        )
    }
}

export default TabPanel
