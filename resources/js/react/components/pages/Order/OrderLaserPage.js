import React, { Component } from 'react'
import { Link } from 'react-router-dom';

import Header from '../../headers/Header.js';
import SlidingDialog from '../../Menus/SlidingDialog.js';

import { getJsonData, postJsonData } from '../../Http/fetch'
import { translate, ucFirstLetterFirstWord } from '../../../traslation/translate';
import { updateState } from '../../helpers.js';
import TabPanel from '../../Menus/TabPanel.js';
import { collectMessagesFromResponse, makeFormHelperTextComponents } from '../../Http/response.js';
import ChangeOwnershipButton from './ChangeOwnershipButton.js';

import { Box } from '@mui/material';
import { Button } from '@mui/material';
import { Checkbox } from '@mui/material';
import { Divider } from '@mui/material';
import { Grid } from '@mui/material';
import { Stack } from '@mui/material';
import { Tab } from '@mui/material';
import { Table } from '@mui/material';
import { TableBody } from '@mui/material';
import { TableCell } from '@mui/material';
import { TableContainer } from '@mui/material';
import { TableHead } from '@mui/material';
import { TableRow } from '@mui/material';
import { Tabs } from '@mui/material';
import LoadingButton from '@mui/lab/LoadingButton';

export class OrderLaserPage extends Component {
    constructor(props) {
        super(props);

        this.handleOrderTabChange = this.handleOrderTabChange.bind(this);
        this.handlePartRowClick = this.handlePartRowClick.bind(this);
        this.handlePackageRowClick = this.handlePackageRowClick.bind(this);
        this.handleOrderSubmit = this.handleOrderSubmit.bind(this);
        this.handleResponseDialogClose = this.handleResponseDialogClose.bind(this);
        this.handleOrderOwnership = this.handleOrderOwnership.bind(this);
        this.handleOrderOwnershipDialogClose = this.handleOrderOwnershipDialogClose.bind(this);
        this.handleAccountId = this.handleAccountId.bind(this);

        this.state = {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            accountId: null,
            parts: [],
            packages: [],
            orderTabsValue: 0,

            arePartsLoading: true,
            arePackagesLoading: true,

            timeResult: null,
            priceResult: null,
            priceWithoutDiscountResult: null,

            partsIndeterminate: false,
            packagesIndeterminate: false,

            allParts: false,
            allPackages: false,

            isSubmitable: false,
            responseDialogOpen: false,
            responseErrors: [],

            isAuthorizedToChangeOrderOwner: false,

            changeOrderOwnerDialogOpen: false,
        };
    }

    handleAccountId(id) {
        this.setState({ accountId: id });
    }

    async componentDidMount() {
        this.checkChangeOrderOwnerPrivilege();

        let gender = '';
        await this.getGender().then((data) => { gender = data.gender });

        let parts = [];
        await this.getParts(gender).then((data) => {
            data = data.map((v, i) => {
                v.checked = false
                return v;
            });
            parts = data;
        });
        let packages = [];
        await this.getPackages(gender).then((data) => {
            data = data.map((v, i) => {
                v.checked = false
                return v;
            });
            packages = data;
        });

        await updateState(this, { parts: parts, arePartsLoading: false });
        await updateState(this, { packages: packages, arePackagesLoading: false });
    }

    checkChangeOrderOwnerPrivilege() {
        getJsonData('/privileges')
            .then((res) => {
                return res.json();
            })
            .then((data) => {
                if (data.indexOf('laserOrderCreate') !== -1 && data.indexOf('accountRead') !== -1) {
                    this.setState({ isAuthorizedToChangeOrderOwner: true });
                }
            });
    }

    handleOrderOwnership(e) {
        this.setState({ changeOrderOwnerDialogOpen: true });
    }

    handleOrderOwnershipDialogClose(e) {
        this.setState({ changeOrderOwnerDialogOpen: false });
    }

    async getGender() {
        return getJsonData('/account', { 'X-CSRF-TOKEN': this.state.token })
            .then((res) => res.json());
    }

    async getParts(gender) {
        return getJsonData('/laser/parts?gender=' + gender, { 'X-CSRF-TOKEN': this.state.token })
            .then((res) => res.json());
    }

    async getPackages(gender) {
        return getJsonData('/laser/packages?gender=' + gender, { 'X-CSRF-TOKEN': this.state.token })
            .then((res) => res.json());
    }

