import React, { Component } from 'react'

import CloseIcon from '@mui/icons-material/Close';
import AddIcon from '@mui/icons-material/Add';
import DeleteIcon from '@mui/icons-material/Delete';
import { GridActionsCellItem, GridToolbarColumnsButton, GridToolbarContainer, GridToolbarDensitySelector, GridToolbarExport, GridToolbarFilterButton } from '@mui/x-data-grid';
import { Alert, Autocomplete, Button, CircularProgress, Divider, IconButton, Modal, Paper, Snackbar, Stack, TextField } from '@mui/material';
import LoadingButton from '@mui/lab/LoadingButton';

import OrdersDataGrid from './OrdersDataGrid';
import { translate } from '../../../traslation/translate';
import FindAccount from '../../Menus/Account/FindAccount';
import { updateState } from '../../helpers';
import { delete_order, get_ordersCount, post_order } from '../../Http/Api/order';
import { connect } from 'react-redux';
import store from '../../../../redux/store';
import { canCreateOrder, canDeleteOrder, canEditRegularOrderNeededTime, canEditRegularOrderPrice } from '../../roles/order';

/**
 * RegularOrdersServerDataGrid
 * @augments {Component<Props, State>}
 */
export class RegularOrdersServerDataGrid extends Component {
    constructor(props) {
        super(props);

        this.handleFeedbackClose = this.handleFeedbackClose.bind(this);
        this.closeFindAccountModal = this.closeFindAccountModal.bind(this);
        this.openFindAccountModal = this.openFindAccountModal.bind(this);

        this.onPageChange = this.onPageChange.bind(this);
        this.onPageSizeChange = this.onPageSizeChange.bind(this);

        this.getRowCount = this.getRowCount.bind(this);
        this.addColumns = this.addColumns.bind(this);

        this.handleOnCreate = this.handleOnCreate.bind(this);
        this.handleDeletedRow = this.handleDeletedRow.bind(this);

        // this.handlePrice = this.handlePrice.bind(this);
        // this.handleTimeConsumption = this.handleTimeConsumption.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            feedbackMessages: [],

            reload: false,

            role: store.getState().role.roles.retrieveOrder.regular.filter((v) => v !== 'self')[0],

            page: 0,
            pagesLastOrderId: [0],

            lastOrderId: 0,

            deletingRowIds: [],

            openCreationModal: false,
            openFindAccountModal: false,
            isCreating: false,
            price: '',
            timeConsumption: '',
        };
    }

    getRowCount() {
        return new Promise(async (resolve, reject) => {
            let rowCount = await get_ordersCount('regular', this.state.role, this.state.token);
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
        if (canDeleteOrder(this.state.role, 'regular', store)) {
            columns.push({
                field: 'actions',
                description: translate('general/columns/action/plural/ucFirstLetterFirstWord'),
                type: 'actions',
                headerName: translate('general/columns/action/plural/ucFirstLetterFirstWord'),
                width: 100,
                getActions: (params) => {
                    return [
                        <GridActionsCellItem icon={this.state.deletingRowIds.indexOf(params.row.id) === -1 ? <DeleteIcon /> : <CircularProgress size='2rem' />} onClick={async (e) => { this.handleDeletedRow(e, params); }} label="Delete" />,
                    ]
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
        this.setState({ page: 0, pages: [0], lastOrderId: 0, reload: true });
    }

    handleFeedbackClose(event, reason, key) {
        if (reason === 'clickaway') {
            return;
        }

        let feedbackMessages = this.state.feedbackMessages;
        feedbackMessages[key].open = false;
        this.setState({ feedbackMessages: feedbackMessages });
    }

    closeFindAccountModal(e) {
        this.setState({ isCreating: false, openFindAccountModal: false });
    }

    openFindAccountModal(e) {
        this.setState({ isCreating: true, openFindAccountModal: true });
    }

    render() {
        return (
            <>
                <TextField onInput={this.handlePrice} sx={{ m: 1 }} size='small' type='number' variant='standard' label={translate('pages/orders/order/columns/price')} value={this.state.price} />
                <OrdersDataGrid
                    roleName={this.state.role}
                    businessName='regular'

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
                                        {canCreateOrder(this.state.role, 'regular', store) &&
                                            (this.state.isCreating ?
                                                <LoadingButton loading variant='text' size='small' >
                                                    {translate('general/create/single/ucFirstLetterFirstWord')}
                                                </LoadingButton> :
                                                <Button onClick={(e) => {
                                                    if (!canEditRegularOrderPrice(this.state.role, store) && !canEditRegularOrderNeededTime(this.state.role, store)) {
                                                        this.setState({ openFindAccountModal: true });
                                                    } else {
                                                        this.setState({ openCreationModal: true });
                                                    }
                                                }} startIcon={<AddIcon />}>
                                                    {translate('general/create/single/ucFirstLetterFirstWord')}
                                                </Button>
                                            )
                                        }
                                        <Autocomplete
                                            sx={{ minWidth: '130px' }}
                                            size='small'
                                            disablePortal
                                            value={this.state.role}
                                            options={store.getState().role.roles.retrieveOrder.regular.filter((v) => v !== 'self')}
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

                <Modal open={this.state.openCreationModal} onClose={() => this.setState({ openCreationModal: false })}>
                    <Paper sx={{
                        position: 'absolute',
                        top: '50%',
                        left: '50%',
                        transform: 'translate(-50%, -50%)',
                        minWidth: '50vw'
                    }}>
                        <Stack>
                            {canEditRegularOrderPrice(this.state.role, store) &&
                                <TextField onInput={(e) => { this.setState({ price: e.target.value }, () => console.log('this.price', this.state.price)) }} sx={{ m: 1 }} size='small' type='number' variant='standard' label={translate('pages/orders/order/columns/price')} value={this.state.price} />
                            }
                            {canEditRegularOrderNeededTime(this.state.role, store) &&
                                <TextField onInput={(e) => this.setState({ timeConsumption: e.target.value })} sx={{ m: 1 }} size='small' type='number' variant='standard' label={translate('pages/orders/order/columns/needed_time')} value={this.state.timeConsumption} />
                            }
                            <Divider />
                            <Button variant='text' onClick={(e) => this.setState({ openCreationModal: false, openFindAccountModal: true })} size='small' >
                                {translate('general/create/single/ucFirstLetterFirstWord')}
                            </Button>
                        </Stack>
                    </Paper>
                </Modal>

                <Modal
                    open={this.state.openFindAccountModal}
                    onClose={this.closeFindAccountModal}
                >
                    <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                        <FindAccount handleAccount={this.handleOnCreate} />
                    </Paper>
                </Modal>
            </>
        )
    }

    async handleOnCreate(account) {
        this.closeFindAccountModal();

        this.setState({ isCreating: true });

        let result = await post_order(
            account.id,
            'regular',
            null,
            null,
            this.state.price !== '' ? this.state.price : null,
            this.state.timeConsumption !== '' ? this.state.timeConsumption : null,
            this.state.token);
        if (result.response.status === 200) {
            this.setState({ reload: true });
        }

        let value = null;
        if (Array.isArray(result.value)) { value = result.value; } else { value = [result.value]; }
        value = value.map((v, i) => { return { open: true, message: v, color: result.response.status === 200 ? 'success' : 'error' } });
        this.setState({ feedbackMessages: value });

        this.setState({ isCreating: false });
    }

    async handleDeletedRow(e, params) {
        let deletingRowIds = this.state.deletingRowIds;
        deletingRowIds.push(params.row.id);
        await updateState(this, { deletingRowIds: deletingRowIds });

        let r = await delete_order('regular', params.row.id, this.state.token);

        deletingRowIds = this.state.deletingRowIds;
        delete deletingRowIds[deletingRowIds.indexOf(params.row.id)];
        updateState(this, { deletingRowIds: deletingRowIds, reload: true });

        let value = null;
        if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
        value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
        this.setState({ feedbackMessages: value });

        if (r.response.status === 200) {
            this.setState({ reload: true });
        }
    }
}

const mapStateToProps = state => ({
    redux: state
});

export default connect(mapStateToProps)(RegularOrdersServerDataGrid)
