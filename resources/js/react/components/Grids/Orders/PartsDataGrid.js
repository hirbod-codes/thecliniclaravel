import React, { Component } from 'react'

import PropTypes from 'prop-types';

import { DataGrid, GridFooterContainer, GridPagination, GridSelectedRowCount } from '@mui/x-data-grid';

import { translate } from '../../../traslation/translate';
import { formatToNumber, formatToTime } from '../formatters';
import { fetchData } from '../../Http/fetch';
import { updateState } from '../../helpers';
import { Button, CircularProgress } from '@mui/material';

/**
 * PartsDataGrid
 * @augments {Component<Props, State>}
 */
export class PartsDataGrid extends Component {
    static propTypes = {
        currentLocaleName: PropTypes.string.isRequired,
    }

    constructor(props) {
        super(props);

        this.getData = this.getData.bind(this);
        this.collectColumns = this.collectColumns.bind(this);

        this.onSelect = this.onSelect.bind(this);

        this.calculate = this.calculate.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

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

    getData() {
        return new Promise(async (resolve) => {
            let rows = [];
            if (Object.hasOwnProperty.call(this.props, 'rows') && this.props.rows && Array.isArray(this.props.rows)) {
                rows = this.props.rows;
            } else {
                if (Object.hasOwnProperty.call(this.props, 'accountId') && Object.hasOwnProperty.call(this.props, 'orderId') && Object.hasOwnProperty.call(this.props, 'businessName')) {
                    rows = await fetchData('get', '/orders/' + this.props.businessName + '/' + this.props.accountId + '/' + this.props.orderId);
                    rows = rows.value.parts;
                } else {
                    if (Object.hasOwnProperty.call(this.props, 'gender') && Object.hasOwnProperty.call(this.props, 'businessName')) {
                        rows = await fetchData('get', '/' + this.props.businessName + '/parts?gender=' + this.props.gender);
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
                            column.headerName = translate('general/columns/' + k + '/single/ucFirstLetterFirstWord', this.props.currentLocaleName);
                            column.type = 'number';
                            column.valueFormatter = formatToNumber;
                            break;

                        case 'neededTime':
                            column.headerName = translate('pages/orders/order/columns/' + k, this.props.currentLocaleName);
                            column.type = 'number';
                            column.valueFormatter = formatToTime;
                            column.valueGetter = formatToNumber;
                            break;

                        case 'price':
                            column.headerName = translate('pages/orders/order/columns/' + k, this.props.currentLocaleName);
                            column.type = 'number';
                            column.valueFormatter = formatToNumber;
                            break;

                        case 'name':
                            column.headerName = translate('general/columns/' + k + '/single/ucFirstLetterFirstWord', this.props.currentLocaleName);
                            column.type = 'string';
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

                        case 'gender':
                            column.headerName = translate('general/columns/' + k + '/single/ucFirstLetterFirstWord', this.props.currentLocaleName);
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
                                            {translate('general/refresh/single/ucFirstLetterFirstWord', this.props.currentLocaleName)}
                                        </Button>
                                    }
                                </div>
                                <div>
                                    {translate('pages/orders/order/total-price', this.props.currentLocaleName)}: {this.state.totalPrice}
                                </div>
                                <div>
                                    {translate('pages/orders/order/total-neededTime', this.props.currentLocaleName)}: {this.state.totalNeddedTime}
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

        await updateState(this, {
            totalPrice: (await fetchData('post', '/' + this.props.businessName + '/price-calculation', data, { 'X-CSRF-TOKEN': this.state.token })).value.price,
            totalNeddedTime: (await fetchData('post', '/' + this.props.businessName + '/time-calculation', data, { 'X-CSRF-TOKEN': this.state.token })).value,
            isCalculatingParts: false,
        });
    }
}

export default PartsDataGrid
