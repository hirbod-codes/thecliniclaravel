import React, { Component } from 'react'

import PropTypes from 'prop-types';

import { TextField } from '@mui/material';
import { translate } from '../../../traslation/translate';
import { resolveTimeZone, updateState } from '../../helpers';
import { DateTime } from 'luxon';
import { LocaleContext } from '../../localeContext';
import { connect } from 'react-redux';

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

        start: PropTypes.string,
        end: PropTypes.string,

        minTime: PropTypes.string,
        maxTime: PropTypes.string,
    }

    constructor(props) {
        super(props);

        this.handleStartingTime = this.handleStartingTime.bind(this);
        this.handleEndingTime = this.handleEndingTime.bind(this);

        this.checkCompletion = this.checkCompletion.bind(this);

        this.state = {
            start: '',
            end: '',

            startError: false,
            endError: false,

            minTime: '',
            maxTime: '',

            locale: LocaleContext._currentValue.currentLocale.shortName,
        };
    }

    async componentDidMount() {
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
        if (this.props.minTime !== undefined && this.props.maxTime !== undefined && this.state.minTime === '' && this.state.maxTime === '') {
            let array = [this.props.minTime, this.props.maxTime].map((v, i) => {
                let temp = v.split(' ');
                if (temp.length === 2) {
                    return temp[1];
                } else {
                    return temp[0];
                }
            });
            this.setState({ minTime: array[0], maxTime: array[1] });
        }
    }

    async handleStartingTime(e) {
        if (this.state.minTime !== '' && ((new Date("2022-01-01 " + e.target.value + ':00')) < (new Date("2022-01-01 " + this.state.minTime)))) {
            this.setState({ startError: true, start: '' });
            return;
        } else {
            this.setState({ startError: false });
        }

        await updateState(this, { start: e.target.value });
        this.checkCompletion();
    }

    async handleEndingTime(e) {
        if (this.state.maxTime !== '' && ((new Date("2022-01-01 " + e.target.value + ':00')) > (new Date("2022-01-01 " + this.state.maxTime)))) {
            this.setState({ endError: true, end: '' });
            return;
        } else {
            this.setState({ endError: false });
        }

        await updateState(this, { end: e.target.value });
        this.checkCompletion();
    }

    checkCompletion() {
        if (this.state.start && this.state.end) {
            let date = DateTime.local({ zone: resolveTimeZone(this.state.locale) });
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
                    error={this.state.startError}
                    helperText={this.state.startError ? translate('generalSentences/minimum-time-range-exceeded') + ': ' + this.state.minTime : undefined}
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
                    error={this.state.endError}
                    helperText={this.state.endError ? translate('generalSentences/maximum-time-range-exceeded') + ': ' + this.state.maxTime : undefined}
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

const mapStateToProps = state => ({
    redux: state
});

export default connect(mapStateToProps)(TimePeriodComponent)
