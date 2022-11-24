import React, { Component } from 'react'

import CloseIcon from '@mui/icons-material/Close';
import { DataGrid, GridFooterContainer, GridPagination, GridSelectedRowCount } from '@mui/x-data-grid';

import { translate } from '../../../traslation/translate';
import { formatToNumber, formatToTime } from '../formatters';
import { localizeDate, updateState } from '../../helpers';
import { Alert, Button, CircularProgress, IconButton, Snackbar } from '@mui/material';
import { get_laser_price_calculation, get_laser_time_calculation, get_orders_laser, get_orders_regular, get_parts } from '../../Http/Api/order';
import { connect } from 'react-redux';

/**
 * PartsDataGrid
 * @augments {Component<Props, State>}
 */
export class PartsDataGrid extends Component {
    constructor(props) {
        super(props);

        this.handleFeedbackClose = this.handleFeedbackClose.bind(this);

        this.getData = this.getData.bind(this);
        this.collectColumns = this.collectColumns.bind(this);

        this.onSelect = this.onSelect.bind(this);

        this.calculate = this.calculate.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            feedbackMessages: [],

            isLoading: true,
            page: 0,
            pageSize: 10,
            rows: [],
            columns: [],

            selectedPartsId: [],
            selectedParts: [],
            isCalculatingParts: false,
            totalPrice: 0,
            totalNeddedTime: 0,
        };
    }

    async componentDidMount() {
        let selectedParts = [];
        if (Object.hasOwnProperty.call(this.props, 'selectedParts')) {
            selectedParts = this.props.selectedParts;
        }

        let rows = await this.getData();

        this.setState({
            selectedPartsId: selectedParts.map((v, i) => v.id),
            selectedParts: selectedParts,
            rows: rows,
            isLoading: false,
            columns: await this.collectColumns(rows)
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

    getData() {
        return new Promise(async (resolve) => {
            let rows = [];
            if (Object.hasOwnProperty.call(this.props, 'rows') && this.props.rows && Array.isArray(this.props.rows)) {
                rows = this.props.rows;
            } else {
                if (Object.hasOwnProperty.call(this.props, 'username') && Object.hasOwnProperty.call(this.props, 'orderId') && Object.hasOwnProperty.call(this.props, 'businessName')) {
                    let rows = null;
                    switch (this.props.businessName) {
                        case 'laser':
                            rows = await get_orders_laser(
                                this.props.roleName,
                                null,
                                this.props.username,
                                this.state.token);
                            break;

                        case 'regular':
                            rows = await get_orders_regular(
                                this.props.roleName,
                                null,
                                this.props.username,
                                this.state.token);
                            break;

                        default:
                            break;
                    }
                    if (rows.response.status !== 200) {
                        let value = null;
                        if (Array.isArray(rows.value)) { value = rows.value; } else { value = [rows.value]; }
                        value = value.map((v, i) => { return { open: true, message: v, color: rows.response.status === 200 ? 'success' : 'error' } });
                        this.setState({ feedbackMessages: value });
                        resolve([]);
                    }
                    rows = rows.value.filter((o) => o.id === this.props.orderId)[0].parts;
                } else {
                    if (Object.hasOwnProperty.call(this.props, 'gender') && Object.hasOwnProperty.call(this.props, 'businessName')) {
                        rows = await get_parts('laser', this.props.gender, this.state.token);
                        if (rows.response.status !== 200) {
                            let value = null;
                            if (Array.isArray(rows.value)) { value = rows.value; } else { value = [rows.value]; }
                            value = value.map((v, i) => { return { open: true, message: v, color: rows.response.status === 200 ? 'success' : 'error' } });
                            this.setState({ feedbackMessages: value });
                            resolve([]);
                        }
                        rows = rows.value.parts;
                    } else {
                        throw Error('Insufficient information for parts data grid');
                    }
                }
            }

            resolve(rows);
        });
    }

    collectColumns(rows) {
        return new Promise((resolve) => {
            if (!rows || rows.length === 0) {
                resolve([]);
                return;
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

                        case 'needed_time':
                            column.headerName = translate('pages/orders/order/columns/' + k);
                            column.type = 'number';
                            column.valueFormatter = formatToTime;
                            column.valueGetter = formatToNumber;
                            break;

                        case 'price':
                            column.headerName = translate('pages/orders/order/columns/' + k);
                            column.type = 'number';
                            column.valueFormatter = formatToNumber;
                            break;

                        case 'name':
                            column.headerName = translate('general/columns/' + k + '/single/ucFirstLetterFirstWord');
                            column.type = 'string';
                            break;

                        case 'created_at':
                            column.headerName = translate('general/columns/' + k + '/single/ucFirstLetterFirstWord');
                            column.type = 'dateTime';
                            column.valueFormatter = (props) => { if (!props.value) { return null; } return localizeDate('utc', props.value, true); };
                            column.minWidth = 170;
                            break;

                        case 'updated_at':
                            column.headerName = translate('general/columns/' + k + '/single/ucFirstLetterFirstWord');
                            column.type = 'dateTime';
                            column.valueFormatter = (props) => { if (!props.value) { return null; } return localizeDate('utc', props.value, true); };
                            column.minWidth = 170;
                            break;

                        case 'gender':
                            column.headerName = translate('general/columns/account/' + k + '/single/ucFirstLetterFirstWord');
                            break;

                        default:
                            break;
                    }

                    columns.push(column);
                }
            }

            resolve(columns);
        });
    }

    render() {
        return (
            <>
                <DataGrid
                    loading={this.state.isLoading}

                    components={{
                        Footer: () =>
                            <GridFooterContainer>
                                <GridSelectedRowCount selectedRowCount={this.state.selectedParts.length} />
                                <>
                                    <div>
                                        {this.state.isCalculatingParts ?
                                            <CircularProgress size='2rem' /> :
                                            <Button type='button' variant='text' onClick={this.calculate}>
                                                {translate('general/refresh/single/ucFirstLetterFirstWord')}
                                            </Button>
                                        }
                                    </div>
                                    <div>
                                        {translate('pages/orders/order/total-price')}: {this.state.totalPrice}
                                    </div>
                                    <div>
                                        {translate('pages/orders/order/total-neededTime')}: {this.state.totalNeddedTime}
                                    </div>
                                </>
                                <GridPagination />
                            </GridFooterContainer>,
                    }}

                    rows={this.state.rows}

                    rowsPerPageOptions={[10, 20, 30]}
                    page={this.state.page}
                    onPageChange={(newPage) => { this.setState({ page: newPage }); }}

                    pageSize={this.state.pageSize}
                    onPageSizeChange={(newPageSize) => { this.setState({ page: 1, pageSize: newPageSize }); }}

                    checkboxSelection={(Object.hasOwnProperty.call(this.props, 'checkboxSelection') && (this.props.checkboxSelection === true)) ? true : false}

                    columns={this.state.columns}

                    onSelectionModelChange={this.onSelect}

                    selectionModel={this.state.selectedPartsId}
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

    async onSelect(selectedPartsId) {
        let selectedParts = [];
        let rows = this.state.rows;

        rows.forEach((r, ir) => {
            selectedPartsId.forEach((id, index) => {
                if (r.id === id) {
                    selectedParts.push(r);
                }
            });
        });

        if (Object.hasOwnProperty.call(this.props, 'onSelect')) {
            this.props.onSelect(selectedParts);
        }

        await updateState(this, {
            selectedParts: selectedParts,
            selectedPartsId: selectedPartsId,
        });
    }

    async calculate() {
        if (this.state.selectedParts.length === 0) {
            return;
        }

        let data = { parts: this.state.selectedParts.map((v, i) => { return v.name; }) };
        if (Object.hasOwnProperty.call(this.props, 'gender')) {
            data.gender = this.props.gender;
        }

        await updateState(this, {
            isCalculatingParts: true,
        });

        let totalPrice = await get_laser_price_calculation(this.props.businessName, data.parts, [], this.props.gender, this.state.token);
        if (totalPrice.response.status !== 200) {
            let value = null;
            if (Array.isArray(totalPrice.value)) { value = totalPrice.value; } else { value = [totalPrice.value]; }
            value = value.map((v, i) => { return { open: true, message: v, color: totalPrice.response.status === 200 ? 'success' : 'error' } });
            this.setState({ feedbackMessages: value });
            return;
        }

        let totalNeddedTime = await get_laser_time_calculation(this.props.businessName, data.parts, [], this.props.gender, this.state.token);
        if (totalNeddedTime.response.status !== 200) {
            let value = null;
            if (Array.isArray(totalNeddedTime.value)) { value = totalNeddedTime.value; } else { value = [totalNeddedTime.value]; }
            value = value.map((v, i) => { return { open: true, message: v, color: totalNeddedTime.response.status === 200 ? 'success' : 'error' } });
            this.setState({ feedbackMessages: value });
            return;
        }

        await updateState(this, {
            totalPrice: totalPrice.value.price,
            totalNeddedTime: totalNeddedTime.value,
            isCalculatingParts: false,
        });
    }
}

const mapStateToProps = state => ({
    redux: state
});

export default connect(mapStateToProps)(PartsDataGrid)
