import React, { Component } from 'react'

import PropTypes from 'prop-types';

import { TextField } from '@mui/material';
import { translate } from '../../../traslation/translate';
import { resolveTimeZone, updateState } from '../../helpers';
import { DateTime } from 'luxon';
import { LocaleContext } from '../../localeContext';

/**
 * TimePeriodComponent
 * @augments {Component<Props, State>}
 */
export class TimePeriodComponent extends Component {
    static propTypes = {
        id: PropTypes.number.isRequired,
        onTimePeriodFulfillment: PropTypes.func.isRequired,
        onTimePeriodNotFulfilled: PropTypes.func.isRequired,
        isDisabled: PropTypes.bool.isRequired,
        weekDayName: PropTypes.string.isRequired,
        weekDayNames: PropTypes.array.isRequired,

        start: PropTypes.string,
        end: PropTypes.string,
    }

    constructor(props) {
        super(props);

        this.handleStartingTime = this.handleStartingTime.bind(this);
        this.handleEndingTime = this.handleEndingTime.bind(this);

        this.checkCompletion = this.checkCompletion.bind(this);

        this.state = {
            start: '',
            end: '',
        };
    }

    async componentDidMount() {
        console.log(this.props);
        if (this.props.start !== undefined && this.state.start === '') {
            let start = new Date(this.props.start);
            await updateState(this, { start: start.toLocaleString('en-US', { hour: '2-digit', hourCycle: 'h24', minute: '2-digit' }) });
        }

        if (this.props.end !== undefined && this.state.end === '') {
            let end = new Date(this.props.end);
            await updateState(this, { end: end.toLocaleString('en-US', { hour: '2-digit', hourCycle: 'h24', minute: '2-digit' }) });
        }
    }

    async componentDidUpdate(prevProps) {
        if (prevProps.weekDayName !== this.props.weekDayName) {
            this.checkCompletion();
        }
    }

    async handleStartingTime(e) {
        await updateState(this, { start: e.target.value });
        this.checkCompletion();
    }

    async handleEndingTime(e) {
        await updateState(this, { end: e.target.value });
        this.checkCompletion();
    }

    checkCompletion() {
        if (this.state.start && this.state.end) {
            const locale = LocaleContext._currentValue.currentLocale.shortName;
            let date = DateTime.local({ zone: resolveTimeZone(locale) });
            let safety = 0
            while (date.weekdayLong !== this.props.weekDayName && safety < 500) {
                date = date.plus({ days: 1 });
                safety++
            }

            this.props.onTimePeriodFulfillment({
                start: date.toFormat('yyyy-MM-dd') + ' ' + this.state.start + ':00',
                end: date.toFormat('yyyy-MM-dd') + ' ' + this.state.end + ':00'
            }, this.props.id);
            return true;
        } else {
            this.props.onTimePeriodNotFulfilled(null, this.props.id);
            return false;
        }
    }

    render() {
        return (
            <>
                <TextField
                    disabled={this.props.isDisabled}
                    type='time'
                    key={0}
                    value={this.state.start}
                    color={this.state.start === '' ? 'error' : 'primary'}
                    onInput={this.handleStartingTime}
                    label={translate('general/starting-time/single/ucFirstLetterAllWords')}
                    variant='standard'
                    sx={{ m: 1 }}
                />
                <TextField
                    disabled={this.props.isDisabled}
                    type='time'
                    key={1}
                    value={this.state.end}
                    color={this.state.end === '' ? 'error' : 'primary'}
                    onInput={this.handleEndingTime}
                    label={translate('general/ending-time/single/ucFirstLetterAllWords')}
                    variant='standard'
                    sx={{ m: 1 }}
                />
            </>
        )
    }
}

export default TimePeriodComponent
