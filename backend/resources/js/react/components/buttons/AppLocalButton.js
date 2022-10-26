import React, { Component } from 'react'

import { Dropdown } from '../Menus/DropDown.js';
import { LocaleContext } from '../localeContext';
import { translate } from '../../traslation/translate.js';

export class AppLocalButton extends Component {
    constructor(props) {
        super(props);

        this.state = {
            anchorEl: null,
            open: false
        };
    }

    render() {
        return (
            <LocaleContext.Consumer>
                {({ locales, currentLocale, isLocaleLoading, changeLocale }) => {
                    return <Dropdown
                        buttonInnerContent={translate('general/' + currentLocale.longName + '/single/ucFirstLetterFirstWord')}
                        menuItems={this.makeItems(locales)}
                        isLoading={isLocaleLoading}
                        buttonProps={this.props.buttonProps}
                        menuItemClickHandler={(e) => { changeLocale(e.target.getAttribute('value')); }}
                    />
                }}
            </LocaleContext.Consumer>
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

AppLocalButton.contextType = LocaleContext;

export default AppLocalButton
