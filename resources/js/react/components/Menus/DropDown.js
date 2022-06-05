import React, { Component } from 'react';

import LoadingButton from '@mui/lab/LoadingButton';
import { Button, Menu, MenuItem } from '@mui/material';

export class Dropdown extends Component {
    constructor(props) {
        super(props);
        this.state = {
            buttonInnerContent: props.buttonInnerContent,
            anchorEl: null,
            open: false
        };

        this.setAnchorEl = this.setAnchorEl.bind(this);
        this.dropDownOpenHandler = this.dropDownOpenHandler.bind(this);
        this.dropDownCloseHandler = this.dropDownCloseHandler.bind(this);
    }

    setAnchorEl(value) {
        this.setState({
            anchorEl: value,
            open: !this.state.open
        })
    }

    dropDownOpenHandler(e) {
        this.setAnchorEl(e.target);
    }

    dropDownCloseHandler() {
        this.setAnchorEl(null);
    }

    dropDownClickHandler(e) {
        this.setState({ buttonInnerContent: e.target.innerText });
    }

    getMenuItem(options, key, disabled = false) {
        let innerText = {};
        let props = {};
        if (Object.keys(options).includes('props')) {
            props = options.props;
        }
        if (Object.keys(options).includes('innerText')) {
            innerText = options.innerText;
        }

        return <MenuItem key={key} onClick={(e) => { this.props.menuItemClickHandler(e); this.dropDownCloseHandler(); this.dropDownClickHandler(e) }} {...props} {...{ disabled: disabled }}>{innerText}</MenuItem>;
    }

    render() {
        if (this.props.isLoading) {
            return (
                <>
                    <LoadingButton loading variant='contained'></LoadingButton>
                </>
            );
        }

        let menuItems = [];
        // if (this.props.selected) {
        //     menuItems.push(this.getMenuItem(this.props.selected, this.props.menuItems.length, true));
        // }
        for (let i = 0; i < this.props.menuItems.length; i++) {
            let menuItem = this.props.menuItems[i];

            menuItems.push(this.getMenuItem(menuItem, i));
        }

        return (
            <>
                <Button type='button' variant={this.props.variant ? this.props.variant : ''} {...this.props.buttonProps} onClick={this.dropDownOpenHandler}>{(this.state.buttonInnerContent ? this.state.buttonInnerContent : this.props.buttonInnerContent)}</Button>
                <Menu
                    anchorEl={this.state.anchorEl}
                    open={this.state.open}
                    onClose={this.dropDownCloseHandler}
                >
                    {menuItems}
                </Menu>
            </>
        )
    }
}

export default Dropdown;
