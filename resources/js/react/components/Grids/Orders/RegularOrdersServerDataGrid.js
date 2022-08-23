import React, { Component } from 'react'

// import PropTypes from 'prop-types';

import CloseIcon from '@mui/icons-material/Close';
import AddIcon from '@mui/icons-material/Add';
import DeleteIcon from '@mui/icons-material/Delete';
import { GridActionsCellItem, GridToolbarColumnsButton, GridToolbarContainer, GridToolbarDensitySelector, GridToolbarExport, GridToolbarFilterButton } from '@mui/x-data-grid';
import { Alert, Autocomplete, Button, CircularProgress, IconButton, Modal, Paper, Snackbar, Stack, TextField } from '@mui/material';
import LoadingButton from '@mui/lab/LoadingButton';

import OrdersDataGrid from './OrdersDataGrid';
import { translate } from '../../../traslation/translate';
import FindAccount from '../../Menus/Account/FindAccount';
import { fetchData } from '../../Http/fetch';
import { updateState } from '../../helpers';
import { PrivilegesContext } from '../../privilegesContext';
import { LocaleContext } from '../../localeContext';

/**
 * RegularOrdersServerDataGrid
 * @augments {Component<Props, State>}
 */
export class RegularOrdersServerDataGrid extends Component {
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

        this.handlePrice = this.handlePrice.bind(this);
        this.handleTimeConsumption = this.handleTimeConsumption.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            feedbackMessages: [],

            reload: false,

            role: null,

            page: 0,
            pagesLastOrderId: [0],

            lastOrderId: 0,

            deletingRowIds: [],

            openCreationModal: false,
            isCreating: false,
            price: '',
            timeConsumption: '',

            locale: LocaleContext._currentValue.currentLocale.shortName,
        };
    }

    componentDidMount() {
        this.setState({ role: this.context.retrieveOrder.laser[0] });
    }

    getRowCount() {
        return new Promise(async (resolve, reject) => {
            let rowCount = await fetchData('get', '/ordersCount?businessName=regular&roleName=' + this.state.role, { 'X-CSRF-TOKEN': this.state.token });
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
        if (this.context.deleteOrder !== undefined && this.context.deleteOrder.regular !== undefined && this.context.deleteOrder.regular.indexOf(this.state.role) !== -1) {
            columns.push({
                field: 'actions',
                description: translate('general/columns/action/plural/ucFirstLetterFirstWord', this.state.locale),
                type: 'actions',
                headerName: translate('general/columns/action/plural/ucFirstLetterFirstWord', this.state.locale),
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
                    roleName={this.state.role === null ? this.context.retrieveOrder.regular.filter((v) => v !== 'self')[0] : this.state.role}
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
                                        {(this.context.createOrder !== undefined && this.context.createOrder.regular !== undefined && this.context.createOrder.regular.filter((v) => v !== 'self').length > 0) ?
                                            (this.state.isCreating ?
                                                <LoadingButton loading variant='text' size='small' >
                                                    {translate('general/create/single/ucFirstLetterFirstWord')}
                                                </LoadingButton> :
                                                <>
                                                    {this.context.privileges !== undefined && this.context.privileges.editRegularOrderPrice !== undefined && this.context.privileges.editRegularOrderNeededTime !== undefined &&
                                                        <>
                                                            <TextField onInput={this.handlePrice} sx={{ m: 1 }} size='small' type='number' variant='standard' label={translate('pages/orders/order/columns/price')} value={this.state.price} />
                                                            <TextField onInput={this.handleTimeConsumption} sx={{ m: 1 }} size='small' type='number' variant='standard' label={translate('pages/orders/order/columns/needed_time')} value={this.state.timeConsumption} />
                                                        </>
                                                    }
                                                    <Button variant='text' onClick={this.openCreationModal} size='small' startIcon={<AddIcon />}>
                                                        {translate('general/create/single/ucFirstLetterFirstWord')}
                                                    </Button>
                                                </>
                                            ) : null
                                        }
                                        <Autocomplete
                                            sx={{ minWidth: '130px' }}
                                            size='small'
                                            disablePortal
                                            value={this.state.role === null ? this.context.retrieveOrder.regular.filter((v) => v !== 'self')[0] : this.state.role}
                                            options={this.context.retrieveOrder.regular.filter((v) => v !== 'self')}
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

                <Modal
                    open={this.state.openCreationModal}
                    onClose={this.closeCreationModal}
                >
                    <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                        <FindAccount handleAccount={this.handleOnCreate} />
                    </Paper>
                </Modal>
            </>
        )
    }

    async handleOnCreate(account) {
        this.closeCreationModal();

        if (!(this.context.createOrder !== undefined && this.context.createOrder.regular !== undefined && this.context.createOrder.regular.filter((v) => v !== 'self').length > 0)) {
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
            this.setState({ reload: true });
        }

        let value = null;
        if (Array.isArray(result.value)) { value = result.value; } else { value = [result.value]; }
        value = value.map((v, i) => { return { open: true, message: v, color: result.response.status === 200 ? 'success' : 'error' } });
        this.setState({ feedbackMessages: value });

        this.setState({ isCreating: false });
    }

    async handleDeletedRow(params) {
        if (!(this.context.deleteOrder !== undefined && this.context.deleteOrder.regular !== undefined && this.context.deleteOrder.regular.indexOf(params.row.role_name) !== -1)) { return []; }

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

export default RegularOrdersServerDataGrid
