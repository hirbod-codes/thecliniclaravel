import React, { Component } from 'react'

import CloseIcon from '@mui/icons-material/Close';
import AddIcon from '@mui/icons-material/Add';
import DeleteIcon from '@mui/icons-material/Delete';
import { GridActionsCellItem, GridToolbarColumnsButton, GridToolbarContainer, GridToolbarDensitySelector, GridToolbarExport, GridToolbarFilterButton } from '@mui/x-data-grid';
import { Alert, Autocomplete, Button, CircularProgress, IconButton, Modal, Paper, Snackbar, Stack, TextField } from '@mui/material';

import OrdersDataGrid from './OrdersDataGrid'
import { translate } from '../../../traslation/translate';
import { updateState } from '../../helpers';
import LaserOrderCreation from '../../Menus/Orders/LaserOrderCreation';
import LoadingButton from '@mui/lab/LoadingButton';
import { formatToNumber } from '../formatters';
import PartsDataGridModal from './Modals/PartsDataGridModal';
import PackagesDataGridModal from './Modals/PackagesDataGridModal';
import { delete_order, get_ordersCount } from '../../Http/Api/order';
import { connect } from 'react-redux';
import { canCreateOrder, canDeleteOrder } from '../../roles/order';
import store from '../../../../redux/store';

/**
 * LaserOrdersServerDataGrid
 * @augments {Component<Props, State>}
 */
export class LaserOrdersServerDataGrid extends Component {
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

            feedbackMessages: [],

            reload: false,

            role: store.getState().role.roles.retrieveOrder.laser.filter((v) => v !== 'self')[0],

            page: 0,
            pagesLastOrderId: [0],

            lastOrderId: 0,

            deletingRowIds: [],

            isCreating: false,
            openCreationModal: false,
        };
    }

    getRowCount() {
        return new Promise(async (resolve, reject) => {
            let rowCount = await get_ordersCount('laser', this.state.role, this.state.token);

            if (rowCount.response.status !== 200) {
                let value = null;
                if (Array.isArray(rowCount.value)) { value = rowCount.value; } else { value = [rowCount.value]; }
                value = value.map((v, i) => { return { open: true, message: v, color: rowCount.response.status === 200 ? 'success' : 'error' } });
                this.setState({ feedbackMessages: value });
                reject();
            }
            resolve(rowCount.value);
        })
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

        if (canDeleteOrder(this.state.role, 'laser', store)) {
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

    handleFeedbackClose(event, reason, key) {
        if (reason === 'clickaway') {
            return;
        }

        let feedbackMessages = this.state.feedbackMessages;
        feedbackMessages[key].open = false;
        this.setState({ feedbackMessages: feedbackMessages });
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
                    roleName={this.state.role}
                    businessName='laser'

                    paginationMode='server'

                    afterGetData={(data) => this.setState({ lastOrderId: (data.length !== undefined || data.length !== 0) ? data[data.length - 1].id : 0 })}
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
                                    <Stack direction='row' spacing={1} flexWrap={'wrap'}>
                                        <GridToolbarColumnsButton />
                                        <GridToolbarFilterButton />
                                        <GridToolbarDensitySelector />
                                        <GridToolbarExport />
                                        {(canCreateOrder(this.state.role, 'laser', store)) ?
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
                                            value={this.state.role}
                                            options={store.getState().role.roles.retrieveOrder.laser.filter((v) => v !== 'self')}
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

                <Modal
                    open={this.state.openCreationModal}
                    onClose={this.closeCreationModal}
                >
                    <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                        <LaserOrderCreation onCreated={this.handleOnCreate} />
                    </Paper>
                </Modal>

                {this.state.feedbackMessages.map((m, i) =>
                    <Snackbar
                        key={i}
                        open={m.open}
                        autoHideDuration={6000}
                        onClose={(e, r) => this.handleFeedbackClose(e, r, i)}
                        action={
                            <IconButton
                                size="small"
                                onClick={(e, r) => this.handleFeedbackClose(e, r, i)}
                            >
                                <CloseIcon fontSize="small" />
                            </IconButton>
                        }
                    >
                        <Alert onClose={(e, r) => this.handleFeedbackClose(e, r, i)} severity={m.color} sx={{ width: '100%' }}>
                            {m.message}
                        </Alert>
                    </Snackbar>
                )}
            </>
        )
    }

    async handleDeletedRow(e, params) {
        let deletingRowIds = this.state.deletingRowIds;
        deletingRowIds.push(params.row.id);
        await updateState(this, { deletingRowIds: deletingRowIds });

        let r = await delete_order('laser', params.row.id, this.state.token);

        deletingRowIds = this.state.deletingRowIds;
        delete deletingRowIds[deletingRowIds.indexOf(params.row.id)];
        updateState(this, { deletingRowIds: deletingRowIds });

        let value = null;
        if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
        value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
        this.setState({ feedbackMessages: value });

        if (r.response.status === 200) {
            this.setState({ reload: true });
        }
    }

    async handleOnCreate(e) {
        this.closeCreationModal();
        this.setState({ reload: true, feedbackMessages: [{ message: translate('general/successful/single/ucFirstLetterFirstWord'), color: 'success', open: true }] });
    }
}

const mapStateToProps = state => ({
    redux: state
});

export default connect(mapStateToProps)(LaserOrdersServerDataGrid)
