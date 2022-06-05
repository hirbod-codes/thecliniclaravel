import React, { Component } from 'react'

import { Dropdown } from '../Menus/DropDown.js';
import { ThemeContext } from '../themeContenxt';
import { LocaleContext, locales } from '../localeContext';

export class AppLocalButton extends Component {
    constructor(props) {
        super(props);
        this.localeClickHandler.bind(this);
        this.state = {
            currentLocale: {
                longName: locales.en.longName
            },

            anchorEl: null,
            open: false
        };
    }

    localeClickHandler(e, changeLocale, changeTheme, currentTheme) {
        let newLocale = e.target.getAttribute('value');
        changeLocale(newLocale);

        this.updateThemedirection(locales[newLocale].direction, changeTheme, currentTheme)
    }

    updateThemedirection(direction, changeTheme, currentTheme) {
        changeTheme(currentTheme + '-' + direction);
        document.dir = direction;
    }

    render() {
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

        return (
            <LocaleContext.Consumer>
                {({ currentLocale, changeLocale }) => (
                    <ThemeContext.Consumer>
                        {({ theme, changeTheme, currentTheme }) => (
                            <Dropdown
                                buttonInnerContent={this.state.currentLocale.longName}
                                buttonProps={this.props.buttonProps}
                                isLoading={false}
                                menuItems={items}
                                menuItemClickHandler={(e) => this.localeClickHandler(e, changeLocale, changeTheme, currentTheme)}
                            />
                        )}
                    </ThemeContext.Consumer>
                )}
            </LocaleContext.Consumer>
        )
    }
}

AppLocalButton.contextType = LocaleContext;

export default AppLocalButton
