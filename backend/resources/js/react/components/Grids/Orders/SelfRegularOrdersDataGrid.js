import React, { Component } from 'react'

import CloseIcon from '@mui/icons-material/Close';
import AddIcon from '@mui/icons-material/Add';
import DeleteIcon from '@mui/icons-material/Delete';
import { GridActionsCellItem, GridToolbarColumnsButton, GridToolbarContainer, GridToolbarDensitySelector, GridToolbarExport, GridToolbarFilterButton } from '@mui/x-data-grid';
import { Alert, Button, CircularProgress, Divider, IconButton, Modal, Paper, Snackbar, Stack, TextField } from '@mui/material';
import LoadingButton from '@mui/lab/LoadingButton';

import OrdersDataGrid from './OrdersDataGrid';
import { translate } from '../../../traslation/translate';
import { updateState } from '../../helpers';
import { delete_order, post_order } from '../../Http/Api/order';
import { canCreateSelfOrder, canDeleteSelfOrder, canEditRegularOrderNeededTime, canEditRegularOrderPrice } from '../../roles/order';
import store from '../../../../redux/store';
import { connect } from 'react-redux';

/**
 * SelfRegularOrdersDataGrid
 * @augments {Component<Props, State>}
 */
export class SelfRegularOrdersDataGrid extends Component {
    constructor(props) {
        super(props);

        this.handleFeedbackClose = this.handleFeedbackClose.bind(this);

        this.addColumns = this.addColumns.bind(this);

        this.handleOnCreate = this.handleOnCreate.bind(this);
        this.handleDeletedRow = this.handleDeletedRow.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            feedbackMessages: [],

            reload: false,

            deletingRowIds: [],

            isCreating: false,
            openCreationMenu: false,
            price: '',
            timeConsumption: '',
        };

        this.price = '';
        this.timeConsumption = '';
    }

    addColumns(columns) {
        if (canDeleteSelfOrder('regular', store)) {
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

    handleFeedbackClose(event, reason, key) {
        if (reason === 'clickaway') {
            return;
        }

        let feedbackMessages = this.state.feedbackMessages;
        feedbackMessages[key].open = false;
        this.setState({ feedbackMessages: feedbackMessages });
    }

    render() {
        return (
            <>
                <OrdersDataGrid
                    businessName='regular'

                    username={this.props.redux.auth.account.username}

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
                                        {(canCreateSelfOrder('regular', store)) ?
                                            (this.state.isCreating ?
                                                <LoadingButton loading variant='text' size='small' >
                                                    {translate('general/create/single/ucFirstLetterFirstWord')}
                                                </LoadingButton> :
                                                <Button onClick={(e) => {
                                                    if (!canEditRegularOrderPrice('self', store) && !canEditRegularOrderNeededTime('self', store)) {
                                                        this.handleOnCreate(this.props.redux.auth.account);
                                                    } else {
                                                        this.setState({ openCreationModal: true });
                                                    }
                                                }} startIcon={<AddIcon />}>
                                                    {translate('general/create/single/ucFirstLetterFirstWord')}
                                                </Button>
                                            ) : null
                                        }
                                    </Stack>
                                </GridToolbarContainer>
                        }
                    }}
                />

                <Modal open={this.state.openCreationModal} onClose={() => this.setState({ openCreationModal: false })}>
                    <Paper sx={{
                        position: 'absolute',
                        top: '50%',
                        left: '50%',
                        transform: 'translate(-50%, -50%)',
                        minWidth: '50vw'
                    }}>
                        <Stack>
                            {canEditRegularOrderPrice('self', store) &&
                                <TextField onInput={(e) => { this.setState({ price: e.target.value }, () => console.log('this.price', this.price)) }} sx={{ m: 1 }} size='small' type='number' variant='standard' label={translate('pages/orders/order/columns/price')} value={this.state.price} />
                            }
                            {canEditRegularOrderNeededTime('self', store) &&
                                <TextField onInput={(e) => this.setState({ timeConsumption: e.target.value })} sx={{ m: 1 }} size='small' type='number' variant='standard' label={translate('pages/orders/order/columns/needed_time')} value={this.state.timeConsumption} />
                            }
                            <Divider />
                            <Button variant='text' onClick={(e) => { this.handleOnCreate(this.props.redux.auth.account); this.setState({ openCreationModal: false }); }} size='small' >
                                {translate('general/create/single/ucFirstLetterFirstWord')}
                            </Button>
                        </Stack>
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

    async handleOnCreate(account) {
        this.setState({ isCreating: true });

        let r = await post_order(
            account.id,
            'regular',
            null,
            null,
            this.state.price !== '' ? this.state.price : null,
            this.state.timeConsumption !== '' ? this.state.timeConsumption : null,
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

export default connect(mapStateToProps)(SelfRegularOrdersDataGrid)
