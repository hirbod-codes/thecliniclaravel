import React, { Component } from 'react'

import PropTypes from 'prop-types';

import CloseIcon from '@mui/icons-material/Close';
import AddIcon from '@mui/icons-material/Add';
import DeleteIcon from '@mui/icons-material/Delete';
import { GridActionsCellItem, GridToolbarColumnsButton, GridToolbarContainer, GridToolbarDensitySelector, GridToolbarExport, GridToolbarFilterButton } from '@mui/x-data-grid';
import { Alert, Button, CircularProgress, IconButton, Modal, Paper, Snackbar, Stack, TextField } from '@mui/material';
import LoadingButton from '@mui/lab/LoadingButton';

import OrdersDataGrid from './OrdersDataGrid';
import { translate } from '../../../traslation/translate';
import FindAccount from '../../Menus/Account/FindAccount';
import { fetchData } from '../../Http/fetch';
import { updateState } from '../../helpers';

/**
 * RegularOrdersServerDataGrid
 * @augments {Component<Props, State>}
 */
export class RegularOrdersServerDataGrid extends Component {
    static propTypes = {
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

        this.handlePrice = this.handlePrice.bind(this);
        this.handleTimeConsumption = this.handleTimeConsumption.bind(this);

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

            openCreationModal: false,
            isCreating: false,
            price: '',
            timeConsumption: '',
        };
    }

    getRowCount() {
        return new Promise(async (resolve) => {
            let rowCount = await fetchData('get', '/orders/count/regular', { 'X-CSRF-TOKEN': this.state.token });
            resolve(rowCount.value);
        })
    }

    addColumns(columns) {
        if (this.props.privileges.regularOrderDelete) {
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

    handlePrice(e) {
        this.setState({ price: e.target.value });
    }

    handleTimeConsumption(e) {
        this.setState({ timeConsumption: e.target.value });
    }

    render() {
        return (
            <>
                <OrdersDataGrid
                    privileges={this.props.privileges}
                    businessName='regular'

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
                                        {this.props.privileges.regularOrderCreate ?
                                            (this.state.isCreating ?
                                                <LoadingButton loading variant='text' size='small' >
                                                    {translate('general/create/single/ucFirstLetterFirstWord')}
                                                </LoadingButton> :
                                                <>
                                                    <TextField onInput={this.handlePrice} sx={{ m: 1 }} size='small' type='number' variant='standard' label={translate('pages/orders/order/columns/price')} value={this.state.price} />
                                                    <TextField onInput={this.handleTimeConsumption} sx={{ m: 1 }} size='small' type='number' variant='standard' label={translate('pages/orders/order/columns/neededTime')} value={this.state.timeConsumption} />
                                                    <Button variant='text' onClick={this.openCreationModal} size='small' startIcon={<AddIcon />}>
                                                        {translate('general/create/single/ucFirstLetterFirstWord')}
                                                    </Button>
                                                </>
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

                {this.props.privileges.regularOrderCreate &&
                    <Modal
                        open={this.state.openCreationModal}
                        onClose={this.closeCreationModal}
                    >
                        <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                            <FindAccount handleAccount={this.handleOnCreate} />
                        </Paper>
                    </Modal>
                }
            </>
        )
    }

    async handleOnCreate(account) {
        this.closeCreationModal();

        if (!this.props.privileges.regularOrderCreate) {
            return;
        }

        this.setState({ isCreating: true });

        let data = {
            accountId: account.id,
            businessName: 'regular',
        };

        if (this.state.price) {
            data.price = Number(this.state.price);
        }

        if (this.state.timeConsumption) {
            data.timeConsumption = Number(this.state.timeConsumption);
        }

        let result = await fetchData('post', '/order', data, { 'X-CSRF-TOKEN': this.state.token });

        if (result.response.status === 200) {
            this.setState({ reload: true, feedbackOpen: true, feedbackMessage: translate('general/successful/single/ucFirstLetterFirstWord'), feedbackColor: 'success' });
        } else {
            this.setState({ feedbackOpen: true, feedbackMessage: translate('general/failure/single/ucFirstLetterFirstWord'), feedbackColor: 'error' });
        }

        this.setState({ isCreating: false });
    }

    async handleDeletedRow(params) {
        if (!this.props.privileges.regularOrderDelete) {
            return;
        }

        let deletingRowIds = this.state.deletingRowIds;
        deletingRowIds.push(params.row.id);
        await updateState(this, { deletingRowIds: deletingRowIds });

        let r = await fetchData('delete', '/orders/regular/' + params.row.userId + '/' + params.row.id, {}, { 'X-CSRF-TOKEN': this.state.token });

        deletingRowIds = this.state.deletingRowIds;
        delete deletingRowIds[deletingRowIds.indexOf(params.row.id)];
        updateState(this, { deletingRowIds: deletingRowIds, reload: true });

        if (r.response.status === 200) {
            this.setState({ reload: true, feedbackOpen: true, feedbackMessage: translate('general/successful/single/ucFirstLetterFirstWord'), feedbackColor: 'success' });
        } else {
            this.setState({ feedbackOpen: true, feedbackMessage: translate('general/failure/single/ucFirstLetterFirstWord'), feedbackColor: 'error' });
        }
    }
}

export default RegularOrdersServerDataGrid
