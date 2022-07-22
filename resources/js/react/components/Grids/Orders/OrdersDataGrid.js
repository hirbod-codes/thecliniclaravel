import React, { Component } from 'react'

import PropTypes from 'prop-types';

import { translate } from '../../../traslation/translate';
import { formatToNumber, formatToTime } from '../formatters';
import { getJsonData } from '../../Http/fetch';
import DataGridComponent from '../DataGridComponent';
import AnOrderVisitsDataGrid from '../Visits/AnOrderVisitsDataGrid';
import { Button, Modal, Paper } from '@mui/material';

/**
 * OrdersDataGrid
 * @augments {Component<Props, State>}
 */
export class OrdersDataGrid extends Component {
    static propTypes = {
        currentLocaleName: PropTypes.string.isRequired,

        privileges: PropTypes.object.isRequired,
        businessName: PropTypes.string.isRequired,

        username: PropTypes.string,
        lastOrderId: PropTypes.number,
        operator: PropTypes.string,
        price: PropTypes.number,
        timeConsumption: PropTypes.number,

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

            openVisitsModal: [],

            pageSize: 10,
        };
    }

    getData() {
        return new Promise(async (resolve, reject) => {
            if (this.props.rows !== undefined) {
                resolve(this.props.rows);
                return;
            }

            let url = '/orders/' + this.props.businessName + '?';
            if (this.props.username !== undefined) {
                url += 'username=' + this.props.username;
            } else {
                if (this.props.lastOrderId !== undefined) {
                    url += 'count=' + this.state.pageSize;
                }
            }

            url += '&';

            if (this.props.lastOrderId !== undefined) {
                url += 'lastOrderId=' + this.props.lastOrderId;
            }

            url += '&';

            if (this.props.operator !== undefined && (this.props.price !== undefined || this.props.timeConsumption !== undefined)) {
                url += 'operator=' + this.props.operator + '&price=' + this.props.price + '&timeConsumption=' + this.props.timeConsumption;
            }

            let data = await getJsonData(url, { 'X-CSRF-TOKEN': this.state.token }).then((res) => res.json());

            data = data.orders;

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
                    case 'visits':
                        column.headerName = translate('general/columns/' + k + '/single/ucFirstLetterFirstWord', this.props.currentLocaleName);
                        column.renderCell = (params) => {
                            return (
                                <>
                                    <Button type='button' onClick={() => {
                                        let t = this.state.openVisitsModal;
                                        t[params.row.id] = true;
                                        this.setState({ openVisitsModal: t });
                                    }}>
                                        {translate('general/show/single/ucFirstLetterFirstWord', this.props.currentLocaleName)}
                                    </Button>
                                    <Modal
                                        open={this.state.openVisitsModal[params.row.id] === true}
                                        onClose={() => {
                                            let t = this.state.openVisitsModal;
                                            delete t[params.row.id];
                                            this.setState({ openVisitsModal: t });
                                        }}
                                    >
                                        <Paper sx={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)', position: 'absolute', height: '70%', width: '70%', p: 1 }}>
                                            <AnOrderVisitsDataGrid orderId={params.row.id} businessName={this.props.businessName} privileges={this.props.privileges} currentLocaleName={this.props.currentLocaleName} />
                                        </Paper>
                                    </Modal>
                                </>
                            );
                        };
                        break;

                    case 'id':
                        column.headerName = translate('general/columns/' + k + '/single/ucFirstLetterFirstWord', this.props.currentLocaleName);
                        column.type = 'number';
                        column.valueFormatter = formatToNumber;
                        break;

                    case 'neededTime':
                        column.headerName = translate('pages/orders/order/columns/' + k, this.props.currentLocaleName);
                        column.type = 'number';
                        column.valueFormatter = formatToTime;
                        column.valueGetter = formatToNumber;
                        column.minWidth = 170;
                        break;

                    case 'price':
                        column.headerName = translate('pages/orders/order/columns/' + k, this.props.currentLocaleName);
                        column.type = 'number';
                        column.valueFormatter = formatToNumber;
                        break;

                    case 'createdAt':
                        column.headerName = translate('general/columns/' + k + '/single/ucFirstLetterFirstWord', this.props.currentLocaleName);
                        column.type = 'dateTime';
                        column.valueGetter = ({ value }) => value && new Date(value);
                        column.minWidth = 170;
                        break;

                    case 'updatedAt':
                        column.headerName = translate('general/columns/' + k + '/single/ucFirstLetterFirstWord', this.props.currentLocaleName);
                        column.type = 'dateTime';
                        column.valueGetter = ({ value }) => value && new Date(value);
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

export default OrdersDataGrid
