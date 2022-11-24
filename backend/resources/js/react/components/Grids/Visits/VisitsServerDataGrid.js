import React, { Component } from 'react'

import PropTypes from 'prop-types';

import CloseIcon from '@mui/icons-material/Close';
import AddIcon from '@mui/icons-material/Add';
import DeleteIcon from '@mui/icons-material/Delete';
import { Alert, IconButton, Snackbar, Modal, Paper, CircularProgress, Button, Stack, Autocomplete, TextField } from '@mui/material';
import { GridActionsCellItem, GridToolbarColumnsButton, GridToolbarContainer, GridToolbarDensitySelector, GridToolbarExport, GridToolbarFilterButton } from '@mui/x-data-grid';
import LoadingButton from '@mui/lab/LoadingButton';

import VisitsDataGrid from './VisitsDataGrid';
import { translate } from '../../../traslation/translate';
import { updateState } from '../../helpers';
import VisitCreator from '../../Menus/Visits/VisitCreator';
import { delete_visit_laser, delete_visit_regular, get_visitsCount } from '../../Http/Api/visits';
import store from '../../../../redux/store';
import { canCreateVisit, canDeleteVisit } from '../../roles/visit';
import { connect } from 'react-redux';

/**
 * VisitsServerDataGrid
 * @augments {Component<Props, State>}
 */
export class VisitsServerDataGrid extends Component {
    static propTypes = {
        businessName: PropTypes.string.isRequired,
    }

