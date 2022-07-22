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
import { deleteJsonData, postJsonData } from '../../Http/fetch';
import { updateState } from '../../helpers';

/**
 * SelfRegularOrdersDataGrid
 * @augments {Component<Props, State>}
 */
export class SelfRegularOrdersDataGrid extends Component {
    static propTypes = {
        currentLocaleName: PropTypes.string.isRequired,
        privileges: PropTypes.object.isRequired,
        account: PropTypes.object.isRequired,
    }

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

            feedbackOpen: false,
            feedbackMessage: '',
            feedbackColor: 'info',

            reload: false,

            deletingRowIds: [],

            isCreating: false,
            price: '',
            timeConsumption: '',
        };
    }

    addColumns(columns) {
        if (this.props.privileges.selfRegularOrderDelete) {
            columns.push({
                field: 'actions',
                description: 'actions',
                type: 'actions',
                headerName: translate('general/columns/action/plural/ucFirstLetterFirstWord', this.props.currentLocaleName),
                width: 100,
                getActions: (params) => [
                    <GridActionsCellItem icon={this.state.deletingRowIds.indexOf(params.row.id) === -1 ? <DeleteIcon /> : <CircularProgress size='2rem' />} onClick={async (e) => { this.handleDeletedRow(params); }} label="Delete" />,
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
                    currentLocaleName={this.props.currentLocaleName}

                    privileges={this.props.privileges}
                    businessName='regular'

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
                                        {this.props.privileges.selfRegularOrderCreate ?
                                            (this.state.isCreating ?
                                                <LoadingButton loading variant='text' size='small' >
                                                    {translate('general/create/single/ucFirstLetterFirstWord', this.props.currentLocaleName)}
                                                </LoadingButton> :
                                                <>
                                                    <TextField onInput={this.handlePrice} sx={{ m: 1 }} size='small' type='text' variant='standard' label={translate('pages/orders/order/columns/price', this.props.currentLocaleName)} value={this.state.price} />
                                                    <TextField onInput={this.handleTimeConsumption} sx={{ m: 1 }} size='small' type='text' variant='standard' label={translate('pages/orders/order/columns/neededTime', this.props.currentLocaleName)} value={this.state.timeConsumption} />
                                                    <Button variant='text' onClick={(e) => this.handleOnCreate(this.props.account)} size='small' startIcon={<AddIcon />}>
                                                        {translate('general/create/single/ucFirstLetterFirstWord', this.props.currentLocaleName)}
                                                    </Button>
                                                </>
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
            </>
        )
    }

    async handleOnCreate(account) {
        if (!this.props.privileges.selfRegularOrderCreate) {
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

        let result = await postJsonData('/order', data, { 'X-CSRF-TOKEN': this.state.token }).then((res) => { if (res.status !== 200) { return null; } return res.json(); });

        if (result) {
            this.setState({ reload: true, feedbackOpen: true, feedbackMessage: translate('general/successful/single/ucFirstLetterFirstWord', this.props.currentLocaleName), feedbackColor: 'success' });
        } else {
            this.setState({ feedbackOpen: true, feedbackMessage: translate('general/failure/single/ucFirstLetterFirstWord', this.props.currentLocaleName), feedbackColor: 'error' });
        }

        this.setState({ isCreating: false });
    }

    async handleDeletedRow(params) {
        if (!this.props.privileges.selfRegularOrderDelete) {
            return;
        }

        let deletingRowIds = this.state.deletingRowIds;
        deletingRowIds.push(params.row.id);
        await updateState(this, { deletingRowIds: deletingRowIds });

        deleteJsonData('/orders/regular/' + this.props.account.id + '/' + params.row.id, {}, { 'X-CSRF-TOKEN': this.state.token })
            .then((res) => {
                let deletingRowIds = this.state.deletingRowIds;
                delete deletingRowIds[deletingRowIds.indexOf(params.row.id)];
                updateState(this, { deletingRowIds: deletingRowIds, reload: true });
                if (res.status === 200) {
                    this.setState({ reload: true, feedbackOpen: true, feedbackMessage: translate('general/successful/single/ucFirstLetterFirstWord', this.props.currentLocaleName), feedbackColor: 'success' });
                } else {
                    this.setState({ feedbackOpen: true, feedbackMessage: translate('general/failure/single/ucFirstLetterFirstWord', this.props.currentLocaleName), feedbackColor: 'error' });
                }
            });
    }
}

export default SelfRegularOrdersDataGrid
