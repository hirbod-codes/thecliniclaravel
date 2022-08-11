import React, { Component } from 'react'

import PropTypes from 'prop-types';

import CloseIcon from '@mui/icons-material/Close';
import AddIcon from '@mui/icons-material/Add';
import DeleteIcon from '@mui/icons-material/Delete';
import { GridActionsCellItem, GridToolbarColumnsButton, GridToolbarContainer, GridToolbarDensitySelector, GridToolbarExport, GridToolbarFilterButton } from '@mui/x-data-grid';
import LoadingButton from '@mui/lab/LoadingButton';
import { Alert, Button, CircularProgress, IconButton, Modal, Paper, Snackbar, Stack } from '@mui/material';

import VisitsDataGrid from './VisitsDataGrid';
import { translate } from '../../../traslation/translate';
import { updateState } from '../../helpers';
import { fetchData } from '../../Http/fetch';
import WeekDayInputComponents from '../../Menus/Visits/WeekDayInputComponents';
import { PrivilegesContext } from '../../privilegesContext';

/**
 * AnOrderVisitsDataGrid
 * @augments {Component<Props, State>}
 */
export class AnOrderVisitsDataGrid extends Component {
    static contextType = PrivilegesContext;

    static propTypes = {
        roleName: PropTypes.string,
        businessName: PropTypes.string.isRequired,
        orderId: PropTypes.number.isRequired,
    }

    constructor(props) {
        super(props);

        this.handleFeedbackClose = this.handleFeedbackClose.bind(this);
        this.closeCreationModal = this.closeCreationModal.bind(this);
        this.openCreationModal = this.openCreationModal.bind(this);

        this.addColumns = this.addColumns.bind(this);

        this.handleOnCreate = this.handleOnCreate.bind(this);
        this.handleDeletedRow = this.handleDeletedRow.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            reload: false,

            feedbackOpen: false,
            feedbackMessage: '',
            feedbackColor: 'info',

            deletingRowIds: [],

            isCreating: false,
            openCreationModal: false,
        };
    }

    addColumns(columns) {
        if (this.context.deleteVisit !== undefined && this.context.deleteVisit[this.props.businessName] !== undefined && this.context.deleteVisit[this.props.businessName].filter((v) => v !== 'self').length > 0) {
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
                <VisitsDataGrid
                    reload={this.state.reload}
                    afterReload={() => this.setState({ reload: false })}

                    businessName={this.props.businessName}
                    orderId={this.props.orderId}
                    sort={'asc'}

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
                                        {(this.context.createVisit !== undefined && this.context.createVisit[this.props.businessName] !== undefined && this.context.createVisit[this.props.businessName].filter((v) => v !== 'self').length > 0) ?
                                            (this.state.isCreating ?
                                                <LoadingButton loading variant='text' size='small' >
                                                    {translate('general/create/single/ucFirstLetterFirstWord')}
                                                </LoadingButton> :
                                                <Button variant='text' onClick={this.openCreationModal} size='small' startIcon={<AddIcon />}>
                                                    {translate('general/create/single/ucFirstLetterFirstWord')}
                                                </Button>
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

                <Modal
                    open={this.state.openCreationModal}
                    onClose={this.closeCreationModal}
                >
                    <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                        <WeekDayInputComponents handleVisitInfo={this.handleOnCreate} />
                    </Paper>
                </Modal>
            </>
        )
    }

    async handleDeletedRow(e, params) {
        if (!(this.context.deleteVisit !== undefined && this.context.deleteVisit[this.props.businessName] !== undefined && this.context.deleteVisit[this.props.businessName].filter((v) => v !== 'self').length > 0)) {
            return;
        }

        let deletingRowIds = this.state.deletingRowIds;
        deletingRowIds.push(params.row.id);
        await updateState(this, { deletingRowIds: deletingRowIds });

        let data = {};
        data[this.props.businessName + 'OrderId'] = params.row.id;

        let r = await fetchData('delete', '/visit/' + this.props.businessName + '/', data, { 'X-CSRF-TOKEN': this.state.token })

        deletingRowIds = this.state.deletingRowIds;
        delete deletingRowIds[deletingRowIds.indexOf(params.row.id)];
        updateState(this, { deletingRowIds: deletingRowIds });

        if (r.response.status === 200) {
            this.setState({ reload: true, feedbackOpen: true, feedbackMessage: translate('general/successful/single/ucFirstLetterFirstWord'), feedbackColor: 'success' });
        } else {
            this.setState({ feedbackOpen: true, feedbackMessage: translate('general/failure/single/ucFirstLetterFirstWord'), feedbackColor: 'error' });
        }
    }

    async handleOnCreate(weekDaysPeriods = null) {
        this.closeCreationModal();

        if (!(this.context.createVisit !== undefined && this.context.createVisit[this.props.businessName] !== undefined && this.context.createVisit[this.props.businessName].filter((v) => v !== 'self').length > 0)) {
            return;
        }

        this.setState({ isCreating: true });

        let data = {};
        data[this.props.businessName + 'OrderId'] = this.props.orderId;
        if (weekDaysPeriods !== null) {
            let computedWeekDaysPeriods = {};
            for (let i = 0; i < weekDaysPeriods.length; i++) {
                const weekDaysPeriod = weekDaysPeriods[i];

                computedWeekDaysPeriods[weekDaysPeriod.weekDay] = weekDaysPeriod.timePeriods;
            }

            data.weekDaysPeriods = computedWeekDaysPeriods;
        }

        let r = await fetchData('post', '/visit/' + this.props.businessName, data, { 'X-CSRF-TOKEN': this.state.token }).then((res) => { if (res.status !== 200) { return null; } return res.json(); });

        if (r.response.status === 200) {
            this.setState({ reload: true, feedbackOpen: true, feedbackMessage: translate('general/successful/single/ucFirstLetterFirstWord'), feedbackColor: 'success' });
        } else {
            this.setState({ feedbackOpen: true, feedbackMessage: translate('general/failure/single/ucFirstLetterFirstWord'), feedbackColor: 'error' });
        }

        this.setState({ isCreating: false });
    }
}

export default AnOrderVisitsDataGrid
