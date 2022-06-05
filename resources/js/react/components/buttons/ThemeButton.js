import React, { Component } from 'react';

import { ThemeContext, themes } from '../themeContenxt.js';

import Dropdown from '../Menus/DropDown.js';
import { LocaleContext, locales } from '../localeContext.js';

export class ThemeButton extends Component {
    constructor(props) {
        super(props);
        this.themeHandler.bind(this);
    }

    themeHandler(e, changeTheme) {
        changeTheme(e.target.getAttribute('value'));
    }

    static contextType = LocaleContext;

    render() {
        let items = [];
        for (const k in themes) {
            let key = k.slice(0, k.indexOf('-'));
            if (!items.includes(key)) {
                items.push(key);
            }
        }

        items = items.map((item, k) => {
            return {
                props: { value: item + '-' + locales[this.context.currentLocale].direction },
                innerText: item
            }
        });

        return (
            <ThemeContext.Consumer>
                {({ theme, changeTheme, currentTheme }) => (
                    <Dropdown
                        buttonInnerContent={currentTheme}
                        buttonProps={this.props.buttonProps}
                        isLoading={false}
                        menuItems={items}
                        menuItemClickHandler={(e) => this.themeHandler(e, changeTheme)}
                    />
                )}
            </ThemeContext.Consumer>
        )
    }
}

export default ThemeButton
