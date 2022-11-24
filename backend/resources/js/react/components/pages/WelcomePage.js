import React, { Component } from 'react'
import { connect } from 'react-redux'

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

const mapStateToProps = state => ({
    redux: state
});

export default connect(mapStateToProps)(WelcomePage)
