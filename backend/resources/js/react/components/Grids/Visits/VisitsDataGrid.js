import React, { Component } from 'react'

import PropTypes from 'prop-types';

import CloseIcon from '@mui/icons-material/Close';
import { Alert, Button, IconButton, Modal, Paper, Snackbar } from '@mui/material';

import DataGridComponent from '../DataGridComponent';
import { translate } from '../../../traslation/translate';
import { formatToNumber, formatToTime } from '../formatters';
import { convertWeeklyTimePatterns, localizeDate, resolveTimeZone } from '../../helpers';
import { LocaleContext } from '../../localeContext';
import { PrivilegesContext } from '../../privilegesContext';
import { DateTime } from 'luxon';
import WeeklyTimePatterns from '../../Menus/Time/WeeklyTimePatterns';
import { get_visits_by_order, get_visits_by_timestamp, get_visits_by_user } from '../../Http/Api/visits';

/**
 * VisitsDataGrid
 * @augments {Component<Props, State>}
 */
export class VisitsDataGrid extends Component {
    static contextType = PrivilegesContext;

    static propTypes = {
        businessName: PropTypes.string.isRequired,

        roleName: PropTypes.string,
        sort: PropTypes.oneOf(['asc', 'desc']),
        timestamp: PropTypes.number,
        operator: PropTypes.string,
        lastVisitId: PropTypes.number,
        count: PropTypes.number,
        orderId: PropTypes.number,
        accountId: PropTypes.number,

        onPageChange: PropTypes.func,
        onPageSizeChange: PropTypes.func,

        getData: PropTypes.func,
        afterGetData: PropTypes.func,
        getRowCount: PropTypes.func,
        collectColumns: PropTypes.func,
        addColumns: PropTypes.func,

        reload: PropTypes.bool,
        beforeReload: PropTypes.func,
        afterReload: PropTypes.func,

        paginationMode: PropTypes.oneOf(['server', 'client']),
        gridProps: PropTypes.object,

        checkboxSelection: PropTypes.bool,
        onSelectionModelChange: PropTypes.func,
        selectionModel: PropTypes.arrayOf(PropTypes.number),
    }

