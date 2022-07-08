import { TextField } from '@mui/material'
import React, { Component } from 'react'
import { translate } from '../../../traslation/translate'
import { updateState } from '../../helpers';
import { LocaleContext } from '../../localeContext'

export class TimePeriodInputComponents extends Component {
    constructor(props) {
        super(props);

        this.handleStartingTime = this.handleStartingTime.bind(this);
        this.handleEndingTime = this.handleEndingTime.bind(this);

        this.getDate = this.getDate.bind(this);

        this.state = {
            inputDataStructure: { start: '', end: '' },
        };
    }

    async handleStartingTime(e) {
        await updateState(this, { inputDataStructure: { start: this.getDate() + ' ' + e.target.value + ':00', end: this.state.inputDataStructure['end'] } });

        if (this.checkFulfillment()) {
            this.props.onComponentFulfillment(this.props.id);
        }

        this.props.addTimePeriod(this.props.id, this.state.inputDataStructure);
    }

    async handleEndingTime(e) {
        await updateState(this, { inputDataStructure: { start: this.state.inputDataStructure['start'], end: this.getDate() + ' ' + e.target.value + ':00' } });

        if (this.checkFulfillment()) {
            this.props.onComponentFulfillment(this.props.id);
        }

        this.props.addTimePeriod(this.props.id, this.state.inputDataStructure);
    }

    getDate() {
        if (typeof this.props.day !== 'string' || this.props.day === '') {
            return '';
        }

        let date = new Date();
        date.toLocaleString('en-US', { timeZone: 'UTC' });
        while (date.getUTCDay() !== this.props.week.indexOf(this.props.day)) {
            date.setUTCDate(date.getUTCDate() + 1);
        }

        return date.getUTCFullYear() + '-' + (date.getUTCMonth() + 1) + '-' + date.getUTCDate();
    }

    checkFulfillment() {
        return this.state.inputDataStructure['start'] !== '' && this.state.inputDataStructure['end'] !== '';
    }

    render() {
        return (
            <LocaleContext.Consumer>
                {({ locales, currentLocale, isLocaleLoading, changeLocale }) => {
                    return <>
                        <TextField type='time'
                            color={this.state.inputDataStructure[0] === '' ? 'error' : 'primary'}
                            id={'starting-time-' + this.props.dayId + '-' + this.props.id}
                            onInput={this.handleStartingTime}
                            label={translate('general/starting-time/single/ucFirstLetterAllWords', currentLocale.shortName)}
                            variant='standard'
                            sx={{ m: 1 }}
                        />
                        <TextField type='time'
                            color={this.state.inputDataStructure[1] === '' ? 'error' : 'primary'}
                            id={'ending-time-' + this.props.dayId + '-' + this.props.id}
                            onInput={this.handleEndingTime}
                            label={translate('general/ending-time/single/ucFirstLetterAllWords', currentLocale.shortName)}
                            variant='standard'
                            sx={{ m: 1 }}
                        />
                    </>
                }}
            </LocaleContext.Consumer>
        )
    }
}

export default TimePeriodInputComponents
