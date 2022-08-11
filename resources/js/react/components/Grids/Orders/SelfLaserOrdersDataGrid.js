import React, { Component } from 'react'

import PropTypes from 'prop-types';

import CloseIcon from '@mui/icons-material/Close';
import AddIcon from '@mui/icons-material/Add';
import DeleteIcon from '@mui/icons-material/Delete';
import { GridActionsCellItem, GridToolbarColumnsButton, GridToolbarContainer, GridToolbarDensitySelector, GridToolbarExport, GridToolbarFilterButton } from '@mui/x-data-grid';
import { Alert, Button, CircularProgress, IconButton, Modal, Paper, Snackbar, Stack } from '@mui/material';

import OrdersDataGrid from './OrdersDataGrid'
import { fetchData } from '../../Http/fetch';
import { translate } from '../../../traslation/translate';
import { updateState } from '../../helpers';
import LaserOrderCreation from '../../Menus/Orders/LaserOrderCreation';
import LoadingButton from '@mui/lab/LoadingButton';
import { formatToNumber } from '../formatters';
import PartsDataGridModal from './Modals/PartsDataGridModal';
import PackagesDataGridModal from './Modals/PackagesDataGridModal';
import { PrivilegesContext } from '../../privilegesContext';

/**
 * SelfLaserOrdersDataGrid
 * @augments {Component<Props, State>}
 */
export class SelfLaserOrdersDataGrid extends Component {
    static propTypes = {
        account: PropTypes.object.isRequired,
        accountRole: PropTypes.string.isRequired,
    }

    static contextType = PrivilegesContext;

    constructor(props) {
        super(props);

        this.handleFeedbackClose = this.handleFeedbackClose.bind(this);
        this.closeCreationModal = this.closeCreationModal.bind(this);
        this.openCreationModal = this.openCreationModal.bind(this);

        this.addColumns = this.addColumns.bind(this);

        this.handleOnCreate = this.handleOnCreate.bind(this);
        this.handleDeletedRow = this.handleDeletedRow.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            feedbackOpen: false,
            feedbackMessage: '',
            feedbackColor: 'info',

            reload: false,

            deletingRowIds: [],

            isCreating: false,
            openCreationModal: false,
        };
    }

    addColumns(columns) {
        columns.push({
            field: 'parts',
            headerName: translate('pages/orders/order/columns/parts'),
            description: translate('pages/orders/order/columns/parts'),
            renderCell: (params) => <PartsDataGridModal gridProps={{ rows: params.row.parts }} />,
        });

        columns.push({
            field: 'packages',
            headerName: translate('pages/orders/order/columns/packages'),
            description: translate('pages/orders/order/columns/packages'),
            renderCell: (params) => <PackagesDataGridModal gridProps={{ rows: params.row.packages }} />,
        });

        columns.push({
            field: 'price_with_discount',
            headerName: translate('pages/orders/order/columns/price_with_discount'),
            description: translate('pages/orders/order/columns/price_with_discount'),
            type: 'number',
            valueFormatter: formatToNumber,
        });

        if (this.context.deleteOrder !== undefined && this.context.deleteOrder.laser !== undefined && this.context.deleteOrder.laser.indexOf('self') !== -1) {
            columns.push({
                field: 'actions',
                description: 'actions',
                type: 'actions',
                headerName: translate('general/columns/action/plural/ucFirstLetterFirstWord'),
                width: 100,
                getActions: (params) => [
                    <GridActionsCellItem icon={this.state.deletingRowIds.indexOf(params.row.id) === -1 ? <DeleteIcon /> : <CircularProgress size='2rem' />} onClick={async (e) => { this.handleDeletedRow(e, params); }} label="Delete" />,
                ],
            });
        }

        return columns;
    }

    handleFeedbackClose(event, reason) {
        if (reason === 'clickaway') {
            return;
        }

        this.setState({ feedbackOpen: false });
    }

    closeCreationModal(e) {
        this.setState({ isCreating: false, openCreationModal: false });
    }

    openCreationModal(e) {
        this.setState({ isCreating: true, openCreationModal: true });
    }

    render() {
        return (
            <>
                <OrdersDataGrid
                    businessName='laser'

                    username={this.props.account.username}

                    addColumns={this.addColumns}

                    reload={this.state.reload}
                    afterReload={() => this.setState({ reload: false })}

                    gridProps={{
                        components: {
                            Toolbar: () =>
                                <GridToolbarContainer>
                                    <Stack direction='row'>
                                        <GridToolbarColumnsButton />
                                        <GridToolbarFilterButton />
                                        <GridToolbarDensitySelector />
                                        <GridToolbarExport />
                                        {(this.context.createOrder !== undefined && this.context.createOrder.laser !== undefined && this.context.createOrder.laser.indexOf('self') !== -1) ?
                                            (this.state.isCreating ?
                                                <LoadingButton loading variant='text' size='small' >
                                                    {translate('general/create/single/ucFirstLetterFirstWord')}
                                                </LoadingButton> :
                                                <Button variant='text' onClick={this.openCreationModal} size='small' startIcon={<AddIcon />}>
                                                    {translate('general/create/single/ucFirstLetterFirstWord')}
                                                </Button>
                                            ) : null
                                        }
                                    </Stack>
                                </GridToolbarContainer>
                        }
                    }}
                />

                <Snackbar
                    open={this.state.feedbackOpen}
                    autoHideDuration={6000}
                    onClose={this.handleFeedbackClose}
                    action={
                        <IconButton
                            size="small"
                            onClick={this.handleFeedbackClose}
                        >
                            <CloseIcon fontSize="small" />
                        </IconButton>
                    }
                >
                    <Alert onClose={this.handleFeedbackClose} severity={this.state.feedbackColor} sx={{ width: '100%' }}>
                        {this.state.feedbackMessage}
                    </Alert>
                </Snackbar>

                <Modal
                    open={this.state.openCreationModal}
                    onClose={this.closeCreationModal}
                >
                    <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                        <LaserOrderCreation accountRole={this.props.accountRole} account={this.props.account} onCreated={this.handleOnCreate} />
                    </Paper>
                </Modal>
            </>
        )
    }

    async handleDeletedRow(params) {
        if (!(this.context.deleteOrder !== undefined && this.context.deleteOrder.laser !== undefined && this.context.deleteOrder.laser.indexOf('self') !== -1)) {
            return;
        }

        let deletingRowIds = this.state.deletingRowIds;
        deletingRowIds.push(params.row.id);
        await updateState(this, { deletingRowIds: deletingRowIds });

        let data = {
            businessName: 'laser',
            childOrderId: params.row.id,
        };

        let r = await fetchData('delete', '/order', data, { 'X-CSRF-TOKEN': this.state.token });

        deletingRowIds = this.state.deletingRowIds;
        delete deletingRowIds[deletingRowIds.indexOf(params.row.id)];
        updateState(this, { deletingRowIds: deletingRowIds });

        if (r.response.status === 200) {
            this.setState({ reload: true, feedbackOpen: true, feedbackMessage: translate('general/successful/single/ucFirstLetterFirstWord'), feedbackColor: 'success' });
        } else {
            this.setState({ feedbackOpen: true, feedbackMessage: translate('general/failure/single/ucFirstLetterFirstWord'), feedbackColor: 'error' });
        }
    }

    async handleOnCreate(e) {
        this.closeCreationModal();
        this.setState({ reload: true, feedbackOpen: true, feedbackMessage: translate('general/successful/single/ucFirstLetterFirstWord'), feedbackColor: 'success' });
    }
}

export default SelfLaserOrdersDataGrid