    constructor(props) {
        super(props);

        this.handleFeedbackClose = this.handleFeedbackClose.bind(this);

        this.getData = this.getData.bind(this);
        this.collectColumns = this.collectColumns.bind(this);
        this.onPageChange = this.onPageChange.bind(this);
        this.onPageSizeChange = this.onPageSizeChange.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            feedbackMessages: [],

            pageSize: 10,

            openWeeklyTimePatterns: [],

            locale: LocaleContext._currentValue.currentLocale.shortName,
        };
    }

    getData() {
        return new Promise(async (resolve, reject) => {
            let data = [];
            if (Object.hasOwnProperty.call(this.props, 'rows')) {
                resolve(this.props.rows);
                return;
            } else {
                if (this.props.timestamp !== undefined && this.props.operator !== undefined) {
                    data = await get_visits_by_timestamp(
                        this.props.businessName,
                        this.props.roleName,
                        this.props.sort,
                        this.props.timestamp,
                        this.props.operator,
                        this.state.pageSize,
                        this.props.lastVisitTimestamp,
                        this.state.token,
                    );
                } else {
                    if (this.props.orderId !== undefined) {
                        data = await get_visits_by_order(
                            this.props.businessName,
                            this.props.sort,
                            this.props.orderId,
                            this.state.token,
                        );
                    } else {
                        if (this.props.accountId !== undefined) {
                            data = await get_visits_by_user(
                                this.props.businessName,
                                this.props.sort,
                                this.props.accountId,
                                this.state.token,
                            );
                        } else {
                            reject();
                        }
                    }
                }
            }

            if (data.response.status !== 200) {
                let value = null;
                if (Array.isArray(data.value)) { value = data.value; } else { value = [data.value]; }
                value = value.map((v, i) => { return { open: true, message: v, color: data.response.status === 200 ? 'success' : 'error' } });
                this.setState({ feedbackMessages: value });
                reject();
            }

            data = data.value;

            if (this.props.afterGetData !== undefined) {
                this.props.afterGetData(data);
            }

            resolve(data);
        });
    }

    handleFeedbackClose(event, reason, key) {
        if (reason === 'clickaway') {
            return;
        }

        let feedbackMessages = this.state.feedbackMessages;
        feedbackMessages[key].open = false;
        this.setState({ feedbackMessages: feedbackMessages });
    }

    collectColumns(rows) {
        if (rows.length === 0) {
            return [{ field: 'id' }];
        }

        const locale = this.state.locale;
        let columns = [];
        for (const k in rows[0]) {
            if (Object.hasOwnProperty.call(rows[0], k)) {
                let column = {
                    field: k,
                };

                switch (k) {
                    case 'id':
                        column.headerName = translate('general/columns/' + k + '/single/ucFirstLetterFirstWord', this.state.locale);
                        column.type = 'number';
                        column.valueFormatter = formatToNumber;
                        break;

                    case 'consuming_time':
                        column.headerName = translate('pages/visits/visit/columns/' + k, this.state.locale);
                        column.type = 'number';
                        column.valueFormatter = formatToTime;
                        break;

                    case 'date_time_period':
                        column.headerName = translate('pages/visits/visit/columns/' + k, this.state.locale);
                        column.valueFormatter = ({ value }) => { if (!value) { return 'N/A'; } else { return value; } };
                        break;

                    case 'weekly_time_patterns':
                        column.headerName = translate('pages/visits/visit/columns/' + k, this.state.locale);
                        column.renderCell = (params) => {
                            const weeklyTimePatterns = params.row.weekly_time_patterns;
                            if (!weeklyTimePatterns) {
                                return 'N/A';
                            }

                            let convertedWeeklyTimePatterns = convertWeeklyTimePatterns(weeklyTimePatterns, 'UTC', resolveTimeZone(locale), "HH:mm:ss");

                            let props = {};
                            if (convertedWeeklyTimePatterns !== null) {
                                props.weeklyTimePatterns = Object.entries(convertedWeeklyTimePatterns);
                            }

                            return (
                                <>
                                    <Button type='button' onClick={(e) => {
                                        let t = this.state.openWeeklyTimePatterns;
                                        t[params.row.id] = true;
                                        this.setState({ openWeeklyTimePatterns: t });
                                    }}>
                                        {translate('general/show/single/ucFirstLetterFirstWord')}
                                    </Button>

                                    <Modal
                                        open={this.state.openWeeklyTimePatterns[params.row.id] !== undefined}
                                        onClose={() => {
                                            let t = this.state.openWeeklyTimePatterns;
                                            delete t[params.row.id];
                                            this.setState({ openWeeklyTimePatterns: t });
                                        }}
                                    >
                                        <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                                            <WeeklyTimePatterns {...props} />
                                        </Paper>
                                    </Modal>
                                </>
                            );
                        };
                        break;

                    case 'visit_timestamp':
                        column.headerName = translate('pages/visits/visit/columns/' + k, this.state.locale);
                        column.type = 'dateTime';
                        column.valueFormatter = (props) => { if (!props.value) { return null; } return localizeDate('utc', DateTime.fromSeconds(Number(props.value), { zone: 'utc' }).toISO(), locale, true); };
                        column.minWidth = 330;
                        break;

                    case 'created_at':
                        column.headerName = translate('general/columns/' + k + '/single/ucFirstLetterFirstWord', this.state.locale);
                        column.type = 'dateTime';
                        column.valueFormatter = (props) => { if (!props.value) { return null; } return localizeDate('utc', props.value, locale, true); };
                        column.minWidth = 200;
                        break;

                    case 'updated_at':
                        column.headerName = translate('general/columns/' + k + '/single/ucFirstLetterFirstWord', this.state.locale);
                        column.type = 'dateTime';
                        column.valueFormatter = (props) => { if (!props.value) { return null; } return localizeDate('utc', props.value, locale, true); };
                        column.minWidth = 200;
                        break;

                    default:
                        continue;
                }

                column.description = column.headerName;
                columns.push(column);
            }
        }

        if (this.props.addColumns !== undefined) {
            columns = this.props.addColumns(columns);
        }

        return columns;
    }

    onPageChange(newPage) {
        if (this.props.onPageChange !== undefined) {
            this.props.onPageChange(newPage);
        }
    }

    onPageSizeChange(newPageSize) {
        this.setState({ pageSize: newPageSize });

        if (this.props.onPageSizeChange !== undefined) {
            this.props.onPageSizeChange(newPageSize);
        }
    }

    render() {
        let props = {};
        if (this.props.gridProps !== undefined) {
            props.gridProps = this.props.gridProps;
        }

        if (this.props.checkboxSelection !== undefined && (this.props.onSelectionModelChange !== undefined || this.props.selectionModel !== undefined)) {
            if (props.gridProps === undefined) {
                props.gridProps = {};
            }

            props.gridProps.checkboxSelection = this.props.checkboxSelection;

            if (this.props.onSelectionModelChange !== undefined) {
                props.gridProps.onSelectionModelChange = this.props.onSelectionModelChange;
            }

            if (this.props.selectionModel !== undefined) {
                props.gridProps.selectionModel = this.props.selectionModel;
            }
        }

        if (this.props.getRowCount !== undefined) {
            props.getRowCount = this.props.getRowCount;
        }

        return (
            <>
                <DataGridComponent
                    paginationMode={(this.props.paginationMode !== undefined) ? this.props.paginationMode : 'client'}

                    reload={(this.props.reload !== undefined) ? this.props.reload : false}
                    beforeReload={(this.props.beforeReload !== undefined) ? this.props.beforeReload : () => { }}
                    afterReload={(this.props.afterReload !== undefined) ? this.props.afterReload : () => { }}

                    getData={(this.props.getData !== undefined) ? this.props.getData : this.getData}
                    collectColumns={(this.props.collectColumns !== undefined) ? this.props.collectColumns : this.collectColumns}

                    onPageChange={this.onPageChange}
                    onPageSizeChange={this.onPageSizeChange}
                    rowsPerPageOptions={[10, 20, 30]}
                    {...props}
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
}

export default VisitsDataGrid
