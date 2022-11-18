import React, { Component } from 'react'

import PropTypes from 'prop-types';

import CloseIcon from '@mui/icons-material/Close';
import AddIcon from '@mui/icons-material/Add';
import { Box, Divider, Fab, FormControl, InputLabel, MenuItem, Select, Stack } from '@mui/material'

import { translate, ucFirstLetterFirstWord } from '../../../traslation/translate'
import { updateState } from '../../helpers';
import TimePeriodComponent from './TimePeriodComponent';

/**
 * WeekDayInputComponent
 * @augments {Component<Props, State>}
 */
export class WeekDayInputComponent extends Component {
    static propTypes = {
        id: PropTypes.number.isRequired,
        onWeekDayFulfillment: PropTypes.func.isRequired,
        onWeekDayNotFulfilled: PropTypes.func.isRequired,
        workSchdule: PropTypes.objectOf(PropTypes.array),
        weekDayNames: PropTypes.arrayOf(PropTypes.string),

        timePeriodComponents: PropTypes.array,
        weekDay: PropTypes.string,
        timePeriods: PropTypes.arrayOf(PropTypes.object),

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
            timePeriodComponents: [],
            timePeriods: [],
        };
    }

    componentDidMount() {
        if (this.props.weekDayNames === undefined && this.props.workSchdule === undefined) {
            throw new Error('insufficient props for WeekDayInputComponent component');
        }

        if (this.props.timePeriodComponents !== undefined && this.props.weekDay !== undefined && this.props.timePeriods !== undefined) {
            this.setState({
                timePeriodComponents: this.props.timePeriodComponents,
                weekDay: this.props.weekDay,
                timePeriods: this.props.timePeriods,
            });
        } else {
            this.insertTimePeriodStack();
        }
    }

    async insertTimePeriodStack() {
        let timePeriods = this.state.timePeriods;
        timePeriods.push(null);


        let timePeriodComponents = this.state.timePeriodComponents;
        timePeriodComponents.push(this.state.timePeriodComponents.length);

        await updateState(this, { timePeriodComponents: timePeriodComponents, timePeriods: timePeriods });

        this.checkCompletion();
    }

    async removeTimePeriodStack() {
        let timePeriods = this.state.timePeriods;
        timePeriods.pop();

        let timePeriodComponents = this.state.timePeriodComponents;
        timePeriodComponents.pop();

        await updateState(this, { timePeriodComponents: timePeriodComponents, timePeriods: timePeriods });

        this.checkCompletion();
    }

    async onTimePeriodFulfillment(timePeriod, key) {
        let timePeriods = this.state.timePeriods;
        timePeriods[key] = timePeriod;

        await updateState(this, { timePeriods: timePeriods });

        this.checkCompletion();
    }

    async onTimePeriodNotFulfilled(timePeriod, key) {
        let timePeriods = this.state.timePeriods;
        timePeriods[key] = null;

        await updateState(this, { timePeriods: timePeriods });

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
        for (let i = 0; i < this.state.timePeriods.length; i++) {
            v = this.state.timePeriods[i];

            if (v === null) {
                return false;
            }
        }

        if ((this.state.timePeriods.length === this.state.timePeriodComponents.length) && this.state.weekDay !== '') {
            this.props.onWeekDayFulfillment({ weekDay: this.state.weekDay, timePeriods: this.state.timePeriods }, this.props.id);
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
                    // const v = this.props.workSchdule[k];

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
                        {this.state.timePeriodComponents.map((v, i, array) => {
                            let timePeriod = {};
                            if (this.props.weekDay !== undefined && this.props.timePeriods !== undefined) {
                                timePeriod.start = this.props.timePeriods[i].start;
                                timePeriod.end = this.props.timePeriods[i].end;
                            }

                            if (this.props.weekDay === undefined && this.props.workSchdule !== undefined && this.props.workSchdule[this.state.weekDay] !== undefined) {
                                timePeriod.minTime = this.props.workSchdule[this.state.weekDay][0].start;

                                let temp = this.props.workSchdule[this.state.weekDay];
                                timePeriod.maxTime = temp[temp.length - 1].end;
                            }

                            return (
                                <TimePeriodComponent
                                    key={v}
                                    id={v}
                                    isDisabled={this.state.weekDay === ''}

                                    weekDayName={this.props.weekDay !== undefined ? this.props.weekDay : this.state.weekDay}

                                    onTimePeriodFulfillment={this.onTimePeriodFulfillment}
                                    onTimePeriodNotFulfilled={this.onTimePeriodNotFulfilled}

                                    {...timePeriod}
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

export default WeekDayInputComponent
