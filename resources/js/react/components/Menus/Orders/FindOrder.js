import React, { Component } from 'react'

import PropTypes from 'prop-types';

import { Button, Divider, Stack } from '@mui/material';

import OrdersDataGrid from '../../Grids/Orders/OrdersDataGrid';
import { translate } from '../../../traslation/translate';
import { formatToNumber } from '../../Grids/formatters';
import PackagesDataGridModal from '../../Grids/Orders/Modals/PackagesDataGridModal';
import PartsDataGridModal from '../../Grids/Orders/Modals/PartsDataGridModal';

/**
 * SelfVisitsDataGrid
 * @augments {Component<Props, State>}
 */
export class FindOrder extends Component {
    static propTypes = {
        businessName: PropTypes.string.isRequired,
        account: PropTypes.object.isRequired,
        privileges: PropTypes.object.isRequired,

        onSelectionModelChange: PropTypes.func.isRequired,
    }

    constructor(props) {
        super(props);

        this.addColumns = this.addColumns.bind(this);
        this.onSelectionModelChange = this.onSelectionModelChange.bind(this);

        this.state = {
            orderId: null,
        };
    }

    addColumns(columns) {
        if (this.props.businessName !== 'laser') {
            return columns;
        }

        columns.push({
            field: 'parts',
            headerName: translate('pages/orders/order/columns/parts'),
            description: translate('pages/orders/order/columns/parts'),
            renderCell: (params) => <PartsDataGridModal gridProps={{ rows: params.value.parts }} />,
        });

        columns.push({
            field: 'packages',
            headerName: translate('pages/orders/order/columns/packages'),
            description: translate('pages/orders/order/columns/packages'),
            renderCell: (params) => <PackagesDataGridModal gridProps={{ rows: params.value.packages }} />,
        });

        columns.push({
            field: 'priceWithDiscount',
            headerName: translate('pages/orders/order/columns/priceWithDiscount'),
            description: translate('pages/orders/order/columns/priceWithDiscount'),
            type: 'number',
            valueFormatter: formatToNumber,
        });

        columns.push({
            field: 'gender',
            headerName: translate('general/columns/gender/single/ucFirstLetterFirstWord'),
            description: translate('general/columns/gender/single/ucFirstLetterFirstWord'),
        });

        return columns;
    }

    onSelectionModelChange(ordersId) {
        if (ordersId.length === 0) {
            this.setState({ orderId: null });
        } else {
            this.setState({ orderId: ordersId[ordersId.length - 1] });
        }
    }

    render() {
        return (
            <Stack direction='column' divider={<Divider orientation='horizontal' />} spacing={2} sx={{ height: '100%' }} >
                <div>{translate('pages/visits/visit/choose-one-order')}</div>

                <OrdersDataGrid
                    privileges={this.props.privileges}

                    businessName={this.props.businessName}
                    username={this.props.account.username}

                    addColumns={this.addColumns}

                    reload={this.state.reload}
                    afterReload={() => this.setState({ reload: true })}

                    checkboxSelection={true}
                    onSelectionModelChange={this.onSelectionModelChange}
                    selectionModel={[this.state.orderId]}
                />

                <Button variant='contained' type='button' disabled={this.state.orderId === null} onClick={(e) => { this.props.onSelectionModelChange(this.state.orderId); }}>
                    {translate('general/submit/single/ucFirstLetterFirstWord')}
                </Button>
            </Stack >
        )
    }
}

export default FindOrder