    handleOrderTabChange(e, newValue) {
        this.setState({ orderTabsValue: newValue });
        if (newValue === 4) {
            this.hendleResult();
        }
    }

    hendleResult() {
        let parts = this.collectSelectedParts();
        let packages = this.collectSelectedPackages();


        if (this.isDisabledSubmitOrderButton(parts, packages)) {
            return;
        }

        postJsonData('/laser/time-calculation', { parts: parts, packages: packages }, { 'X-CSRF-TOKEN': this.state.token })
            .then(((res) => {
                return res.text();
            }))
            .then((data) => {
                this.setState({ timeResult: data });
            });

        postJsonData('/laser/price-calculation', { parts: parts, packages: packages }, { 'X-CSRF-TOKEN': this.state.token })
            .then(((res) => {
                return res.json();
            }))
            .then((data) => {
                this.setState({ priceResult: data.price, priceWithoutDiscountResult: data.priceWithoutDiscount });
            });

    }

    collectSelectedParts() {
        let parts = this.state.parts;
        let selectedParts = [];
        for (let i = 0; i < parts.length; i++) {
            const part = parts[i];
            if (part.checked) {
                selectedParts.push(part.name);
            }
        }
        return selectedParts;
    }

    collectSelectedPackages() {
        let packages = this.state.packages;
        let selectedPackages = [];
        for (let i = 0; i < packages.length; i++) {
            const onePackage = packages[i];
            if (onePackage.checked) {
                selectedPackages.push(onePackage.name);
            }
        }
        return selectedPackages;
    }

    getColumns(data = []) {
        if (Array.isArray(data)) {
            for (let i = 0; i < data.length; i++) {
                const element = data[i];
                return this.makeColumn(element);
            }
        } else {
            if (typeof data === 'object') {
                return this.makeColumn(data);
            } else {
                return [];
            }
        }
    }

    makeColumn(obj) {
        let columns = [{
            align: 'center',
            label: (props = {}) => {
                return <Checkbox {...props} />
            }
        }];
        for (const k in obj) {
            if (Object.hasOwnProperty.call(obj, k)) {
                if ((['id', 'created_at', 'updated_at', 'checked']).includes(k)) {
                    continue;
                }

                let align = '';
                if (!isNaN(k)) {
                    align = 'right';
                } else {
                    align = this.props.currentLocaleDirection === 'ltr' ? 'left' : 'right';
                }

                columns.push({
                    label: k,
                    align: align
                });
            }
        }

        return columns;
    }

    handlePartRowClick(e, stateNum = 0) {
        this.setState({ priceResult: null, priceWithoutDiscountResult: null, timeResult: null, isSubmitable: false });
        this.handleRowClick('parts', e.target.checked, e.target.value, stateNum);
    }

    handlePackageRowClick(e, stateNum = 0) {
        this.setState({ priceResult: null, priceWithoutDiscountResult: null, timeResult: null, isSubmitable: false });
        this.handleRowClick('packages', e.target.checked, e.target.value, stateNum);
    }

    async handleRowClick(stateKey, shouldAdd = true, value, stateNum) {
        if (value === 'all') {
            for (let i = 0; i < this.state[stateKey].length; i++) {
                await updateState(this, (state) => {
                    state[stateKey][i].checked = shouldAdd;
                    return state;
                });
            }
        } else {
            await updateState(this, (state) => {
                state[stateKey][stateNum].checked = shouldAdd;
                return state;
            });
        }
        await this.checkIndetermination(stateKey);
    }

    checkIndetermination(stateKey) {
        return new Promise(async (resolve) => {
            let allSame = true;
            let previousValue = null;

            for (let i = 0; i < this.state[stateKey].length; i++) {
                const elm = this.state[stateKey][i];

                if (previousValue === null) {
                    previousValue = elm.checked;
                    continue;
                }

                if (elm.checked !== previousValue) {
                    allSame = false;
                }

                previousValue = elm.checked;
            }

            await updateState(this, (state) => {
                state[stateKey + 'Indeterminate'] = !allSame;
                if (allSame) {
                    state['all' + ucFirstLetterFirstWord(stateKey)] = previousValue;
                }
                return state;
            });
            resolve();
        });

    }

