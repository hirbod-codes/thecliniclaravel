import React, { Component } from 'react';

import { ThemeContext, themes } from '../themeContenxt.js';

import Dropdown from '../Menus/DropDown.js';

export class ThemeButton extends Component {
    constructor(props) {
        super(props);
        this.themeHandler.bind(this);
    }

    themeHandler(e, changeTheme) {
        changeTheme(e.target.getAttribute('value'));
    }

    render() {
        return (
            <ThemeContext.Consumer>
                {({ theme, changeTheme, currentTheme, isThemeLoading }) => {
                    return <Dropdown
                        buttonInnerContent={currentTheme}
                        menuItems={this.makeItems()}
                        isLoading={isThemeLoading}
                        buttonProps={this.props.buttonProps}
                        menuItemClickHandler={(e) => this.themeHandler(e, changeTheme)}
                    />
                }}
            </ThemeContext.Consumer>
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
                innerText: item
            }
        });
    }
}

export default ThemeButton
