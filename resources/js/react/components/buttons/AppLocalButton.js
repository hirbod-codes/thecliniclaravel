import React, { Component } from 'react'

import { Dropdown } from '../Menus/DropDown.js';
import { LocaleContext } from '../localeContext';

export class AppLocalButton extends Component {
    constructor(props) {
        super(props);
        this.localeClickHandler.bind(this);

        this.state = {
            anchorEl: null,
            open: false
        };
    }

    localeClickHandler(e, changeLocale) {
        changeLocale(e.target.getAttribute('value'));
    }

    render() {
        return (
            <LocaleContext.Consumer>
                {({ locales, currentLocale, isLocaleLoading, changeLocale }) => {
                    return <Dropdown
                        buttonInnerContent={currentLocale.longName}
                        menuItems={this.makeItems(locales)}
                        isLoading={isLocaleLoading}
                        buttonProps={this.props.buttonProps}
                        menuItemClickHandler={(e) => {changeLocale(e.target.getAttribute('value'));}}
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
                    innerText: locale.longName
                });
            }
        }

        return items;
    }
}

AppLocalButton.contextType = LocaleContext;

export default AppLocalButton
