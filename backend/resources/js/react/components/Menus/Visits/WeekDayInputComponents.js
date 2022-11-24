import React, { Component } from 'react'

import PropTypes from 'prop-types';

import CloseIcon from '@mui/icons-material/Close';
import AddIcon from '@mui/icons-material/Add';
import { Box, Button, Divider, Fab, Stack } from '@mui/material';

import { translate } from '../../../traslation/translate';
import WeekDayInputComponent from './WeekDayInputComponent';
import { convertWeekDays, resolveTimeZone, updateState } from '../../helpers';
import store from '../../../../redux/store';
import { connect } from 'react-redux';

/**
 * WeekDayInputComponents
 * @augments {Component<Props, State>}
 */
export class WeekDayInputComponents extends Component {
    static propTypes = {
        handleVisitInfo: PropTypes.func,
        weekDayInputComponents: PropTypes.arrayOf(PropTypes.number),
        weekDays: PropTypes.arrayOf(PropTypes.object),
        workSchdule: PropTypes.objectOf(PropTypes.array),
    }

    constructor(props) {
        super(props)

        this.insertWeekDayStack = this.insertWeekDayStack.bind(this);
        this.removeWeekDayStack = this.removeWeekDayStack.bind(this);
        this.onWeekDayFulfillment = this.onWeekDayFulfillment.bind(this);
        this.onWeekDayNotFulfilled = this.onWeekDayNotFulfilled.bind(this);
        this.checkCompletion = this.checkCompletion.bind(this);
        this.handleVisitInfo = this.handleVisitInfo.bind(this);


        this.state = {
            weekDayNames: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            workSchdule: null,
            weekDayInputComponents: [],
            weekDays: [],
            isComplete: false,
        };
    }

    componentDidMount() {
        if (this.props.workSchdule !== undefined) {
            this.setState({ workSchdule: this.props.workSchdule });
        }

        if (this.props.weekDayInputComponents !== undefined && this.props.weekDays !== undefined) {
            this.setState({
                weekDayInputComponents: this.props.weekDayInputComponents,
                weekDays: this.props.weekDays,
            });
        } else {
            this.insertWeekDayStack();
        }
    }

    async insertWeekDayStack(e) {
        if (this.state.weekDayInputComponents.length > 7) {
            return;
        }

        let weekDays = this.state.weekDays;
        weekDays.push(null);

        let weekDayInputComponents = this.state.weekDayInputComponents;
        weekDayInputComponents.push(this.state.weekDayInputComponents.length);

        await updateState(this, { weekDayInputComponents: weekDayInputComponents });
        this.setState({ isComplete: this.checkCompletion() === true ? true : false });
    }

    async removeWeekDayStack(e) {
        let weekDays = this.state.weekDays;
        weekDays.pop();

        let weekDayInputComponents = this.state.weekDayInputComponents;
        weekDayInputComponents.pop();

        await updateState(this, { weekDayInputComponents: weekDayInputComponents, weekDays: weekDays });
        this.setState({ isComplete: this.checkCompletion() === true ? true : false });
    }

    async onWeekDayFulfillment(weekDay, key) {
        let weekDays = this.state.weekDays;
        weekDays[key] = weekDay;

        await updateState(this, { weekDays: weekDays });
        this.setState({ isComplete: this.checkCompletion() === true ? true : false });
    }

    async onWeekDayNotFulfilled(key) {
        let weekDays = this.state.weekDays;
        weekDays[key] = null;

        await updateState(this, { isComplete: false, weekDays: weekDays });
        this.setState({ isComplete: this.checkCompletion() === true ? true : false });
    }

    checkCompletion() {
        if (this.state.weekDays.length === 0) {
            return false;
        }

        for (let i = 0; i < this.state.weekDays.length; i++) {
            const weekDay = this.state.weekDays[i];

            if (weekDay === null) {
                return false;
            }
        }

        return this.state.weekDays.length === this.state.weekDayInputComponents.length;
    }

    handleVisitInfo() {
        if (!this.state.isComplete) {
            return;
        }

        if (this.props.handleVisitInfo !== undefined) {
            let weekDays = this.state.weekDays;
            let newWeekDays = {};
            weekDays.forEach((v, i) => {
                newWeekDays[v.weekDay] = v.timePeriods;
            });
            const locale = store.getState().local.local.shortName;

            weekDays = convertWeekDays(newWeekDays, resolveTimeZone(locale), 'UTC');
            this.props.handleVisitInfo(weekDays);
        }
    }

    render() {
        return (
            <Box sx={{ overflowY: 'auto', height: '100%' }}>
                <Stack
                    height='100%'
                    justifyContent='space-between'
                    direction="column"
                    divider={<Divider orientation="horizontal" />}
                    spacing={2}
                >
                    {this.state.weekDayInputComponents.map((v, i) => {
                        let props = {};
                        if (this.props.handleVisitInfo !== undefined) {
                            props.isAddable = true;
                        }

                        if (this.props.weekDayInputComponents !== undefined && this.props.weekDays !== undefined) {
                            props.weekDay = this.props.weekDays[i].weekDay;
                            props.timePeriods = this.props.weekDays[i].timePeriods;

                            props.timePeriodComponents = [];
                            for (let j = 0; j < this.props.weekDays[i].timePeriods.length; j++) {
                                props.timePeriodComponents[j] = j;
                            }
                        }

                        if (this.state.workSchdule !== null) {
                            props.workSchdule = this.state.workSchdule;
                        } else {
                            props.weekDayNames = this.state.weekDayNames;
                        }

                        return (
                            <WeekDayInputComponent
                                key={v}
                                id={v}

                                weekDayNames={this.state.weekDayNames}

                                onWeekDayFulfillment={this.onWeekDayFulfillment}
                                onWeekDayNotFulfilled={this.onWeekDayNotFulfilled}

                                {...props}
                            />
                        );
                    })}

                    {this.props.handleVisitInfo === undefined ? null :
                        <>
                            <Stack
                                justifyContent='center'
                                direction="row"
                                spacing={2}
                            >
                                <Fab size="medium" color="primary" onClick={this.insertWeekDayStack}>
                                    <AddIcon />
                                </Fab>
                                <Fab size="medium" color="error" onClick={this.removeWeekDayStack}>
                                    <CloseIcon />
                                </Fab>
                            </Stack>

                            <Button type='button' disabled={!this.state.isComplete} onClick={this.handleVisitInfo} fullWidth >
                                {translate('general/done/single/ucFirstLetterFirstWord')}
                            </Button>
                        </>
                    }
                </Stack>
            </Box>
        )
    }
}

const mapStateToProps = state => ({
    redux: state
});

export default connect(mapStateToProps)(WeekDayInputComponents)