    handleOrderSubmit(e) {
        let data = {
            businessName: 'laser',
            parts: this.collectSelectedParts(),
            packages: this.collectSelectedPackages()
        };

        if (this.state.accountId !== null) {
            data.accountId = this.state.accountId;
        }

        if (this.isDisabledSubmitOrderButton(data.parts, data.packages)) {
            return;
        }

        postJsonData('/order', data, { 'X-CSRF-TOKEN': this.state.token })
            .then((res) => {
                if (res.status === 200 && res.redirected) {
                    alert(translate('pages/orders/order/redirectMessage', this.props.currentLocaleName));
                    window.location.replace(res.url);
                } else {
                    if (res.headers.get('Content-Type') === 'application/json') {
                        return res.json();
                    } else {
                        return res.text();
                    }
                }
            })
            .then((data) => {
                let collectedData = collectMessagesFromResponse(data);
                if (collectedData !== false) {
                    this.setState({ responseErrors: makeFormHelperTextComponents(collectedData), responseDialogOpen: true });
                }
            });
    }

    isDisabledSubmitOrderButton(parts, packages) {
        if (parts.length === 0 && packages.length === 0) {
            this.setState({ isSubmitable: false });
            return true;
        } else {
            this.setState({ isSubmitable: true });
            return false;
        }
    }

    handleResponseDialogClose() {
        this.setState({ responseDialogOpen: false });
    }

