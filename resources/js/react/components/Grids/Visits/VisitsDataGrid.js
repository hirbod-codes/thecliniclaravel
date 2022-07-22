import React, { Component } from 'react'

import PropTypes from 'prop-types';

import DataGridComponent from '../DataGridComponent';
import { getJsonData } from '../../Http/fetch';
import { translate } from '../../../traslation/translate';
import { formatToNumber, formatToTime } from '../formatters';
import WeekDayInputComponents from '../../Menus/Visits/WeekDayInputComponents';
import { Button, Modal, Paper } from '@mui/material';
import { convertWeekDays, getDateTimeFormatObject, resolveTimeZone } from '../../helpers';

/**
 * VisitsDataGrid
 * @augments {Component<Props, State>}
 */
export class VisitsDataGrid extends Component {
    static propTypes = {
        currentLocaleName: PropTypes.string.isRequired,
        businessName: PropTypes.string.isRequired,

        sort: PropTypes.oneOf(['asc', 'desc']),
        timestamp: PropTypes.number,
        operator: PropTypes.string,
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
                    data = await getJsonData('/visits/' + this.props.businessName + '?timestamp=' + this.props.timestamp + '&sortByTimestamp=' + this.props.sort + '&operator=' + this.props.operator, { 'X-CSRF-TOKEN': this.state.token }).then((res) => res.json());
                } else {
                    if (this.props.orderId !== undefined) {
                        data = await getJsonData('/visits/' + this.props.businessName + '?sortByTimestamp=' + this.props.sort + '&' + this.props.businessName + 'OrderId=' + this.props['orderId'], { 'X-CSRF-TOKEN': this.state.token }).then((res) => res.json());
                    } else {
                        if (this.props.accountId !== undefined) {
                            data = await getJsonData('/visits/' + this.props.businessName + '?accountId=' + this.props.accountId + '&sortByTimestamp=' + this.props.sort, { 'X-CSRF-TOKEN': this.state.token }).then((res) => res.json());
                        } else {
                            reject();
                        }
                    }
                }
            }


            data = data.visits;

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
                        column.headerName = translate('general/columns/' + k + '/single/ucFirstLetterFirstWord', this.props.currentLocaleName);
                        column.type = 'number';
                        column.valueFormatter = formatToNumber;
                        break;

                    case 'consumingTime':
                        column.headerName = translate('pages/visits/visit/columns/' + k, this.props.currentLocaleName);
                        column.type = 'number';
                        column.valueFormatter = formatToTime;
                        column.valueGetter = formatToNumber;
                        break;

                    case 'dateTimePeriod':
                        column.headerName = translate('pages/visits/visit/columns/' + k, this.props.currentLocaleName);
                        column.valueGetter = ({ value }) => { if (!value) { return 'N/A'; } else { return value; } };
                        break;

                    case 'weekDaysPeriods':
                        column.headerName = translate('pages/visits/visit/columns/' + k, this.props.currentLocaleName);
                        column.renderCell = (params) => {
                            const weekDaysPeriods = params.row.weekDaysPeriods;
                            if (!weekDaysPeriods) {
                                return 'N/A';
                            }

                            let weekDays = convertWeekDays(weekDaysPeriods, 'UTC', resolveTimeZone(this.props.currentLocaleName));

                            let weekDayInputComponents = [];
                            let error = true;
                            if (weekDays !== null) {
                                for (const k in weekDaysPeriods) {
                                    if (Object.hasOwnProperty.call(weekDaysPeriods, k)) {
                                        const weekDaysPeriod = weekDaysPeriods[k];
                                        weekDayInputComponents.push(weekDayInputComponents.length);
                                        // weekDayInputComponents.push(weekDayInputComponents.length);
                                        // if (Array.isArray(weekDaysPeriod) === true && weekDaysPeriod.length !== 0) {
                                        //     error = false;
                                        //     let weekDay = k;
                                        //     let timePeriods = [];

                                        //     weekDaysPeriod.forEach((v, i) => {
                                        // let startDateParts, startTimeParts = null;
                                        // let t = v.start.split(' ');
                                        // startDateParts = t[0].split('-');
                                        // startTimeParts = t[1].split(':');
                                        // let start = {};
                                        // getDateTimeFormatObjectInEnglish(this.props.currentLocaleName).formatToParts(new Date(Date.UTC(startDateParts[0], startDateParts[1] - 1, startDateParts[2], startTimeParts[0], startTimeParts[1], startTimeParts[2]))).forEach((v, h) => {
                                        //     if (v.type !== 'literal') {
                                        //         start[v.type] = v.value;
                                        //     }
                                        // });
                                        // if (start.dayPeriod !== undefined && start.dayPeriod === 'PM') {
                                        //     start.hour = String(Number(start.hour) + 12);
                                        // }
                                        // t = null;

                                        // let endDateParts, endTimeParts = null;
                                        // t = v.end.split(' ');
                                        // endDateParts = t[0].split('-');
                                        // endTimeParts = t[1].split(':');
                                        // let end = {};
                                        // getDateTimeFormatObjectInEnglish(this.props.currentLocaleName).formatToParts(new Date(Date.UTC(endDateParts[0], endDateParts[1] - 1, endDateParts[2], endTimeParts[0], endTimeParts[1], endTimeParts[2]))).forEach((v, y) => {
                                        //     if (v.type !== 'literal') {
                                        //         end[v.type] = v.value;
                                        //     }
                                        // });
                                        // if (end.dayPeriod !== undefined && end.dayPeriod === 'PM') {
                                        //     end.hour = String(Number(end.hour) + 12);
                                        // }

                                        // if (Number(start.day) === Number(startDateParts[2]) && Number(end.day) === Number(startDateParts[2])) {
                                        //     let found = false;
                                        //     weekDays.forEach((v, j) => {
                                        //         if (v.weekDay === start.weekday) {
                                        //             found = true;
                                        //             weekDays[j].timePeriods.push({ start: start.hour + ':' + start.minute, end: end.hour + ':' + end.minute });
                                        //         }
                                        //     });
                                        //     if (!found) {
                                        //         timePeriods.push({ start: start.hour + ':' + start.minute, end: end.hour + ':' + end.minute });
                                        //     }
                                        // } else {
                                        //     if ((Number(start.day) < Number(startDateParts[2]) && Number(end.day) < Number(startDateParts[2])) || (Number(start.day) > Number(startDateParts[2]) && Number(end.day) > Number(startDateParts[2]))) {
                                        //         let found = false;
                                        //         weekDays.forEach((v, j) => {
                                        //             if (v.weekDay === start.weekday) {
                                        //                 found = true;
                                        //                 if (Number(start.day) < Number(startDateParts[2]) && Number(end.day) < Number(startDateParts[2])) {
                                        //                     if (weekDays[j].timePeriods[weekDays[j].timePeriods.length - 1].end === (start.hour + ':' + start.minute)) {
                                        //                         weekDays[j].timePeriods[weekDays[j].timePeriods.length - 1].end = end.hour + ':' + end.minute;
                                        //                     } else {
                                        //                         weekDays[j].timePeriods.push({ start: start.hour + ':' + start.minute, end: end.hour + ':' + end.minute });
                                        //                     }
                                        //                 } else {
                                        //                     if (weekDays[j].timePeriods[0].start === (end.hour + ':' + end.minute)) {
                                        //                         weekDays[j].timePeriods[0].start = start.hour + ':' + start.minute;
                                        //                     } else {
                                        //                         weekDays[j].timePeriods.push({ start: start.hour + ':' + start.minute, end: end.hour + ':' + end.minute });
                                        //                     }
                                        //                 }
                                        //             }
                                        //         });
                                        //         if (!found) {
                                        //             weekDays.push({ weekDay: start.weekday, timePeriods: [{ start: start.hour + ':' + start.minute, end: end.hour + ':' + end.minute }] });
                                        //             weekDayInputComponents.push(weekDayInputComponents.length);
                                        //         }
                                        //     } else {
                                        //         if (Number(start.day) < Number(startDateParts[2])) {
                                        //             let found = false;
                                        //             weekDays.forEach((v, j) => {
                                        //                 if (v.weekDay === start.weekday) {
                                        //                     found = true;
                                        //                     weekDays[j].timePeriods.push({ start: '00:00', end: end.hour + ':' + end.minute });
                                        //                 }
                                        //             });
                                        //             if (!found) {
                                        //                 timePeriods.push({ start: '00:00', end: end.hour + ':' + end.minute });
                                        //             }

                                        //             found = false;
                                        //             weekDays.forEach((v, j) => {
                                        //                 if (v.weekDay === start.weekday) {
                                        //                     found = true;
                                        //                     if (weekDays[j].timePeriods[weekDays[j].timePeriods.length - 1].end === (start.hour + ':' + start.minute)) {
                                        //                         weekDays[j].timePeriods[weekDays[j].timePeriods.length - 1].end = '00:00';
                                        //                     } else {
                                        //                         weekDays[j].timePeriods.push({ start: start.hour + ':' + start.minute, end: '00:00' });
                                        //                     }
                                        //                 }
                                        //             });
                                        //             if (!found) {
                                        //                 weekDays.push({ weekDay: start.weekday, timePeriods: [{ start: start.hour + ':' + start.minute, end: '00:00' }] });
                                        //                 weekDayInputComponents.push(weekDayInputComponents.length);
                                        //             }
                                        //         } else {
                                        //             let found = false;
                                        //             weekDays.forEach((v, j) => {
                                        //                 if (v.weekDay === end.weekday) {
                                        //                     found = true;
                                        //                     weekDays[j].timePeriods.push({ start: start.hour + ':' + start.minute, end: '00:00' });
                                        //                 }
                                        //             });
                                        //             if (!found) {
                                        //                 timePeriods.push({ start: start.hour + ':' + start.minute, end: '00:00' });
                                        //             }


                                        //             found = false;
                                        //             weekDays.forEach((v, j) => {
                                        //                 if (v.weekDay === end.weekday) {
                                        //                     found = true;
                                        //                     if (weekDays[j].timePeriods[0].start === (end.hour + ':' + end.minute)) {
                                        //                         weekDays[j].timePeriods[0].start = '00:00';
                                        //                     } else {
                                        //                         weekDays[j].timePeriods.push({ start: '00:00', end: end.hour + ':' + end.minute });
                                        //                     }
                                        //                 }
                                        //             });
                                        //             if (!found) {
                                        //                 weekDays.push({ weekDay: end.weekday, timePeriods: [{ start: '00:00', end: end.hour + ':' + end.minute }] });
                                        //                 weekDayInputComponents.push(weekDayInputComponents.length);
                                        //             }
                                        //         }
                                        //     }
                                        // }
                                        //     });

                                        //     if (timePeriods.length === 0) {
                                        //         continue;
                                        //     }

                                        //     weekDays.push({ weekDay: weekDay, timePeriods: timePeriods });
                                        //     weekDayInputComponents.push(weekDayInputComponents.length);
                                        // } else {
                                        //     continue;
                                        // }
                                    }
                                }
                            }

                            let props = {};
                            if (
                                // !error &&
                                weekDays !== null &&
                                weekDays.length !== 0 && weekDayInputComponents.length !== 0
                            ) {
                                props.weekDays = weekDays;
                                props.weekDayInputComponents = weekDayInputComponents;
                            }

                            return (
                                <>
                                    <Button type='button' onClick={(e) => {
                                        let t = this.state.openWeekDaysPeriods;
                                        t[params.row.id] = true;
                                        this.setState({ openWeekDaysPeriods: t });
                                    }}>
                                        {translate('general/show/single/ucFirstLetterFirstWord', this.props.currentLocaleName)}
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
                                            <WeekDayInputComponents currentLocaleName={this.props.currentLocaleName} {...props} />
                                        </Paper>
                                    </Modal>
                                </>
                            );
                        };
                        break;

                    case 'visitTimestamp':
                        column.headerName = translate('pages/visits/visit/columns/' + k, this.props.currentLocaleName);
                        column.type = 'dateTime';
                        column.valueGetter = (props) => { if (!props.value) { return null; } return getDateTimeFormatObject(this.props.currentLocaleName).format(new Date(props.value * 1000)); };
                        column.minWidth = 330;
                        break;

                    case 'createdAt':
                        column.headerName = translate('general/columns/' + k + '/single/ucFirstLetterFirstWord', this.props.currentLocaleName);
                        column.type = 'dateTime';
                        column.valueGetter = ({ value }) => { if (!value) { return ''; } return new Date(value); };
                        column.minWidth = 170;
                        break;

                    case 'updatedAt':
                        column.headerName = translate('general/columns/' + k + '/single/ucFirstLetterFirstWord', this.props.currentLocaleName);
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
