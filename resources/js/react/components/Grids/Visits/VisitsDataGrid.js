import React, { Component } from 'react'

import PropTypes from 'prop-types';

import { Button, Modal, Paper } from '@mui/material';

import DataGridComponent from '../DataGridComponent';
import { fetchData } from '../../Http/fetch';
import { translate } from '../../../traslation/translate';
import { formatToNumber, formatToTime } from '../formatters';
import WeekDayInputComponents from '../../Menus/Visits/WeekDayInputComponents';
import { convertWeekDays, getDateTimeFormatObject, resolveTimeZone } from '../../helpers';
import { LocaleContext } from '../../localeContext';
import { PrivilegesContext } from '../../privilegesContext';

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

        this.getData = this.getData.bind(this);
        this.collectColumns = this.collectColumns.bind(this);
        this.onPageChange = this.onPageChange.bind(this);
        this.onPageSizeChange = this.onPageSizeChange.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            pageSize: 10,

            openWeekDaysPeriods: [],
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
                    data = await fetchData('get', '/visits?businessName=' + this.props.businessName + '&roleName=' + this.props.roleName + '&timestamp=' + this.props.timestamp + '&sortByTimestamp=' + this.props.sort + '&operator=' + this.props.operator + '&count=' + this.state.pageSize + ((this.props.lastVisitTimestamp && this.props.lastVisitTimestamp !== 0) ? ('&lastVisitTimestamp=' + this.props.lastVisitTimestamp) : ''), {}, { 'X-CSRF-TOKEN': this.state.token });
                } else {
                    if (this.props.orderId !== undefined) {
                        data = await fetchData('get', '/visits?businessName=' + this.props.businessName + '&sortByTimestamp=' + this.props.sort + '&' + this.props.businessName + 'OrderId=' + this.props['orderId'], {}, { 'X-CSRF-TOKEN': this.state.token });
                    } else {
                        if (this.props.accountId !== undefined) {
                            data = await fetchData('get', '/visits?businessName=' + this.props.businessName + '&accountId=' + this.props.accountId + '&sortByTimestamp=' + this.props.sort, {}, { 'X-CSRF-TOKEN': this.state.token });
                        } else {
                            reject();
                        }
                    }
                }
            }

            if (data.response.status !== 200) {
                reject();
            }

            data = data.value;
            console.log(data);

            if (this.props.afterGetData !== undefined) {
                this.props.afterGetData(data);
            }

            resolve(data);
        });
    }

    collectColumns(rows) {
        if (rows.length === 0) {
            return [{ field: 'id' }];
        }

        let columns = [];
        for (const k in rows[0]) {
            if (Object.hasOwnProperty.call(rows[0], k)) {
                let column = {
                    field: k,
                };

                switch (k) {
                    case 'id':
                        column.headerName = translate('general/columns/' + k + '/single/ucFirstLetterFirstWord');
                        column.type = 'number';
                        column.valueFormatter = formatToNumber;
                        break;

                    case 'consuming_time':
                        column.headerName = translate('pages/visits/visit/columns/' + k);
                        column.type = 'number';
                        column.valueFormatter = formatToTime;
                        break;

                    case 'date_time_period':
                        column.headerName = translate('pages/visits/visit/columns/' + k);
                        column.valueGetter = ({ value }) => { if (!value) { return 'N/A'; } else { return value; } };
                        break;

                    case 'week_days_periods':
                        column.headerName = translate('pages/visits/visit/columns/' + k);
                        column.renderCell = (params) => {
                            const weekDaysPeriods = params.row.week_days_periods;
                            if (!weekDaysPeriods) {
                                return 'N/A';
                            }

                            const locale = LocaleContext._currentValue.currentLocale.shortName;
                            let weekDays = convertWeekDays(weekDaysPeriods, 'UTC', resolveTimeZone(locale));

                            let weekDayInputComponents = [];
                            if (weekDays !== null) {
                                for (const k in weekDays) {
                                    if (Object.hasOwnProperty.call(weekDays, k)) {
                                        weekDayInputComponents.push(weekDayInputComponents.length);
                                    }
                                }
                            }

                            let props = {};
                            if (weekDays !== null && weekDays.length !== 0 && weekDayInputComponents.length !== 0) {
                                props.weekDays = weekDays;
                                props.weekDayInputComponents = weekDayInputComponents;
                            }
                            console.log(props);

                            return (
                                <>
                                    <Button type='button' onClick={(e) => {
                                        let t = this.state.openWeekDaysPeriods;
                                        t[params.row.id] = true;
                                        this.setState({ openWeekDaysPeriods: t });
                                    }}>
                                        {translate('general/show/single/ucFirstLetterFirstWord')}
                                    </Button>

                                    <Modal
                                        open={this.state.openWeekDaysPeriods[params.row.id] !== undefined}
                                        onClose={() => {
                                            let t = this.state.openWeekDaysPeriods;
                                            delete t[params.row.id];
                                            this.setState({ openWeekDaysPeriods: t });
                                        }}
                                    >
                                        <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                                            <WeekDayInputComponents {...props} />
                                        </Paper>
                                    </Modal>
                                </>
                            );
                        };
                        break;

                    case 'visit_timestamp':
                        column.headerName = translate('pages/visits/visit/columns/' + k);
                        column.type = 'dateTime';
                        const locale = LocaleContext._currentValue.currentLocale.shortName;

                        column.valueGetter = (props) => { if (!props.value) { return null; } return getDateTimeFormatObject(locale).format(new Date(props.value * 1000)); };
                        column.minWidth = 330;
                        break;

                    case 'created_at':
                        column.headerName = translate('general/columns/' + k + '/single/ucFirstLetterFirstWord');
                        column.type = 'dateTime';
                        column.valueGetter = ({ value }) => { if (!value) { return ''; } return new Date(value); };
                        column.minWidth = 170;
                        break;

                    case 'updated_at':
                        column.headerName = translate('general/columns/' + k + '/single/ucFirstLetterFirstWord');
                        column.type = 'dateTime';
                        column.valueGetter = ({ value }) => { if (!value) { return ''; } return new Date(value); };
                        column.minWidth = 170;
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
        )
    }
}

export default VisitsDataGrid
