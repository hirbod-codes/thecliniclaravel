import React, { Component } from 'react'

import { translate } from '../../traslation/translate'
import Header from '../headers/Header'

export class WelcomePage extends Component {
    render() {
        return (
            <>
                <Header title={translate('general/welcome/single/ucFirstLetterFirstWord')} />
            </>
        )
    }
}

export default WelcomePage