    render() {
        return (
            <>
                <Grid container spacing={1} sx={{ minHeight: '100vh' }}>
                    <Grid item xs={12} >
                        <Header
                            title={<Link to='/' style={{ textDecoration: 'none', color: 'white' }} >{translate('pages/orders/laser/title', this.props.currentLocaleName)}</ Link>}
                            currentLocaleName={this.props.currentLocaleName}
                            isLogInPage={false}
                        />
                    </Grid>
                    <Grid item xs={12} >
                        <Grid container >
                            <Grid item xs >
                            </Grid>
                            <Grid item xs={12} sm={9} md={6} >
                                <Box sx={{ borderBottom: 1, borderColor: 'divider' }}>
                                    <Tabs value={this.state.orderTabsValue} onChange={this.handleOrderTabChange} variant="scrollable" scrollButtons={true} allowScrollButtonsMobile>
                                        <Tab label={translate('pages/orders/order/part/plural/ucFirstLetterFirstWord', this.props.currentLocaleName)} id={'order-tab-' + 0} />
                                        <Tab label={translate('pages/orders/order/package/plural/ucFirstLetterFirstWord', this.props.currentLocaleName)} id={'order-tab-' + 1} />
                                        <Tab label={translate('pages/orders/order/selected-part/plural/ucFirstLetterAllWords', this.props.currentLocaleName)} id={'order-tab-' + 2} />
                                        <Tab label={translate('pages/orders/order/selected-package/plural/ucFirstLetterAllWords', this.props.currentLocaleName)} id={'order-tab-' + 3} />
                                        <Tab label={translate('general/result/single/ucFirstLetterFirstWord', this.props.currentLocaleName)} id={'order-tab-' + 4} />
                                    </Tabs>
                                </Box>
                                <TabPanel id={'order-tabPanel-' + 0} value={this.state.orderTabsValue} index={0}>
                                    {!this.state.arePartsLoading &&
                                        <TableContainer
                                            sx={{ maxHeight: 440 }}
                                        >
                                            <Table stickyHeader >
                                                <TableHead>
                                                    <TableRow>
                                                        {this.getColumns(this.state.parts).map((column, i) => {
                                                            let content;
                                                            if (i === 0) {
                                                                content = column.label({ checked: this.state.allParts, indeterminate: this.state.partsIndeterminate, onChange: this.handlePartRowClick, value: 'all' });
                                                            } else {
                                                                content = column.label;
                                                            }

                                                            return <TableCell
                                                                key={i}
                                                                align={column.align}
                                                            >
                                                                {content}
                                                            </TableCell>
                                                        })}
                                                    </TableRow>
                                                </TableHead>
                                                <TableBody>
                                                    {this.state.parts.map((v, i, array) => {
                                                        return (
                                                            <TableRow hover role="checkbox" tabIndex={-1} key={i}>
                                                                {this.getColumns(this.state.parts).map((column, i2) => {
                                                                    let value = '';
                                                                    if (i2 === 0) {
                                                                        value = column.label({ checked: this.state.parts[i].checked, onChange: (e) => this.handlePartRowClick(e, i), value: v.name });
                                                                    } else {
                                                                        value = v[column.label];
                                                                    }

                                                                    return (
                                                                        <TableCell key={i2} align={column.align}>
                                                                            {column.format && typeof value === 'number'
                                                                                ? column.format(value)
                                                                                : value}
                                                                        </TableCell>
                                                                    );
                                                                })}
                                                            </TableRow>
                                                        );
                                                    })}
                                                </TableBody>
                                            </Table>
                                        </TableContainer>
                                    }
                                </TabPanel>
                                <TabPanel id={'order-tabPanel-' + 1} value={this.state.orderTabsValue} index={1}>
                                    {!this.state.arePackagesLoading &&
                                        <TableContainer
                                            sx={{ maxHeight: 440 }}
                                        >
                                            <Table stickyHeader >
                                                <TableHead>
                                                    <TableRow>
                                                        {this.getColumns(this.state.packages).map((column, i) => {
                                                            let content;
                                                            if (i === 0) {
                                                                content = column.label({ checked: this.state.allPackages, indeterminate: this.state.packagesIndeterminate, onChange: this.handlePackageRowClick, value: 'all' });
                                                            } else {
                                                                content = column.label;
                                                            }

                                                            return <TableCell
                                                                key={i}
                                                                align={column.align}
                                                            >
                                                                {content}
                                                            </TableCell>
                                                        })}
                                                    </TableRow>
                                                </TableHead>
                                                <TableBody>
                                                    {this.state.packages.map((v, i, array) => {
                                                        return (
                                                            <TableRow hover role="checkbox" tabIndex={-1} key={i}>
                                                                {this.getColumns(this.state.packages).map((column, i2) => {
                                                                    let value = '';
                                                                    if (i2 === 0) {
                                                                        value = column.label({ checked: this.state.packages[i].checked, onChange: (e) => this.handlePackageRowClick(e, i), value: v.name });
                                                                    } else {
                                                                        value = v[column.label];
                                                                    }

                                                                    if (Array.isArray(value)) {
                                                                        let temp = '';
                                                                        value.forEach((obj) => {
                                                                            if (temp === '') {
                                                                                temp = obj.name;
                                                                            }
                                                                            temp += ', ' + obj.name;
                                                                        });
                                                                        value = temp;
                                                                    }

                                                                    return (
                                                                        <TableCell key={i2} align={column.align}>

                                                                            {column.format && typeof value === 'number'
                                                                                ? column.format(value)
                                                                                : value}
                                                                        </TableCell>
                                                                    );
                                                                })}
                                                            </TableRow>
                                                        );
                                                    })}
                                                </TableBody>
                                            </Table>
                                        </TableContainer>
                                    }
                                </TabPanel>
                                <TabPanel id={'order-tabPanel-' + 2} value={this.state.orderTabsValue} index={2}>
                                    {!this.state.arePartsLoading &&
                                        <TableContainer
                                            sx={{ maxHeight: 440 }}
                                        >
                                            <Table stickyHeader >
                                                <TableHead>
                                                    <TableRow>
                                                        {this.getColumns(this.state.parts).map((column, i) => {
                                                            let content;
                                                            if (i === 0) {
                                                                return null;
                                                            } else {
                                                                content = column.label;
                                                            }

                                                            return <TableCell
                                                                key={i}
                                                                align={column.align}
                                                            >
                                                                {content}
                                                            </TableCell>
                                                        })}
                                                    </TableRow>
                                                </TableHead>
                                                <TableBody>
                                                    {this.state.parts.map((v, i, array) => {
                                                        if (!v.checked) {
                                                            return null;
                                                        }
                                                        return (
                                                            <TableRow hover role="checkbox" tabIndex={-1} key={i}>
                                                                {this.getColumns(this.state.parts).map((column, i2) => {
                                                                    let value = '';
                                                                    if (i2 === 0) {
                                                                        return null;
                                                                    } else {
                                                                        value = v[column.label];
                                                                    }

                                                                    if (Array.isArray(value)) {
                                                                        let temp = '';
                                                                        value.forEach((obj) => {
                                                                            if (temp === '') {
                                                                                temp = obj.name;
                                                                            }
                                                                            temp += ', ' + obj.name;
                                                                        });
                                                                        value = temp;
                                                                    }

                                                                    return (
                                                                        <TableCell key={i2} align={column.align}>

                                                                            {column.format && typeof value === 'number'
                                                                                ? column.format(value)
                                                                                : value}
                                                                        </TableCell>
                                                                    );
                                                                })}
                                                            </TableRow>
                                                        );
                                                    })}
                                                </TableBody>
                                            </Table>
                                        </TableContainer>
                                    }
                                </TabPanel>
                                <TabPanel id={'order-tabPanel-' + 3} value={this.state.orderTabsValue} index={3}>
                                    {!this.state.arePackagesLoading &&
                                        <TableContainer
                                            sx={{ maxHeight: 440 }}
                                        >
                                            <Table stickyHeader >
                                                <TableHead>
                                                    <TableRow>
                                                        {this.getColumns(this.state.packages).map((column, i) => {
                                                            let content;
                                                            if (i === 0) {
                                                                return null;
                                                            } else {
                                                                content = column.label;
                                                            }

                                                            return <TableCell
                                                                key={i}
                                                                align={column.align}
                                                            >
                                                                {content}
                                                            </TableCell>
                                                        })}
                                                    </TableRow>
                                                </TableHead>
                                                <TableBody>
                                                    {this.state.packages.map((v, i, array) => {
                                                        if (!v.checked) {
                                                            return null;
                                                        }
                                                        return (
                                                            <TableRow hover role="checkbox" tabIndex={-1} key={i}>
                                                                {this.getColumns(this.state.packages).map((column, i2) => {
                                                                    let value = '';
                                                                    if (i2 === 0) {
                                                                        return null;
                                                                    } else {
                                                                        value = v[column.label];
                                                                    }

                                                                    if (Array.isArray(value)) {
                                                                        let temp = '';
                                                                        value.forEach((obj) => {
                                                                            if (temp === '') {
                                                                                temp = obj.name;
                                                                            }
                                                                            temp += ', ' + obj.name;
                                                                        });
                                                                        value = temp;
                                                                    }

                                                                    return (
                                                                        <TableCell key={i2} align={column.align}>

                                                                            {column.format && typeof value === 'number'
                                                                                ? column.format(value)
                                                                                : value}
                                                                        </TableCell>
                                                                    );
                                                                })}
                                                            </TableRow>
                                                        );
                                                    })}
                                                </TableBody>
                                            </Table>
                                        </TableContainer>
                                    }
                                </TabPanel>
                                <TabPanel id={'order-tabPanel-' + 4} value={this.state.orderTabsValue} index={4}>
                                    <Stack
                                        direction="column"
                                        divider={<Divider orientation="horizontal" />}
                                        spacing={2}
                                    >
                                        <div>{translate('pages/orders/order/timeResult', this.props.currentLocaleName)}{this.state.timeResult === null ? <LoadingButton loading></LoadingButton> : this.state.timeResult}</div>
                                        <div>{translate('pages/orders/order/priceResult', this.props.currentLocaleName)}{this.state.priceResult === null ? <LoadingButton loading></LoadingButton> : this.state.priceResult}</div>
                                        <div>{translate('pages/orders/order/priceWithoutDiscountResult', this.props.currentLocaleName)}{this.state.priceWithoutDiscountResult === null ? <LoadingButton loading></LoadingButton> : this.state.priceWithoutDiscountResult}</div>
                                        {this.state.isAuthorizedToChangeOrderOwner && <div><Button fullWidth variant='contained' onClick={this.handleOrderOwnership} >{translate('pages/orders/order/anotherUserButton', this.props.currentLocaleName)}</Button></div>}
                                        <div><Button fullWidth variant='contained' onClick={this.handleOrderSubmit} disabled={!this.state.isSubmitable}>{translate('pages/orders/order/submitOrder', this.props.currentLocaleName)}</Button></div>
                                    </Stack>
                                </TabPanel>

                                <SlidingDialog
                                    open={this.state.responseDialogOpen}
                                    slideTrigger={<div></div>}
                                    onClose={this.handleResponseDialogClose}
                                >
                                    {this.state.responseErrors}
                                </SlidingDialog>

                                <ChangeOwnershipButton
                                    open={this.state.changeOrderOwnerDialogOpen}
                                    onClose={this.handleOrderOwnershipDialogClose}
                                    handleAccountId={this.handleAccountId}
                                />
                            </Grid>
                            <Grid item xs >
                            </Grid>
                        </Grid>
                    </Grid>
                    <Grid item xs={12} >
                        {/* <Footer /> */}
                    </Grid>
                </Grid>
            </>
        )
    }
}

export default OrderLaserPage
