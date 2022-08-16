import React, { Component } from 'react'

import PropTypes from 'prop-types';

import UpdateIcon from '@mui/icons-material/Update';
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
import Account from '../../Menus/Account/Account';
import { PrivilegesContext } from '../../privilegesContext';
import { LocaleContext } from '../../localeContext';

/**
 * AccountsServerDataGrid
 * @augments {Component<Props, State>}
 */
export class AccountsServerDataGrid extends Component {
    static propTypes = {
        account: PropTypes.object.isRequired,
        roles: PropTypes.arrayOf(PropTypes.string).isRequired,
    }

    static contextType = PrivilegesContext;

    constructor(props) {
        super(props);

        this.handleFeedbackClose = this.handleFeedbackClose.bind(this);
        this.closeCreationModal = this.closeCreationModal.bind(this);
        this.openCreationModal = this.openCreationModal.bind(this);
        this.closeUpdationModal = this.closeUpdationModal.bind(this);
        this.openUpdationModal = this.openUpdationModal.bind(this);

        this.onPageChange = this.onPageChange.bind(this);
        this.onPageSizeChange = this.onPageSizeChange.bind(this);

        this.getDataType = this.getDataType.bind(this);
        this.getRowCount = this.getRowCount.bind(this);
        this.addColumns = this.addColumns.bind(this);

        this.handleOnCreate = this.handleOnCreate.bind(this);
        this.handleDeletedRow = this.handleDeletedRow.bind(this);
        this.handleUpdatedRow = this.handleUpdatedRow.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            feedbackMessages: [],

            role: this.props.roles[0],
            dataType: '',

            reload: false,

            page: 0,
            pagesAccountId: [0],

            lastAccountId: 0,

            isCreatable: false,

            deletingRowIds: [],
            isDeletable: false,

            updatingRow: null,
            updatingRowIds: [],
            isUpdatable: false,
            openUpdationModal: false,
            updatableColumns: [],

            isCreating: false,
            openCreationModal: false,

            locale: LocaleContext._currentValue.currentLocale.shortName,
        };
    }

    componentDidMount() {
        this.getDataType();
    }

    getRowCount() {
        return new Promise(async (resolve, reject) => {
            let rowCount = await fetchData('get', '/accountsCount?roleName=' + this.state.role, {}, { 'X-CSRF-TOKEN': this.state.token });
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

    async getDataType() {
        let r = await fetchData('get', '/dataType?roleName=' + this.state.role, {}, { 'X-CSRF-TOKEN': this.state.token });

        if (r.response.status !== 200) {
            let value = null;
            if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
            value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
            this.setState({ feedbackMessages: value });
            return;
        }
        this.setState({ dataType: r.value });
    }

    addColumns(columns) {
        let isDeletable, isUpdatable = false;

        if (this.context.deleteUser !== undefined && this.context.deleteUser.length > 0 && this.context.deleteUser.indexOf(this.state.role) !== -1) { isDeletable = true; }
        if (this.context.updatableColumns !== undefined && this.context.updatableColumns[this.state.role] !== undefined && this.context.updatableColumns[this.state.role].length > 0) { isUpdatable = true; }

        if (!isDeletable && !isUpdatable) {
            return columns;
        }

        let getActions = (params) => {
            let getActionsArray = [];
            if (isDeletable) {
                getActionsArray.push(
                    <GridActionsCellItem icon={this.state.deletingRowIds.indexOf(params.row.id) === -1 ? <DeleteIcon /> : <CircularProgress size='2rem' />} onClick={async (e) => { this.handleDeletedRow(e, params); }} label="Delete" />
                );
            }
            if (isUpdatable) {
                getActionsArray.push(
                    <GridActionsCellItem icon={this.state.updatingRowIds.indexOf(params.row.id) === -1 ? <UpdateIcon /> : <CircularProgress size='2rem' />} onClick={async (e) => { await updateState(this, { updatingRow: params.row }); this.openUpdationModal(); }} label="Update" />
                );
            }

            return getActionsArray;
        };

        columns.push({
            field: 'actions',
            description: 'actions',
            type: 'actions',
            headerName: translate('general/columns/action/plural/ucFirstLetterFirstWord', this.state.locale),
            width: 100,
            getActions: getActions,
        });

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

    closeUpdationModal(e) {
        this.setState({ isUpdating: false, openUpdationModal: false });
    }

    openUpdationModal(e) {
        this.setState({ isUpdating: true, openUpdationModal: true });
    }

    render() {
        return (
            <>
                <AccountsDataGrid
                    role={this.state.role}

                    account={this.props.account}

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
                                        {(this.context.createUser !== undefined && this.context.createUser.indexOf(this.state.role) !== -1) ?
                                            (!this.state.isCreating ?
                                                <Button variant='text' onClick={this.openCreationModal} size='small' startIcon={<AddIcon />}>{translate('general/create/single/ucFirstLetterFirstWord')}</Button>
                                                :
                                                <LoadingButton loading variant='text' size='small' >{translate('general/create/single/ucFirstLetterFirstWord')}</LoadingButton>
                                            ) : null
                                        }
                                        <Autocomplete
                                            sx={{ minWidth: '130px' }}
                                            size='small'
                                            disablePortal
                                            value={this.state.role}
                                            options={this.props.roles}
                                            onChange={(e) => {
                                                const elm = e.target;

                                                let v = '';
                                                if (elm.tagName === 'INPUT') {
                                                    v = elm.getAttribute('value');
                                                } else {
                                                    v = elm.innerText;
                                                }

                                                this.setState({ role: v, page: 0, pagesAccountId: [0], lastAccountId: 0, reload: true })
                                            }}
                                            renderInput={(params) => <TextField {...params} sx={{ mt: 0 }} label={translate('general/rule/plural/ucFirstLetterFirstWord')} variant='standard' />}
                                        />
                                    </Stack>
                                </GridToolbarContainer>
                        }
                    }}

                    lastAccountId={this.state.pagesAccountId[this.state.page]}
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

                {(this.context.createUser !== undefined && this.context.createUser.indexOf(this.state.role) !== -1) &&
                    <Modal
                        open={this.state.openCreationModal}
                        onClose={this.closeCreationModal}
                    >
                        <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', overflowY: 'auto', p: 1 }}>
                            <AccountCreator dataType={this.state.dataType} onSuccess={() => { this.handleOnCreate(); }} rules={this.context.createUser.map((v, i) => v === 'self' ? this.context.role : v)} />
                        </Paper>
                    </Modal>
                }

                {this.context.updatableColumns !== undefined && this.context.updatableColumns[this.state.role] !== undefined && this.context.updatableColumns[this.state.role].length > 0 &&
                    <Modal
                        open={this.state.openUpdationModal}
                        onClose={this.closeUpdationModal}
                    >
                        <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1, overflowY: 'auto' }}>
                            <Account onUpdateSuccess={() => { this.closeUpdationModal(); this.setState({ reload: true }); }} account={this.state.updatingRow} accountRole={this.context.role} />
                        </Paper>
                    </Modal>
                }
            </>
        )
    }

    async handleDeletedRow(e, params) {
        if (!(this.context.deleteUser !== undefined && this.context.deleteUser.indexOf(this.state.role) !== -1)) {
            return;
        }

        let deletingRowIds = this.state.deletingRowIds;
        deletingRowIds.push(params.row.id);
        await updateState(this, { deletingRowIds: deletingRowIds });

        let r = await fetchData('delete', '/account/' + params.row.id, {}, { 'X-CSRF-TOKEN': this.state.token });
        deletingRowIds = this.state.deletingRowIds;
        delete deletingRowIds[deletingRowIds.indexOf(params.row.id)];
        updateState(this, { deletingRowIds: deletingRowIds });

        let value = null;
        if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
        value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
        this.setState({ reload: true, feedbackMessages: value });
    }

    async handleUpdatedRow(e, params) {
        this.closeUpdationModal();
        this.setState({ reload: true, feedbackMessages: [{ open: true, color: 'success', message: translate('general/successful/single/ucFirstLetterFirstWord') }] });
    }

    async handleOnCreate(e) {
        this.closeCreationModal();
        this.setState({ reload: true, feedbackMessages: [{ open: true, color: 'success', message: translate('general/successful/single/ucFirstLetterFirstWord') }] });
    }
}

export default AccountsServerDataGrid
