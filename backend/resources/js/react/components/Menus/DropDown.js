import React, { Component } from 'react';

import LoadingButton from '@mui/lab/LoadingButton';
import { Button, Menu, MenuItem } from '@mui/material';
import { updateState } from '../helpers';
import { connect } from 'react-redux';

export class Dropdown extends Component {
    constructor(props) {
        super(props);
        this.state = {
            buttonInnerContent: null,
            anchorEl: null,
            open: false
        };

        this.setAnchorEl = this.setAnchorEl.bind(this);
        this.dropDownOpenHandler = this.dropDownOpenHandler.bind(this);
        this.dropDownCloseHandler = this.dropDownCloseHandler.bind(this);
    }

    async shouldComponentUpdate(n) {
        if (this.state.buttonInnerContent !== null && (this.props.buttonInnerContent === null || this.props.buttonInnerContent === undefined) && n.buttonInnerContent !== null && n.buttonInnerContent !== undefined) {
            await updateState(this, { buttonInnerContent: null });
        }

        return true;
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

    dropDownClickHandler(innerText) {
        this.setState({ buttonInnerContent: innerText });
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

        return <MenuItem key={key} onClick={(e) => { this.props.menuItemClickHandler(e); this.dropDownCloseHandler(); this.dropDownClickHandler(innerText) }} {...props} {...{ disabled: disabled }}>{innerText}</MenuItem>;
    }

    render() {
        if (this.props.isLoading) {
            return (
                <>
                    <LoadingButton loading variant='contained'></LoadingButton>
                </>
            );
        }

        let menuItems = this.props.menuItems.map((v, i) => {
            return this.getMenuItem(v, i);
        });

        return (
            <>
                <Button type='button' {...this.props.buttonProps} onClick={this.dropDownOpenHandler}>
                    {this.props.buttonInnerContent ? this.props.buttonInnerContent : (this.state.buttonInnerContent ? this.state.buttonInnerContent : this.props.menuItems[0])}
                </Button>
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

const mapStateToProps = state => ({
    redux: state
});

export default connect(mapStateToProps)(Dropdown);
