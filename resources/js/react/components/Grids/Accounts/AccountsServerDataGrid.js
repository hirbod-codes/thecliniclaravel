import React, { Component } from 'react'

import PropTypes from 'prop-types';

import AddIcon from '@mui/icons-material/Add';
import DeleteIcon from '@mui/icons-material/Delete';
import CloseIcon from '@mui/icons-material/Close';
import AccountsDataGrid from './AccountsDataGrid';
import { Alert, Autocomplete, Button, CircularProgress, IconButton, Modal, Paper, Snackbar, Stack, TextField } from '@mui/material';

import { fetchData } from '../../Http/fetch';
import { GridActionsCellItem, GridToolbarColumnsButton, GridToolbarContainer, GridToolbarDensitySelector, GridToolbarExport, GridToolbarFilterButton } from '@mui/x-data-grid';
import { translate } from '../../../traslation/translate';
import LoadingButton from '@mui/lab/LoadingButton';
import AccountCreator from '../../Menus/Account/AccountCreator';
import { updateState } from '../../helpers';

/**
 * AccountsServerDataGrid
 * @augments {Component<Props, State>}
 */
export class AccountsServerDataGrid extends Component {
    static propTypes = {
        account: PropTypes.object.isRequired,
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

            rule: 'patient',

            reload: false,

            page: 0,
            pagesAccountId: [0],

            lastAccountId: 0,

            deletingRowIds: [],

            isCreating: false,
            openCreationModal: false,
        };
    }

    getRowCount() {
        return new Promise(async (resolve) => {
            let rowCount = await fetchData('get', '/accountsCount/' + this.state.rule, {}, { 'X-CSRF-TOKEN': this.state.token });
            resolve(rowCount.value);
        })
    }

    addColumns(columns) {
        if (this.props.privileges.accountDelete) {
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
        if (this.state.pagesAccountId[newPage] !== undefined) {
            this.setState({ reload: true });
        } else {
            let pagesAccountId = this.state.pagesAccountId;
            pagesAccountId.push(this.state.lastAccountId);
            this.setState({ pagesAccountId: pagesAccountId, reload: true });
        }
    }

    onPageSizeChange(newPageSize) {
        this.setState({ page: 0, pagesAccountId: [0], lastAccountId: 0, reload: true });
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
                <AccountsDataGrid
                    role={this.state.rule}

                    account={this.props.account}
                    privileges={this.props.privileges}

                    paginationMode='server'

                    afterGetData={(data) => { if (data.length === undefined) { return; } this.setState({ lastAccountId: data[data.length - 1].id }); }}
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
                                    <Stack direction='row' spacing={1}>
                                        <GridToolbarColumnsButton />
                                        <GridToolbarFilterButton />
                                        <GridToolbarDensitySelector />
                                        <GridToolbarExport />
                                        {(this.props.privileges.accountCreate && !this.state.isCreating) ?
                                            <Button variant='text' onClick={this.openCreationModal} size='small' startIcon={<AddIcon />}>{translate('general/create/single/ucFirstLetterFirstWord')}</Button>
                                            :
                                            <LoadingButton loading variant='text' size='small' >{translate('general/create/single/ucFirstLetterFirstWord')}</LoadingButton>
                                        }
                                        <Autocomplete
                                            sx={{ minWidth: '130px' }}
                                            size='small'
                                            disablePortal
                                            value={this.state.rule}
                                            options={['admin', 'doctor', 'secretary', 'operator', 'patient']}
                                            onChange={(e) => {
                                                const elm = e.target;

                                                let v = '';
                                                if (elm.tagName === 'INPUT') {
                                                    v = elm.getAttribute('value');
                                                } else {
                                                    v = elm.innerText;
                                                }

                                                this.setState({ rule: v, page: 0, pagesAccountId: [0], lastAccountId: 0, reload: true })
                                            }}
                                            renderInput={(params) => <TextField {...params} sx={{ mt: 0 }} label={translate('general/rule/plural/ucFirstLetterFirstWord')} variant='standard' />}
                                        />
                                    </Stack>
                                </GridToolbarContainer>
                        }
                    }}

                    lastAccountId={this.state.pagesAccountId[this.state.page]}
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

                {this.props.privileges.accountCreate &&
                    <Modal
                        open={this.state.openCreationModal}
                        onClose={this.closeCreationModal}
                    >
                        <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                            <AccountCreator onSuccess={() => { this.closeCreationModal(); }} />
                        </Paper>
                    </Modal>
                }
            </>
        )
    }

    async handleDeletedRow(e, params) {
        if (!this.props.privileges.accountDelete) {
            return;
        }

        let deletingRowIds = this.state.deletingRowIds;
        deletingRowIds.push(params.row.id);
        await updateState(this, { deletingRowIds: deletingRowIds });

        fetchData('delete', '/account/' + params.row.id, {}, { 'X-CSRF-TOKEN': this.state.token })
            .then((res) => {
                let deletingRowIds = this.state.deletingRowIds;
                delete deletingRowIds[deletingRowIds.indexOf(params.row.id)];
                updateState(this, { deletingRowIds: deletingRowIds });
                if (res.status === 200) {
                    this.setState({ reload: true, feedbackOpen: true, feedbackMessage: translate('general/successful/single/ucFirstLetterFirstWord'), feedbackColor: 'success' });
                } else {
                    this.setState({ feedbackOpen: true, feedbackMessage: translate('general/failure/single/ucFirstLetterFirstWord'), feedbackColor: 'error' });
                }
            });
    }

    async handleOnCreate(e) {
        this.closeCreationModal();
        this.setState({ reload: true, feedbackOpen: true, feedbackMessage: translate('general/successful/single/ucFirstLetterFirstWord'), feedbackColor: 'success' });
    }
}

export default AccountsServerDataGrid
