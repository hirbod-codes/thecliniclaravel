import React, { Component } from 'react'

// import PropTypes from 'prop-types';

import CloseIcon from '@mui/icons-material/Close';
import AddIcon from '@mui/icons-material/Add';
import DeleteIcon from '@mui/icons-material/Delete';
import { GridActionsCellItem, GridToolbarColumnsButton, GridToolbarContainer, GridToolbarDensitySelector, GridToolbarExport, GridToolbarFilterButton } from '@mui/x-data-grid';
import { Alert, Autocomplete, Button, CircularProgress, IconButton, Modal, Paper, Snackbar, Stack, TextField } from '@mui/material';

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
 * LaserOrdersServerDataGrid
 * @augments {Component<Props, State>}
 */
export class LaserOrdersServerDataGrid extends Component {
    static propTypes = {}

    static contextType = PrivilegesContext;

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

            role: null,

            page: 0,
            pagesLastOrderId: [0],

            lastOrderId: 0,

            deletingRowIds: [],

            isCreating: false,
            openCreationModal: false,
        };
    }

    componentDidMount() {
        this.setState({ role: this.context.retrieveOrder.laser[0] });
    }

    getRowCount() {
        return new Promise(async (resolve) => {
            let rowCount = await fetchData('get', '/ordersCount?businessName=regular&roleName=' + this.state.role, {}, { 'X-CSRF-TOKEN': this.state.token });
            resolve(rowCount.value);
        })
    }

    addColumns(columns) {
        columns.push({
            field: 'parts',
            headerName: translate('pages/orders/order/columns/parts'),
            description: translate('pages/orders/order/columns/parts'),
            renderCell: (params) => <PartsDataGridModal rows={params.row.parts} />,
        });

        columns.push({
            field: 'packages',
            headerName: translate('pages/orders/order/columns/packages'),
            description: translate('pages/orders/order/columns/packages'),
            renderCell: (params) => <PackagesDataGridModal rows={params.row.packages} />,
        });

        columns.push({
            field: 'price_with_discount',
            headerName: translate('pages/orders/order/columns/price_with_discount'),
            description: translate('pages/orders/order/columns/price_with_discount'),
            type: 'number',
            valueFormatter: formatToNumber,
        });

        if (this.context.deleteOrder !== undefined && this.context.deleteOrder.laser !== undefined && this.context.deleteOrder.laser.indexOf(this.state.role) !== -1) {
            columns.push({
                field: 'actions',
                description: 'actions',
                type: 'actions',
                headerName: translate('general/columns/action/plural/ucFirstLetterFirstWord'),
                width: 100,
                getActions: (params) => {
                    return [
                        <GridActionsCellItem icon={this.state.deletingRowIds.indexOf(params.row.id) === -1 ? <DeleteIcon /> : <CircularProgress size='2rem' />} onClick={async (e) => { this.handleDeletedRow(e, params); }} label="Delete" />,
                    ];
                },
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
        this.setState({ page: 0, pagesLastOrderId: [0], lastOrderId: 0, reload: true });
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
                    roleName={this.state.role ? this.context.retrieveOrder.laser[0].filter((v) => v !== 'self') : this.state.role}
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
                                        {(this.context.createOrder !== undefined && this.context.createOrder.laser !== undefined && this.context.createOrder.laser.filter((v) => v !== 'self').length > 0) ?
                                            (this.state.isCreating ?
                                                <LoadingButton loading variant='text' size='small' >
                                                    {translate('general/create/single/ucFirstLetterFirstWord')}
                                                </LoadingButton> :
                                                <Button variant='text' onClick={this.openCreationModal} size='small' startIcon={<AddIcon />}>
                                                    {translate('general/create/single/ucFirstLetterFirstWord')}
                                                </Button>
                                            ) : null
                                        }
                                        <Autocomplete
                                            sx={{ minWidth: '130px' }}
                                            size='small'
                                            disablePortal
                                            value={this.state.role ? this.context.retrieveOrder.laser[0].filter((v) => v !== 'self') : this.state.role}
                                            options={this.context.retrieveOrder.laser.filter((v) => v !== 'self')}
                                            onChange={(e) => {
                                                const elm = e.target;

                                                let v = '';
                                                if (elm.tagName === 'INPUT') {
                                                    v = elm.getAttribute('value');
                                                } else {
                                                    v = elm.innerText;
                                                }

                                                this.setState({ role: v, page: 0, pagesLastOrderId: [0], lastOrderId: 0, reload: true })
                                            }}
                                            renderInput={(params) => <TextField {...params} sx={{ mt: 0 }} label={translate('general/rule/plural/ucFirstLetterFirstWord')} variant='standard' />}
                                        />
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

                <Modal
                    open={this.state.openCreationModal}
                    onClose={this.closeCreationModal}
                >
                    <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                        <LaserOrderCreation onCreated={this.handleOnCreate} />
                    </Paper>
                </Modal>
            </>
        )
    }

    async handleDeletedRow(e, params) {
        if (!(this.context.deleteOrder !== undefined && this.context.deleteOrder.laser !== undefined && this.context.deleteOrder.laser.indexOf(params.row.role_name) !== -1)) {
            return;
        }

        let deletingRowIds = this.state.deletingRowIds;
        deletingRowIds.push(params.row.id);
        await updateState(this, { deletingRowIds: deletingRowIds });

        let data = {
            businessName: 'regular',
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

export default LaserOrdersServerDataGrid
