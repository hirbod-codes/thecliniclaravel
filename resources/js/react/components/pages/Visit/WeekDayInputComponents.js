import React, { Component } from 'react'

import { translate } from '../../../traslation/translate'

import { Box, Divider, Fab, FormControl, InputLabel, MenuItem, Select, Stack } from '@mui/material'

import AddIcon from '@mui/icons-material/Add';
import ClearIcon from '@mui/icons-material/Clear';
import { LocaleContext } from '../../localeContext';
import { updateState } from '../../helpers';
import TimePeriodInputComponents from './TimePeriodInputComponents';

export class WeekDayInputComponents extends Component {
    constructor(props) {
        super(props)

        this.insertTimePeriodStack = this.insertTimePeriodStack.bind(this);
        this.removeTimePeriodStack = this.removeTimePeriodStack.bind(this);

        this.onWeekDayChange = this.onWeekDayChange.bind(this);

        this.handleTimePeriodComponentFulfillment = this.handleTimePeriodComponentFulfillment.bind(this);

        this.addTimePeriod = this.addTimePeriod.bind(this);

        this.state = {
            weekDay: '',

            timePeriodComponents: [],
            timePeriodInputComponentsFulfillments: [],
            inputDataStructure: [],
        };
    }

    async insertTimePeriodStack(e = null) {
        let timePeriodComponents = this.state.timePeriodComponents;
        let timePeriodInputComponentsFulfillments = this.state.timePeriodInputComponentsFulfillments;
        let inputDataStructure = this.state.inputDataStructure;

        timePeriodComponents.push(
            <TimePeriodInputComponents
                dayId={this.props.id}
                id={this.state.timePeriodComponents.length}

                key={this.state.timePeriodComponents.length}

                week={this.props.week}
                day={this.state.weekDay}

                onComponentFulfillment={this.handleTimePeriodComponentFulfillment}
                addTimePeriod={this.addTimePeriod}
            />
        );

        timePeriodInputComponentsFulfillments.push(false);
        inputDataStructure.push('');

        await updateState(this, { inputDataStructure: inputDataStructure, timePeriodComponents: timePeriodComponents, timePeriodInputComponentsFulfillments: timePeriodInputComponentsFulfillments });
    }

    async removeTimePeriodStack(e) {
        let timePeriodComponents = this.state.timePeriodComponents;
        let inputDataStructure = this.state.inputDataStructure;
        let timePeriodInputComponentsFulfillments = this.state.timePeriodInputComponentsFulfillments;

        timePeriodComponents.pop();
        inputDataStructure.pop();
        timePeriodInputComponentsFulfillments.pop();

        await updateState(this, { inputDataStructure: inputDataStructure, timePeriodComponents: timePeriodComponents, timePeriodInputComponentsFulfillments: timePeriodInputComponentsFulfillments });
    }

    async addTimePeriod(id, timePeriod) {
        let inputDataStructure = this.state.inputDataStructure;

        inputDataStructure[id] = timePeriod;

        await updateState(this, { inputDataStructure: inputDataStructure });

        if (this.checkFulfillment()) {
            this.props.onComponentFulfillment(this.props.id);
        }
    }

    async onWeekDayChange(e) {
        await updateState(this, {
            weekDay: e.target.value,
            timePeriodComponents: [],
            timePeriodInputComponentsFulfillments: [],
            inputDataStructure: [],
        });

        if (this.checkFulfillment()) {
            this.props.onComponentFulfillment(this.props.id);
        }

        this.props.addWeekDay(this.props.id, { weekDay: e.target.value, periods: this.state.inputDataStructure });
    };

    handleTimePeriodComponentFulfillment(id) {
        let timePeriodInputComponentsFulfillments = this.state.timePeriodInputComponentsFulfillments;

        timePeriodInputComponentsFulfillments[id] = true;

        this.setState({ timePeriodInputComponentsFulfillments: timePeriodInputComponentsFulfillments })
    }

    checkFulfillment() {
        if (this.state.timePeriodComponents.length !== this.state.timePeriodInputComponentsFulfillments.length || this.state.weekDay === '') {
            return false;
        }

        for (let i = 0; i < this.state.timePeriodInputComponentsFulfillments.length; i++) {
            const v = this.state.timePeriodInputComponentsFulfillments[i];

            if (!v) {
                return false;
            }
        }

        return true;
    }

    render() {
        let id = this.props.id;
        return (
            <LocaleContext.Consumer>
                {({ locales, currentLocale, isLocaleLoading, changeLocale }) => {
                    return <FormControl sx={{ backgroundColor: theme => theme.palette.secondary }}>
                        <Stack
                            key={id}
                            id={'week-day-stack-' + id}
                            justifyContent='space-between'
                            direction="row"
                            divider={<Divider orientation="vertical" flexItem />}
                            spacing={2}
                        >
                            <Box>
                                <InputLabel id={"week-of-the-day-" + id}>{translate('pages/visits/visit/week-of-the-day', currentLocale.shortName)}</InputLabel>
                                <Select
                                    color={this.state.weekDay === '' ? 'error' : 'primary'}
                                    labelId={"week-of-the-day-" + id}
                                    id={"select-week-of-the-day-" + id}
                                    value={this.state.weekDay !== '' ? this.state.weekDay : ''}
                                    label={translate('pages/visits/visit/week-of-the-day', currentLocale.shortName)}
                                    onChange={this.onWeekDayChange}
                                >
                                    <MenuItem value={'Monday'}>{translate('general/monday/single/ucFirstLetterFirstWord', currentLocale.shortName)}</MenuItem>
                                    <MenuItem value={'Tuesday'}>{translate('general/tuesday/single/ucFirstLetterFirstWord', currentLocale.shortName)}</MenuItem>
                                    <MenuItem value={'Wednesday'}>{translate('general/wednesday/single/ucFirstLetterFirstWord', currentLocale.shortName)}</MenuItem>
                                    <MenuItem value={'Thursday'}>{translate('general/thursday/single/ucFirstLetterFirstWord', currentLocale.shortName)}</MenuItem>
                                    <MenuItem value={'Friday'}>{translate('general/friday/single/ucFirstLetterFirstWord', currentLocale.shortName)}</MenuItem>
                                    <MenuItem value={'Saturday'}>{translate('general/saturday/single/ucFirstLetterFirstWord', currentLocale.shortName)}</MenuItem>
                                    <MenuItem value={'Sunday'}>{translate('general/sunday/single/ucFirstLetterFirstWord', currentLocale.shortName)}</MenuItem>
                                </Select>
                            </Box>

                            <Stack
                                direction="column"
                                divider={<Divider orientation="horizontal" />}
                                spacing={2}
                            >
                                {this.state.timePeriodComponents}

                                <Stack
                                    justifyContent='center'
                                    direction="row"
                                    spacing={2}
                                >
                                    <Fab size="small" color="primary" disabled={this.state.weekDay === '' ? true : false} onClick={this.insertTimePeriodStack}>
                                        <AddIcon />
                                    </Fab>
                                    <Fab size="small" color="error" disabled={this.state.weekDay === '' ? true : false} onClick={this.removeTimePeriodStack}>
                                        <ClearIcon />
                                    </Fab>
                                </Stack>
                            </Stack>
                        </Stack>
                    </FormControl>
                }
                }
            </LocaleContext.Consumer >
        )
    }
}

export default WeekDayInputComponents
