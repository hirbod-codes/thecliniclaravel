import React, { Component } from 'react'

import PropTypes from 'prop-types';

import CloseIcon from '@mui/icons-material/Close';
import AddIcon from '@mui/icons-material/Add';
import DeleteIcon from '@mui/icons-material/Delete';
import { Alert, IconButton, Snackbar, Modal, Paper, CircularProgress, Button, Stack } from '@mui/material';
import LoadingButton from '@mui/lab/LoadingButton';
import { GridActionsCellItem, GridToolbarColumnsButton, GridToolbarContainer, GridToolbarDensitySelector, GridToolbarExport, GridToolbarFilterButton } from '@mui/x-data-grid';

import VisitsDataGrid from './VisitsDataGrid';
import { translate, ucFirstLetterFirstWord } from '../../../traslation/translate';
import { fetchData } from '../../Http/fetch';
import { updateState } from '../../helpers';
import VisitCreator from '../../Menus/Visits/VisitCreator';

/**
 * SelfVisitsDataGrid
 * @augments {Component<Props, State>}
 */
export class SelfVisitsDataGrid extends Component {
    static propTypes = {
        currentLocaleName: PropTypes.string.isRequired,
        privileges: PropTypes.object.isRequired,
        account: PropTypes.object.isRequired,
        businessName: PropTypes.string.isRequired,
    }

    constructor(props) {
        super(props);

        this.handleFeedbackClose = this.handleFeedbackClose.bind(this);

        this.closeVisitCreatorModal = this.closeVisitCreatorModal.bind(this);
        this.openVisitCreatorModal = this.openVisitCreatorModal.bind(this);

        this.addColumns = this.addColumns.bind(this);

        this.handleDeletedRow = this.handleDeletedRow.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            reload: false,

            feedbackOpen: false,
            feedbackMessage: '',
            feedbackColor: 'info',

            deletingRowIds: [],

            openVisitCreatorModal: false,
            isCreating: false,
        }
    }

    addColumns(columns) {
        if (this.props.privileges['self' + ucFirstLetterFirstWord(this.props.businessName) + 'VisitDelete']) {
            columns.push({
                field: 'actions',
                type: 'actions',
                headerName: translate('general/columns/action/plural/ucFirstLetterFirstWord', this.props.currentLocaleName),
                width: 100,
                getActions: (params) => [
                    <GridActionsCellItem icon={this.state.deletingRowIds.indexOf(params.row.id) === -1 ? <DeleteIcon /> : <CircularProgress size='2rem' />} onClick={async (e) => { this.handleDeletedRow(e, params); }} label="Delete" />,
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

    closeVisitCreatorModal(e) {
        this.setState({ openVisitCreatorModal: false });
    }

    openVisitCreatorModal(e) {
        this.setState({ openVisitCreatorModal: true });
    }

    render() {
        return (
            <>
                <VisitsDataGrid
                    currentLocaleName={this.props.currentLocaleName}

                    businessName={this.props.businessName}
                    accountId={this.props.account.id}
                    sort={'asc'}

                    reload={this.state.reload}
                    afterReload={() => this.setState({ reload: false })}

                    addColumns={this.addColumns}

                    gridProps={{
                        components: {
                            Toolbar: () =>
                                <GridToolbarContainer>
                                    <Stack direction='row'>
                                        <GridToolbarColumnsButton />
                                        <GridToolbarFilterButton />
                                        <GridToolbarDensitySelector />
                                        <GridToolbarExport />
                                        {this.props.privileges['self' + ucFirstLetterFirstWord(this.props.businessName) + 'VisitCreate'] ?
                                            (this.state.isCreating ?
                                                <LoadingButton loading variant='text' size='small' >
                                                    {translate('general/create/single/ucFirstLetterFirstWord', this.props.currentLocaleName)}
                                                </LoadingButton> :
                                                <Button variant='text' onClick={this.openVisitCreatorModal} size='small' startIcon={<AddIcon />}>
                                                    {translate('general/create/single/ucFirstLetterFirstWord', this.props.currentLocaleName)}
                                                </Button>
                                            ) : null
                                        }
                                    </Stack>
                                </GridToolbarContainer>
                        }
                    }}
                />

                {this.props.privileges['self' + ucFirstLetterFirstWord(this.props.businessName) + 'VisitCreate'] &&
                    <Modal
                        open={this.state.openVisitCreatorModal}
                        onClose={this.closeVisitCreatorModal}
                    >
                        <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                            <VisitCreator
                                account={this.props.account}

                                onSuccess={() => {
                                    this.closeVisitCreatorModal();
                                    this.setState({ isCreating: false, reload: true, feedbackOpen: true, feedbackMessage: translate('general/successful/single/ucFirstLetterFirstWord', this.props.currentLocaleName), feedbackColor: 'success' });
                                }}
                                onClose={() => {
                                    this.setState({ isCreating: false });
                                    this.closeVisitCreatorModal();
                                }}
                                onFailure={() => {
                                    this.setState({ isCreating: false, feedbackOpen: true, feedbackMessage: translate('general/failure/single/ucFirstLetterFirstWord', this.props.currentLocaleName), feedbackColor: 'error' });
                                }}

                                privileges={this.props.privileges} businessName={this.props.businessName} currentLocaleName={this.props.currentLocaleName}
                            />
                        </Paper>
                    </Modal>
                }

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
        if (!this.props.privileges['self' + ucFirstLetterFirstWord(this.props.businessName) + 'VisitDelete']) {
            return;
        }

        let deletingRowIds = this.state.deletingRowIds;
        deletingRowIds.push(params.row.id);
        await updateState(this, { deletingRowIds: deletingRowIds });

        let r = await fetchData('delete', '/visit/' + this.props.businessName + '/' + params.row.id, {}, { 'X-CSRF-TOKEN': this.state.token });

        deletingRowIds = this.state.deletingRowIds;
        delete deletingRowIds[deletingRowIds.indexOf(params.row.id)];
        updateState(this, { deletingRowIds: deletingRowIds });

        if (r.response.status === 200) {
            this.setState({ reload: true, feedbackOpen: true, feedbackMessage: translate('general/successful/single/ucFirstLetterFirstWord'), feedbackColor: 'success' });
        } else {
            this.setState({ feedbackOpen: true, feedbackMessage: translate('general/failure/single/ucFirstLetterFirstWord'), feedbackColor: 'error' });
        }
    }
}

export default SelfVisitsDataGrid
