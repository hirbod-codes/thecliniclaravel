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

/**
 * LaserOrdersServerDataGrid
 * @augments {Component<Props, State>}
 */
export class LaserOrdersServerDataGrid extends Component {
    static propTypes = {
        currentLocaleName: PropTypes.string.isRequired,
        privileges: PropTypes.object.isRequired,
    }

    constructor(props) {
        super(props);

        this.handleFeedbackClose = this.handleFeedbackClose.bind(this);
        this.closeCreationModal = this.closeCreationModal.bind(this);
        this.openCreationModal = this.openCreationModal.bind(this);

        this.onPageChange = this.onPageChange.bind(this);
        this.onPageSizeChange = this.onPageSizeChange.bind(this);

        this.getRowCount = this.getRowCount.bind(this);
        this.addColumns = this.addColumns.bind(this);

        this.handleOnCreate = this.handleOnCreate.bind(this);
        this.handleDeletedRow = this.handleDeletedRow.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            feedbackOpen: false,
            feedbackMessage: '',
            feedbackColor: 'info',

            reload: false,

            page: 0,
            pagesLastOrderId: [0],

            lastOrderId: 0,

            deletingRowIds: [],

            isCreating: false,
            openCreationModal: false,
        };
    }

    getRowCount() {
        return new Promise(async (resolve) => {
            let rowCount = await fetchData('get', '/orders/count/laser', {}, { 'X-CSRF-TOKEN': this.state.token });
            resolve(rowCount.value);
        })
    }

    addColumns(columns) {
        columns.push({
            field: 'parts',
            headerName: translate('pages/orders/order/columns/parts', this.props.currentLocaleName),
            description: translate('pages/orders/order/columns/parts', this.props.currentLocaleName),
            renderCell: (params) => <PartsDataGridModal gridProps={{ rows: params.value.parts }} currentLocaleName={this.props.currentLocaleName} />,
        });

        columns.push({
            field: 'packages',
            headerName: translate('pages/orders/order/columns/packages', this.props.currentLocaleName),
            description: translate('pages/orders/order/columns/packages', this.props.currentLocaleName),
            renderCell: (params) => <PackagesDataGridModal gridProps={{ rows: params.value.packages }} currentLocaleName={this.props.currentLocaleName} />,
        });

        columns.push({
            field: 'priceWithDiscount',
            headerName: translate('pages/orders/order/columns/priceWithDiscount', this.props.currentLocaleName),
            description: translate('pages/orders/order/columns/priceWithDiscount', this.props.currentLocaleName),
            type: 'number',
            valueFormatter: formatToNumber,
        });

        columns.push({
            field: 'gender',
            headerName: translate('general/columns/gender/single/ucFirstLetterFirstWord', this.props.currentLocaleName),
            description: translate('general/columns/gender/single/ucFirstLetterFirstWord', this.props.currentLocaleName),
        });

        if (this.props.privileges.laserOrderDelete) {
            columns.push({
                field: 'actions',
                description: 'actions',
                type: 'actions',
                headerName: translate('general/columns/action/plural/ucFirstLetterFirstWord', this.props.currentLocaleName),
                width: 100,
                getActions: (params) => [
                    <GridActionsCellItem icon={this.state.deletingRowIds.indexOf(params.row.id) === -1 ? <DeleteIcon /> : <CircularProgress size='2rem' />} onClick={async (e) => { this.handleDeletedRow(e, params); }} label="Delete" />,
                ],
            });
        }

        return columns;
    }

    onPageChange(newPage) {
        this.setState({ page: newPage });
        if (this.state.pagesLastOrderId[newPage] !== undefined) {
            this.setState({ reload: true });
        } else {
            let pagesLastOrderId = this.state.pagesLastOrderId;
            pagesLastOrderId.push(this.state.lastOrderId);
            this.setState({ pagesLastOrderId: pagesLastOrderId, reload: true });
        }
    }

    onPageSizeChange(newPageSize) {
        this.setState({ page: 0, pages: [0], lastOrderId: 0, reload: true });
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
                    currentLocaleName={this.props.currentLocaleName}

                    privileges={this.props.privileges}
                    businessName='laser'

                    paginationMode='server'

                    afterGetData={(data) => this.setState({ lastOrderId: data[data.length - 1].id })}
                    getRowCount={this.getRowCount}
                    addColumns={this.addColumns}

                    reload={this.state.reload}
                    afterReload={() => this.setState({ reload: false })}

                    onPageChange={this.onPageChange}
                    onPageSizeChange={this.onPageSizeChange}

                    gridProps={{
                        components: {
                            Toolbar: () =>
                                <GridToolbarContainer>
                                    <Stack direction='row'>
                                        <GridToolbarColumnsButton />
                                        <GridToolbarFilterButton />
                                        <GridToolbarDensitySelector />
                                        <GridToolbarExport />
                                        {this.props.privileges.laserOrderCreate ?
                                            (this.state.isCreating ?
                                                <LoadingButton loading variant='text' size='small' >
                                                    {translate('general/create/single/ucFirstLetterFirstWord', this.props.currentLocaleName)}
                                                </LoadingButton> :
                                                <Button variant='text' onClick={this.openCreationModal} size='small' startIcon={<AddIcon />}>
                                                    {translate('general/create/single/ucFirstLetterFirstWord', this.props.currentLocaleName)}
                                                </Button>
                                            ) : null
                                        }
                                    </Stack>
                                </GridToolbarContainer>
                        }
                    }}

                    lastOrderId={this.state.pagesLastOrderId[this.state.page]}
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

                {this.props.privileges.laserOrderCreate &&
                    <Modal
                        open={this.state.openCreationModal}
                        onClose={this.closeCreationModal}
                    >
                        <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                            <LaserOrderCreation onCreated={this.handleOnCreate} currentLocaleName={this.props.currentLocaleName} />
                        </Paper>
                    </Modal>
                }
            </>
        )
    }

    async handleDeletedRow(e, params) {
        if (!this.props.privileges.laserOrderDelete) {
            return;
        }

        let deletingRowIds = this.state.deletingRowIds;
        deletingRowIds.push(params.row.id);
        await updateState(this, { deletingRowIds: deletingRowIds });

        fetchData('delete', '/orders/laser/' + params.row.userId + '/' + params.row.id, {}, { 'X-CSRF-TOKEN': this.state.token })
            .then((res) => {
                let deletingRowIds = this.state.deletingRowIds;
                delete deletingRowIds[deletingRowIds.indexOf(params.row.id)];
                updateState(this, { deletingRowIds: deletingRowIds });
                if (res.status === 200) {
                    this.setState({ reload: true, feedbackOpen: true, feedbackMessage: translate('general/successful/single/ucFirstLetterFirstWord', this.props.currentLocaleName), feedbackColor: 'success' });
                } else {
                    this.setState({ feedbackOpen: true, feedbackMessage: translate('general/failure/single/ucFirstLetterFirstWord', this.props.currentLocaleName), feedbackColor: 'error' });
                }
            });
    }

    async handleOnCreate(e) {
        this.closeCreationModal();
        this.setState({ reload: true, feedbackOpen: true, feedbackMessage: translate('general/successful/single/ucFirstLetterFirstWord', this.props.currentLocaleName), feedbackColor: 'success' });
    }
}

export default LaserOrdersServerDataGrid