    constructor(props) {
        super(props);

        this.handleFeedbackClose = this.handleFeedbackClose.bind(this);
        this.closeVisitCreatorModal = this.closeVisitCreatorModal.bind(this);
        this.openVisitCreatorModal = this.openVisitCreatorModal.bind(this);

        this.onPageChange = this.onPageChange.bind(this);
        this.onPageSizeChange = this.onPageSizeChange.bind(this);

        this.getRowCount = this.getRowCount.bind(this);
        this.addColumns = this.addColumns.bind(this);

        this.handleDeletedRow = this.handleDeletedRow.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            reload: false,

            feedbackOpen: false,
            feedbackMessage: '',
            feedbackColor: 'info',

            role: store.getState().role.roles.retrieveVisit[this.props.businessName].filter((v) => v !== 'self')[0],

            page: 0,
            pagesLastVisitId: [0],

            deletingRowIds: [],

            openVisitCreatorModal: false,
            isCreating: false,

            visitFinderTabsValue: 0,

            isRefreshingClosestVisit: false,
            isSubmittingClosestVisit: false,
            closestVisitRefresh: null,

            isRefreshingWeeklyVisit: false,
            isSubmittingWeeklyVisit: false,
            weeklyVisitRefresh: null,
        }
    }

    getRowCount() {
        return new Promise(async (resolve, reject) => {
            let rowCount = await get_visitsCount(this.props.businessName, this.state.role, this.state.token);
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
        if (canDeleteVisit(this.state.role, this.props.businessName, store)) {
            columns.push({
                field: 'actions',
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
        if (this.state.pagesLastVisitId[newPage] !== undefined) {
            this.setState({ reload: true });
        } else {
            let pagesLastVisitId = this.state.pagesLastVisitId;
            pagesLastVisitId.push(this.state.lastVisitTimestamp);
            this.setState({ pagesLastVisitId: pagesLastVisitId, reload: true });
        }
    }

    onPageSizeChange(newPageSize) {
        this.setState({ page: 0, pagesLastVisitId: [0], lastVisitTimestamp: 0, reload: true });
    }

    handleFeedbackClose(event, reason) {
        if (reason === 'clickaway') {
            return;
        }

        this.setState({ feedbackOpen: false });
    }

    closeVisitCreatorModal(e, reason) {
        if (reason === 'backdropClick') {
            this.setState({ isCreating: false, openVisitCreatorModal: false });
            return;
        }

        this.setState({ openVisitCreatorModal: false });
    }

    openVisitCreatorModal(e) {
        this.setState({ openVisitCreatorModal: true });
    }

    render() {
        return (
            <>
                <VisitsDataGrid
                    paginationMode='server'

                    roleName={this.state.role}
                    businessName={this.props.businessName}
                    sort='asc'
                    operator='>='
                    timestamp={Math.floor(Date.parse(new Date()) / 1000)}
                    lastVisitTimestamp={this.state.pagesLastVisitId[this.state.page]}

                    afterGetData={(data) => this.setState({ lastVisitTimestamp: (data.length !== undefined || data.length !== 0) ? data[data.length - 1].visit_timestamp : 0 })}
                    getRowCount={this.getRowCount}
                    reload={this.state.reload}
                    afterReload={() => this.setState({ reload: false })}

                    onPageChange={this.onPageChange}
                    onPageSizeChange={this.onPageSizeChange}

                    addColumns={this.addColumns}

                    gridProps={{
                        components: {
                            Toolbar: () =>
                                <GridToolbarContainer>
                                    <Stack direction='row' spacing={1} flexWrap={'wrap'}>
                                        <GridToolbarColumnsButton />
                                        <GridToolbarFilterButton />
                                        <GridToolbarDensitySelector />
                                        <GridToolbarExport />
                                        {(canCreateVisit(this.state.role, this.props.businessName, store)) ?
                                            (this.state.isCreating ?
                                                <LoadingButton loading variant='text' size='small' >
                                                    {translate('general/create/single/ucFirstLetterFirstWord')}
                                                </LoadingButton> :
                                                <Button variant='text' onClick={this.openVisitCreatorModal} size='small' startIcon={<AddIcon />}>
                                                    {translate('general/create/single/ucFirstLetterFirstWord')}
                                                </Button>
                                            ) : null
                                        }
                                        <Autocomplete
                                            sx={{ minWidth: '130px' }}
                                            size='small'
                                            disablePortal
                                            value={this.state.role}
                                            options={store.getState().role.roles.retrieveVisit[this.props.businessName].filter((v) => v !== 'self')}
                                            onChange={(e) => {
                                                const elm = e.target;

                                                let v = '';
                                                if (elm.tagName === 'INPUT') {
                                                    v = elm.getAttribute('value');
                                                } else {
                                                    v = elm.innerText;
                                                }

                                                this.setState({ role: v, page: 0, pagesLastVisitId: [0], lastVisitTimestamp: 0, reload: true })
                                            }}
                                            renderInput={(params) => <TextField {...params} sx={{ mt: 0 }} label={translate('general/rule/plural/ucFirstLetterFirstWord')} variant='standard' />}
                                        />
                                    </Stack>
                                </GridToolbarContainer>
                        }
                    }}
                />

                <Modal
                    open={this.state.openVisitCreatorModal}
                    onClose={this.closeVisitCreatorModal}
                >
                    <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                        <VisitCreator
                            onSuccess={() => {
                                this.closeVisitCreatorModal();
                                this.setState({ isCreating: false, reload: true, feedbackOpen: true, feedbackMessage: translate('general/successful/single/ucFirstLetterFirstWord'), feedbackColor: 'success' });
                            }}
                            onClose={() => {
                                this.setState({ isCreating: false });
                                this.closeVisitCreatorModal();
                            }}
                            onFailure={() => {
                                this.setState({ isCreating: false, feedbackOpen: true, feedbackMessage: translate('general/failure/single/ucFirstLetterFirstWord'), feedbackColor: 'error' });
                            }}

                            businessName={this.props.businessName}
                            targetRoleName={this.state.role}
                        />
                    </Paper>
                </Modal>

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

    async handleDeletedRow(e, params) {
        let deletingRowIds = this.state.deletingRowIds;
        deletingRowIds.push(params.row.id);
        await updateState(this, { deletingRowIds: deletingRowIds });

        let r = null;
        switch (this.props.businessName) {
            case 'laser':
                r = await delete_visit_laser(params.row.id, this.state.token);
                break;

            case 'regular':
                r = await delete_visit_regular(params.row.id, this.state.token);
                break;

            default:
                break;
        }
        let value = null;
        if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
        value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
        this.setState({ feedbackMessages: value });

        deletingRowIds = this.state.deletingRowIds;
        delete deletingRowIds[deletingRowIds.indexOf(params.row.id)];
        updateState(this, { deletingRowIds: deletingRowIds });

        if (r.response.status === 200) {
            this.setState({ reload: true });
        }
    }
}

const mapStateToProps = state => ({
    redux: state
});

export default connect(mapStateToProps)(VisitsServerDataGrid)
