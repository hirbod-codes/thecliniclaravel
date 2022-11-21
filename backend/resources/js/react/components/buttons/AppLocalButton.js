import React, { Component } from 'react'

import { Dropdown } from '../Menus/DropDown.js';
import { translate } from '../../traslation/translate.js';
import store from '../../../redux/store.js';
import { connect } from 'react-redux';
import { setLocal } from '../../../redux/reducers/local.js';

export class AppLocalButton extends Component {
    constructor(props) {
        super(props);

        this.state = {
            anchorEl: null,
            open: false
        };
    }

    render() {
        let reduxStore = store.getState();

        return (
            <Dropdown
                buttonInnerContent={translate('general/' + reduxStore.local.local.longName + '/single/ucFirstLetterFirstWord')}
                menuItems={this.makeItems(reduxStore.local.locals)}
                buttonProps={this.props.buttonProps}
                menuItemClickHandler={(e) => { this.props.dispatch(setLocal(e.target.getAttribute('value'))); }}
            />
        )
    }

    makeItems(locales) {
        let items = [];

        for (const k in locales) {
            if (Object.hasOwnProperty.call(locales, k)) {
                const locale = locales[k];
                items.push({
                    props: {
                        value: locale.shortName
                    },
                    innerText: translate('general/' + locale.longName + '/single/ucFirstLetterFirstWord')
                });
            }
        }

        return items;
    }
}

export default connect(null)(AppLocalButton)
