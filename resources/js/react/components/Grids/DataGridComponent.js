import React, { Component } from 'react'

import PropTypes from 'prop-types';

import { DataGrid as MUiDataGrid } from '@mui/x-data-grid';
import { updateState } from '../helpers';

/**
 * DataGridComponent
 * @augments {Component<Props, State>}
 */
export class DataGridComponent extends Component {
    static propTypes = {
        getData: PropTypes.func.isRequired,
        collectColumns: PropTypes.func.isRequired,
        getRowCount: PropTypes.func,

        reload: PropTypes.bool,
        beforeReload: PropTypes.func,
        afterReload: PropTypes.func,

        pagination: PropTypes.bool,
        paginationMode: PropTypes.oneOf(['client', 'server']),

        onPageChange: PropTypes.func,
        onPageSizeChange: PropTypes.func,
        rowsPerPageOptions: PropTypes.arrayOf(PropTypes.number),

        gridProps: PropTypes.object,
    }

    constructor(props) {
        super(props);

        this.reload = this.reload.bind(this);

        this.state = {
            isLoading: true,

            page: 0,
            pageSize: 10,

            rows: [],
            rowCount: 0,
            columns: [],
        };
    }

    async componentDidMount(props, state) {
        let rows = await this.props.getData();
        let columns = await this.props.collectColumns(rows);

        let rowCount = 0;
        if (this.props.getRowCount !== undefined) {
            rowCount = await this.props.getRowCount();
        } else {
            rowCount = rows.length;
        }

        await updateState(this, { rowCount: Number(rowCount), rows: rows, columns: columns, isLoading: false });
    }

    componentDidUpdate(prevProps) {
        if (this.props.reload === true) {
            if (this.props.beforeReload !== undefined) {
                this.props.beforeReload();
            }
            this.reload();
            if (this.props.afterReload !== undefined) {
                this.props.afterReload();
            }
        }
    }

    async reload() {
        await updateState(this, { isLoading: true });

        let rows = await this.props.getData();
        let columns = await this.props.collectColumns(rows);

        let rowCount = 0;
        if (this.props.getRowCount !== undefined) {
            rowCount = await this.props.getRowCount();
        } else {
            rowCount = rows.length;
        }

        await updateState(this, { isLoading: false, rows: rows, rowCount: Number(rowCount), columns: columns });
    }

    render() {
        return (
            <MUiDataGrid
                loading={this.state.isLoading}

                rows={this.state.rows}
                rowCount={this.state.rowCount}

                pagination={(this.props.pagination !== undefined) ? this.props.pagination : true}
                paginationMode={(this.props.paginationMode !== undefined) ? this.props.paginationMode : true}

                rowsPerPageOptions={(this.props.rowsPerPageOptions !== undefined) ? this.props.rowsPerPageOptions : [10, 20, 30]}
                page={this.state.page}
                onPageChange={(newPage) => { this.setState({ page: newPage }); if (this.props.onPageChange !== undefined) { this.props.onPageChange(newPage) } }}

                pageSize={this.state.pageSize}
                onPageSizeChange={(newPageSize) => { this.setState({ page: 0, pageSize: newPageSize }); if (this.props.onPageSizeChange !== undefined) { this.props.onPageSizeChange(newPageSize) } }}

                columns={this.state.columns}

                onSelectionModelChange={this.props.onSelect}

                selectionModel={this.props.selectedPartsId}

                {...((this.props.gridProps !== undefined) ? this.props.gridProps : {})}
            />
        )
    }
}

export default DataGridComponent
