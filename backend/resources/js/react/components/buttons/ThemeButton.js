import React, { Component } from 'react';

import { themes } from '../themeContenxt.js';

import Dropdown from '../Menus/DropDown.js';
import { translate } from '../../traslation/translate.js';
import { gotTheme } from '../../../redux/reducers/theme.js';
import store from '../../../redux/store.js';
import { connect } from 'react-redux';

export class ThemeButton extends Component {
    render() {
        let theme = store.getState().theme.theme;
        return (
            <Dropdown
                buttonInnerContent={translate('general/' + theme + '/single/ucFirstLetterFirstWord')}
                menuItems={this.makeItems()}
                buttonProps={this.props.buttonProps}
                menuItemClickHandler={(e) => { this.props.dispatch(gotTheme(e.target.getAttribute('value'))); }}
            />
        )
    }

    makeItems() {
        let items = [];
        for (const k in themes) {
            let key = k.slice(0, k.indexOf('-'));
            if (!items.includes(key)) {
                items.push(key);
            }
        }

        return items.map((item, k) => {
            return {
                props: { value: item },
                innerText: translate('general/' + item + '/single/ucFirstLetterFirstWord')
            }
        });
    }
}

export default connect(null)(ThemeButton)
