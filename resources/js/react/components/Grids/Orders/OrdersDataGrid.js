import React, { Component } from 'react'

import PropTypes from 'prop-types';

import CloseIcon from '@mui/icons-material/Close';
import { translate } from '../../../traslation/translate';
import { formatToNumber, formatToTime } from '../formatters';
import { fetchData } from '../../Http/fetch';
import DataGridComponent from '../DataGridComponent';
import { PrivilegesContext } from '../../privilegesContext';
import { Alert, IconButton, Snackbar } from '@mui/material';

/**
 * OrdersDataGrid
 * @augments {Component<Props, State>}
 */
export class OrdersDataGrid extends Component {
    static propTypes = {
        businessName: PropTypes.string.isRequired,

        roleName: PropTypes.string,
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

    static contextType = PrivilegesContext;

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

            openVisitsModal: [],

            pageSize: 10,
        };
    }

    handleFeedbackClose(event, reason, key) {
        if (reason === 'clickaway') {
            return;
        }

        let feedbackMessages = this.state.feedbackMessages;
        feedbackMessages[key].open = false;
        this.setState({ feedbackMessages: feedbackMessages });
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
                url += '&';
            } else {
                url += 'roleName=' + this.props.roleName;
                url += '&';

                if (this.props.lastOrderId !== undefined) {
                    url += 'count=' + this.state.pageSize;
                    url += '&';
                }
            }

            if (this.props.lastOrderId !== undefined) {
                url += 'lastOrderId=' + this.props.lastOrderId;
                url += '&';
            }

            if (this.props.operator !== undefined && (this.props.price !== undefined || this.props.timeConsumption !== undefined)) {
                url += 'operator=' + this.props.operator + '&price=' + this.props.price + '&timeConsumption=' + this.props.timeConsumption;
            }

            let r = await fetchData('get', url, {}, { 'X-CSRF-TOKEN': this.state.token });
            if (r.response.status !== 200) {
                let value = null;
                if (Array.isArray(r.value)) { value = r.value; } else { value = [r.value]; }
                value = value.map((v, i) => { return { open: true, message: v, color: r.response.status === 200 ? 'success' : 'error' } });
                this.setState({ feedbackMessages: value });
                resolve([]);
            }

            let data = r.value;

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
            if (!Object.hasOwnProperty.call(rows[0], k)) {
                continue;
            }

            let column = {
                field: k,
            };
            let pass = false;

            switch (k) {
                case 'id':
                    column.headerName = translate('general/columns/' + k + '/single/ucFirstLetterFirstWord');
                    column.type = 'number';
                    column.valueFormatter = formatToNumber;
                    break;

                case 'needed_time':
                    column.headerName = translate('pages/orders/order/columns/' + k);
                    column.type = 'number';
                    column.valueFormatter = formatToTime;
                    column.minWidth = 170;
                    break;

                case 'price':
                    column.headerName = translate('pages/orders/order/columns/' + k);
                    column.type = 'number';
                    column.valueFormatter = formatToNumber;
                    break;

                case 'created_at':
                    column.headerName = translate('general/columns/' + k + '/single/ucFirstLetterFirstWord');
                    column.type = 'dateTime';
                    column.valueGetter = ({ value }) => value && new Date(value);
                    column.minWidth = 170;
                    break;

                case 'updated_at':
                    column.headerName = translate('general/columns/' + k + '/single/ucFirstLetterFirstWord');
                    column.type = 'dateTime';
                    column.valueGetter = ({ value }) => value && new Date(value);
                    column.minWidth = 170;
                    break;

                default:
                    continue;
            }

            if (pass) {
                continue;
            }

            column.description = column.headerName;
            columns.push(column);
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

export default OrdersDataGrid
