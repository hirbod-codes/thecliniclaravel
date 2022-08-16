import React, { Component } from 'react'

import { translate } from '../../traslation/translate'
import Header from '../headers/Header'

export class WelcomePage extends Component {
    render() {
        return (
            <>
                <Header
                    title={translate('general/welcome/single/ucFirstLetterFirstWord')}
                    onLogout={this.props.onLogout}
                    isAuthenticated={this.props.isAuthenticated}
                    isAuthenticationLoading={this.props.isAuthenticationLoading}
                    navigator={this.props.navigator}
                />
            </>
        )
    }
}

export default WelcomePage
