import React, { Component } from 'react'

import { Button, Modal, Paper } from '@mui/material';

import PackagesDataGrid from '../PackagesDataGrid';
import { translate } from '../../../../traslation/translate';

export class PackagesDataGridModal extends Component {
    constructor(props) {
        super(props);

        this.openModal = this.openModal.bind(this);
        this.closeModal = this.closeModal.bind(this);

        this.state = {
            open: false,
        };
    }

    closeModal(e) {
        this.setState({ open: false });
    }

    openModal(e) {
        this.setState({ open: true });
    }

    render() {
        let props = {};
        if (Object.hasOwnProperty.call(this.props, 'gridProps')) {
            props = this.props.gridProps;
        }

        return (
            <>
                <Button type='button' variant='contained' onClick={this.openModal}>
                    {translate('general/show/single/ucFirstLetterFirstWord')}
                </Button>

                <Modal
                    open={this.state.open}
                    onClose={this.closeModal}
                >
                    <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%' }}>
                        <PackagesDataGrid rows={this.props.rows} {...props} />
                    </Paper>
                </Modal>
            </>
        )
    }
}

export default PackagesDataGridModal
