import React, { Component } from 'react'

import PropTypes from 'prop-types';

import CloseIcon from '@mui/icons-material/Close';

import DataGridComponent from '../DataGridComponent';
import { translate } from '../../../traslation/translate';
import { formatToNumber } from '../formatters';
import { Alert, IconButton, Snackbar } from '@mui/material';
import { localizeDate } from '../../helpers';
import { get_accounts } from '../../Http/Api/accounts';
import { connect } from 'react-redux';
import store from '../../../../redux/store';

/**
 * AccountsDataGrid
 * @augments {Component<Props, State>}
 */
export class AccountsDataGrid extends Component {
    static propTypes = {
        account: PropTypes.object.isRequired,

        role: PropTypes.string.isRequired,
        lastAccountId: PropTypes.number.isRequired,

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

            openOrdersModal: [],

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

    async getData() {
        return new Promise(async (resolve, reject) => {
            if (this.props.rows !== undefined) {
                resolve(this.props.rows);
                return;
            }

            let data = await get_accounts(this.props.role, this.state.pageSize, this.props.lastAccountId <= 0 ? null : this.props.lastAccountId, this.state.token);
            if (data.response.status !== 200) {
                let value = null;
                if (Array.isArray(data.value)) { value = data.value; } else { value = [data.value]; }
                value = value.map((v, i) => { return { open: true, message: v, color: data.response.status === 200 ? 'success' : 'error' } });
                this.setState({ feedbackMessages: value });
                reject();
            }

            data = data.value.map((v, i) => {
                return Object.assign(v.user, v.account);
            });

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

        let columns = [{
            field: 'id',
            headerName: translate('general/columns/id/single/ucFirstLetterFirstWord'),
            type: 'number',
            valueFormatter: formatToNumber,
        }];

        const locale = store.getState().local.local.shortName;

        for (const k in rows[0]) {
            if (Object.hasOwnProperty.call(rows[0], k)) {
                if (k.indexOf('id') !== -1) {
                    continue;
                }

                let column = {
                    field: k,
                };

                switch (k) {
                    case 'firstname':
                        column.headerName = translate('general/columns/account/firstname/single/ucFirstLetterFirstWord');
                        break;

                    case 'lastname':
                        column.headerName = translate('general/columns/account/lastname/single/ucFirstLetterFirstWord');
                        break;

                    case 'username':
                        column.headerName = translate('general/columns/account/username/single/ucFirstLetterFirstWord');
                        break;

                    case 'email':
                        column.headerName = translate('general/email/single/ucFirstLetterFirstWord');
                        break;

                    case 'emailVerifiedAt':
                        column.headerName = translate('general/columns/account/emailVerifiedAt/single/ucFirstLetterFirstWord');
                        column.type = 'dateTime';
                        column.valueGetter = ({ value }) => value && new Date(value);
                        column.minWidth = 170;
                        break;

                    case 'phonenumber':
                        column.headerName = translate('general/columns/account/phonenumber/single/ucFirstLetterFirstWord');
                        break;

                    case 'phonenumberVerifiedAt':
                        column.headerName = translate('general/columns/account/phonenumberVerifiedAt/single/ucFirstLetterFirstWord');
                        column.type = 'dateTime';
                        column.valueGetter = ({ value }) => value && new Date(value);
                        column.minWidth = 170;
                        break;

                    case 'gender':
                        column.headerName = translate('general/gender/single/ucFirstLetterFirstWord');
                        break;

                    case 'age':
                        column.headerName = translate('general/columns/account/age/single/ucFirstLetterFirstWord');
                        column.type = 'number';
                        break;

                    case 'state':
                        column.headerName = translate('general/columns/account/state/single/ucFirstLetterFirstWord');
                        break;

                    case 'city':
                        column.headerName = translate('general/columns/account/city/single/ucFirstLetterFirstWord');
                        break;

                    case 'address':
                        column.headerName = translate('general/columns/account/address/single/ucFirstLetterFirstWord');
                        break;

                    case 'created_at':
                        column.headerName = translate('general/columns/' + k + '/single/ucFirstLetterFirstWord');
                        column.type = 'dateTime';
                        column.valueFormatter = (props) => { if (!props.value) { return null; } return localizeDate('utc', props.value, locale, true); };
                        column.minWidth = 170;
                        break;

                    case 'updated_at':
                        column.headerName = translate('general/columns/' + k + '/single/ucFirstLetterFirstWord');
                        column.type = 'dateTime';
                        column.valueFormatter = (props) => { if (!props.value) { return null; } return localizeDate('utc', props.value, locale, true); };
                        column.minWidth = 170;
                        break;

                    default:
                        break;
                }

                if (column.headerName === undefined) {
                    continue;
                }

                if (column.description === undefined) {
                    column.description = column.headerName;
                }

                if (column.minWidth === undefined) {
                    column.minWidth = 150;
                }

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

const mapStateToProps = state => ({
    redux: state
});

export default connect(mapStateToProps)(AccountsDataGrid)
