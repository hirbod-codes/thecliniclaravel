import React, { Component } from 'react'

import PropTypes from 'prop-types';

import CloseIcon from '@mui/icons-material/Close';
import AddIcon from '@mui/icons-material/Add';
import { Box, Divider, Fab, FormControl, InputLabel, MenuItem, Select, Stack } from '@mui/material'

import { translate, ucFirstLetterFirstWord } from '../../../traslation/translate'
import { updateState } from '../../helpers';
import TimePattern from './TimePattern';

/**
 * WeeklyTimePattern
 * @augments {Component<Props, State>}
 */
export class WeeklyTimePattern extends Component {
    static propTypes = {
        id: PropTypes.number.isRequired,
        onWeekDayFulfillment: PropTypes.func.isRequired,
        onWeekDayNotFulfilled: PropTypes.func.isRequired,
        workSchdule: PropTypes.objectOf(PropTypes.array),
        weekDayNames: PropTypes.arrayOf(PropTypes.string),

        weekDay: PropTypes.string,
        timePatterns: PropTypes.arrayOf(PropTypes.object),

        isAddable: PropTypes.bool,
    }

    constructor(props) {
        super(props);

        this.onWeekDayChange = this.onWeekDayChange.bind(this);
        this.getWeekDayName = this.getWeekDayName.bind(this);
        this.isDisabled = this.isDisabled.bind(this);

        this.insertTimePeriodStack = this.insertTimePeriodStack.bind(this);
        this.removeTimePeriodStack = this.removeTimePeriodStack.bind(this);

        this.onTimePeriodFulfillment = this.onTimePeriodFulfillment.bind(this);
        this.onTimePeriodNotFulfilled = this.onTimePeriodNotFulfilled.bind(this);

        this.state = {
            weekDay: '',
            timePatterns: [],
        };
    }

    componentDidMount() {
        if (this.props.weekDayNames === undefined && this.props.workSchdule === undefined) {
            throw new Error('insufficient props for WeeklyTimePattern component');
        }

        if (this.props.weekDay !== undefined && this.props.timePatterns !== undefined) {
            this.setState({
                weekDay: this.props.weekDay,
                timePatterns: this.props.timePatterns,
            });
        } else {
            this.insertTimePeriodStack();
        }
    }

    async insertTimePeriodStack() {
        let timePatterns = this.state.timePatterns;
        timePatterns.push(null);

        await updateState(this, { timePatterns: timePatterns });

        this.checkCompletion();
    }

    async removeTimePeriodStack() {
        let timePatterns = this.state.timePatterns;
        timePatterns.pop();

        await updateState(this, { timePatterns: timePatterns });

        this.checkCompletion();
    }

    async onTimePeriodFulfillment(timePattern, key) {
        let timePatterns = this.state.timePatterns;
        timePatterns[key] = timePattern;

        await updateState(this, { timePatterns: timePatterns });

        this.checkCompletion();
    }

    async onTimePeriodNotFulfilled(timePattern, key) {
        let timePatterns = this.state.timePatterns;
        timePatterns[key] = null;

        await updateState(this, { timePatterns: timePatterns });

        this.checkCompletion();
    }

    async onWeekDayChange(e) {
        await updateState(this, { weekDay: e.target.value });

        this.checkCompletion();
    }

    getWeekDayName() { return this.state.weekDay; }

    isDisabled() { return this.state.weekDay === ''; }

    checkCompletion() {
        let v = true;
        for (let i = 0; i < this.state.timePatterns.length; i++) {
            v = this.state.timePatterns[i];

            if (v === null) {
                return false;
            }
        }

        if ((this.state.timePatterns.length === 0) && this.state.weekDay !== '') {
            this.props.onWeekDayFulfillment(this.state.weekDay, this.state.timePatterns);
            return true;
        } else {
            this.props.onWeekDayNotFulfilled(this.props.id);
            return false;
        }
    }

    render() {
        let weekDayNames = [];
        if (this.props.workSchdule !== undefined) {
            for (const k in this.props.workSchdule) {
                if (Object.hasOwnProperty.call(this.props.workSchdule, k)) {
                    weekDayNames.push(<MenuItem disabled={this.props.weekDay !== undefined} value={ucFirstLetterFirstWord(k)} key={k}>{translate('general/' + k.toLocaleLowerCase() + '/single/ucFirstLetterFirstWord')}</MenuItem>);
                }
            }
        } else {
            weekDayNames = this.props.weekDayNames.map((v, i) => <MenuItem disabled={this.props.weekDay !== undefined} value={ucFirstLetterFirstWord(v)} key={i}>{translate('general/' + v.toLocaleLowerCase() + '/single/ucFirstLetterFirstWord')}</MenuItem>)
        }

        return (
            <FormControl sx={{ backgroundColor: theme => theme.palette.secondary }}>
                <Stack
                    key={this.props.id}
                    justifyContent='space-around'
                    direction="row"
                    spacing={1}
                >
                    <Box>
                        <InputLabel id={"week-of-the-day-" + this.props.id}>{translate('pages/visits/visit/week-of-the-day')}</InputLabel>
                        <Select
                            color={this.state.weekDay === '' ? 'error' : 'primary'}
                            labelId={"week-of-the-day-" + this.props.id}
                            value={this.state.weekDay !== '' ? this.state.weekDay : ''}
                            label={translate('pages/visits/visit/week-of-the-day')}
                            onChange={this.onWeekDayChange}
                        >
                            {weekDayNames}
                        </Select>
                    </Box>

                    <Stack
                        direction="column"
                        divider={<Divider orientation="horizontal" />}
                        spacing={2}
                    >
                        {this.state.timePatterns.map((v, i, array) => {
                            let props = {};
                            if (this.props.weekDay !== undefined && this.props.timePatterns !== undefined) {
                                props.start = this.props.timePatterns[i].start;
                                props.end = this.props.timePatterns[i].end;
                            }

                            if (this.props.weekDay === undefined && this.props.workSchdule !== undefined && this.props.workSchdule[this.state.weekDay] !== undefined) {
                                props.minTime = this.props.workSchdule[this.state.weekDay][0].start;

                                let temp = this.props.workSchdule[this.state.weekDay];
                                props.maxTime = temp[temp.length - 1].end;
                            }

                            return (
                                <TimePattern
                                    key={i}
                                    id={i}
                                    isDisabled={this.state.weekDay === ''}

                                    weekDayName={this.props.weekDay !== undefined ? this.props.weekDay : this.state.weekDay}

                                    onTimePeriodFulfillment={this.onTimePeriodFulfillment}
                                    onTimePeriodNotFulfilled={this.onTimePeriodNotFulfilled}

                                    {...props}
                                />
                            );
                        })}

                        {this.props.isAddable === true ?
                            <Stack
                                justifyContent='center'
                                direction="row"
                                spacing={1}
                            >
                                <Fab size="small" color="primary" disabled={this.state.weekDay === '' ? true : false} onClick={this.insertTimePeriodStack}>
                                    <AddIcon />
                                </Fab>
                                <Fab size="small" color="error" disabled={this.state.weekDay === '' ? true : false} onClick={this.removeTimePeriodStack}>
                                    <CloseIcon />
                                </Fab>
                            </Stack>
                            : null}
                    </Stack>
                </Stack>
            </FormControl>
        )
    }
}

export default WeeklyTimePattern
