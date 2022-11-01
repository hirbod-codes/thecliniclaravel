import React, { Component } from 'react'

import PropTypes from 'prop-types';

import CloseIcon from '@mui/icons-material/Close';
import AddIcon from '@mui/icons-material/Add';
import DeleteIcon from '@mui/icons-material/Delete';
import { GridActionsCellItem, GridToolbarColumnsButton, GridToolbarContainer, GridToolbarDensitySelector, GridToolbarExport, GridToolbarFilterButton } from '@mui/x-data-grid';
import { Alert, Button, CircularProgress, IconButton, Snackbar, Stack, TextField } from '@mui/material';
import LoadingButton from '@mui/lab/LoadingButton';

import OrdersDataGrid from './OrdersDataGrid';
import { translate } from '../../../traslation/translate';
import { updateState } from '../../helpers';
import { PrivilegesContext } from '../../privilegesContext';
import { LocaleContext } from '../../localeContext';
import { delete_order, post_order } from '../../Http/Api/order';

/**
 * SelfRegularOrdersDataGrid
 * @augments {Component<Props, State>}
 */
export class SelfRegularOrdersDataGrid extends Component {
    static propTypes = {
        account: PropTypes.object.isRequired,
    }

    static contextType = PrivilegesContext;

    constructor(props) {
        super(props);

        this.handleFeedbackClose = this.handleFeedbackClose.bind(this);

        this.addColumns = this.addColumns.bind(this);

        this.handleOnCreate = this.handleOnCreate.bind(this);
        this.handleDeletedRow = this.handleDeletedRow.bind(this);

        this.handlePrice = this.handlePrice.bind(this);
        this.handleTimeConsumption = this.handleTimeConsumption.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            feedbackMessages: [],

            reload: false,

            deletingRowIds: [],

            isCreating: false,
            price: '',
            timeConsumption: '',

            locale: LocaleContext._currentValue.currentLocale.shortName,
        };
    }

    addColumns(columns) {
        if (this.context.deleteOrder !== undefined && this.context.deleteOrder.regular !== undefined && this.context.deleteOrder.regular.indexOf('self') !== -1) {
            columns.push({
                field: 'actions',
                description: 'actions',
                type: 'actions',
                headerName: translate('general/columns/action/plural/ucFirstLetterFirstWord', this.state.locale),
                width: 100,
                getActions: (params) => [
                    <GridActionsCellItem icon={this.state.deletingRowIds.indexOf(params.row.id) === -1 ? <DeleteIcon /> : <CircularProgress size='2rem' />} onClick={async (e) => { this.handleDeletedRow(params); }} label="Delete" />,
                ],
            });
        }
        return columns;
    }

    handleFeedbackClose(event, reason, key) {
        if (reason === 'clickaway') {
            return;
        }

        let feedbackMessages = this.state.feedbackMessages;
        feedbackMessages[key].open = false;
        this.setState({ feedbackMessages: feedbackMessages });
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
                    businessName='regular'

                    username={this.props.account.username}

                    addColumns={this.addColumns}

                    reload={this.state.reload}
                    afterReload={() => this.setState({ reload: false })}

                    gridProps={{
                        components: {
                            Toolbar: () =>
                                <GridToolbarContainer>
                                    <Stack direction='row' spacing={1} flexWrap={'wrap'}>
                                        <GridToolbarColumnsButton />
                                        <GridToolbarFilterButton />
                                        <GridToolbarDensitySelector />
                                        <GridToolbarExport />
                                        {(this.context.createOrder !== undefined && this.context.createOrder.regular !== undefined && this.context.createOrder.regular.indexOf('self') !== -1) ?
                                            (this.state.isCreating ?
                                                <LoadingButton loading variant='text' size='small' >
                                                    {translate('general/create/single/ucFirstLetterFirstWord')}
                                                </LoadingButton> :
                                                <>
                                                    {this.context.privileges !== undefined && this.context.privileges.editRegularOrderPrice !== undefined && this.context.privileges.editRegularOrderNeededTime !== undefined &&
                                                        <>
                                                            <TextField onInput={this.handlePrice} sx={{ m: 1 }} size='small' type='text' variant='standard' label={translate('pages/orders/order/columns/price')} value={this.state.price} />
                                                            <TextField onInput={this.handleTimeConsumption} sx={{ m: 1 }} size='small' type='text' variant='standard' label={translate('pages/orders/order/columns/needed_time')} value={this.state.timeConsumption} />
                                                        </>
                                                    }
                                                    <Button variant='text' onClick={(e) => this.handleOnCreate(this.props.account)} size='small' startIcon={<AddIcon />}>
                                                        {translate('general/create/single/ucFirstLetterFirstWord')}
                                                    </Button>
                                                </>
                                            ) : null
                                        }
                                    </Stack>
                                </GridToolbarContainer>
                        }
                    }}
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
            </>
        )
    }

    async handleOnCreate(account) {
        if (!(this.context.createOrder !== undefined && this.context.createOrder.regular !== undefined && this.context.createOrder.regular.indexOf('self') !== -1)) {
            return;
        }

        this.setState({ isCreating: true });

        let r = await post_order(
            account.id,
            'regular',
            null,
            null,
            this.state.price !== undefined ? this.state.price : null,
            this.state.timeConsumption !== undefined ? this.state.timeConsumption : null,
            this.state.token);
        let value = null;
        if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
        value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
        this.setState({ feedbackMessages: value });

        if (r.response.status === 200) {
            this.setState({ reload: true });
        }

        this.setState({ isCreating: false });
    }

    async handleDeletedRow(params) {
        if (!(this.context.deleteOrder !== undefined && this.context.deleteOrder.regular !== undefined && this.context.deleteOrder.regular.indexOf('self') !== -1)) {
            return;
        }

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

export default SelfRegularOrdersDataGrid
