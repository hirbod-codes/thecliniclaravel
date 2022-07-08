import React, { Component } from 'react'

import { Box } from '@mui/material';

export class TabPanel extends Component {
    render() {
        const { children, value, index, id, ...other } = this.props;
        return (
            <div
                role="tabpanel"
                hidden={value !== index}
                id={id}
                {...other}
            >
                {value === index && (
                    <Box sx={{ p: 3 }}>
                        {children}
                    </Box>
                )}
            </div>
        )
    }
}

export default TabPanel
