import React, { Component } from 'react'

import PropTypes from 'prop-types';

import CloseIcon from '@mui/icons-material/Close';
import AddIcon from '@mui/icons-material/Add';
import { Box, Button, Divider, Fab, Stack } from '@mui/material';

import { translate } from '../../../traslation/translate';
import { convertWeeklyTimePatterns, resolveTimeZone, updateState } from '../../helpers';
import { LocaleContext } from '../../localeContext';
import WeeklyTimePattern from './WeeklyTimePattern';
import { connect } from 'react-redux';

/**
 * WeeklyTimePatterns
 * @augments {Component<Props, State>}
 */
export class WeeklyTimePatterns extends Component {
    static propTypes = {
        handleVisitInfo: PropTypes.func,
        weeklyTimePatterns: PropTypes.arrayOf(PropTypes.array),
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
            weeklyTimePatterns: [],
            isComplete: false,
        };
    }

    componentDidMount() {
        if (this.props.workSchdule !== undefined) {
            this.setState({ workSchdule: this.props.workSchdule });
        }

        if (this.props.weeklyTimePatterns !== undefined) {
            this.setState({
                weeklyTimePatterns: this.props.weeklyTimePatterns,
            });
        } else {
            this.insertWeekDayStack();
        }
    }

    async insertWeekDayStack(e) {
        let weeklyTimePatterns = this.state.weeklyTimePatterns;
        if (this.state.weeklyTimePatterns.length > 7 || weeklyTimePatterns[weeklyTimePatterns.length - 1] === null) {
            return;
        }

        weeklyTimePatterns.push(null);

        this.setState({ isComplete: this.checkCompletion() === true ? true : false });
    }

    async removeWeekDayStack(e) {
        let weeklyTimePatterns = this.state.weeklyTimePatterns;
        weeklyTimePatterns.pop();

        await updateState(this, { weeklyTimePatterns: weeklyTimePatterns });
        this.setState({ isComplete: this.checkCompletion() === true ? true : false });
    }

    async onWeekDayFulfillment(weekDay, patterns, id) {
        let weeklyTimePatterns = this.state.weeklyTimePatterns;
        weeklyTimePatterns[id] = [weekDay, patterns];

        await updateState(this, { weeklyTimePatterns: weeklyTimePatterns });
        this.setState({ isComplete: this.checkCompletion() === true ? true : false });
    }

    async onWeekDayNotFulfilled(id) {
        let weeklyTimePatterns = this.state.weeklyTimePatterns;
        weeklyTimePatterns[id] = null;

        await updateState(this, { isComplete: false, weeklyTimePatterns: weeklyTimePatterns });
        this.setState({ isComplete: this.checkCompletion() === true ? true : false });
    }

    checkCompletion() {
        if (this.state.weeklyTimePatterns.length === 0) {
            return false;
        }

        for (let i = 0; i < this.state.weeklyTimePatterns.length; i++) {
            const v = this.state.weeklyTimePatterns[i];

            if (v === null) {
                return false;
            }

            if (v[0] === undefined || v.length !== 2) {
                return false;
            }
        }

        return true;
    }

    handleVisitInfo() {
        if (!this.state.isComplete) {
            return;
        }

        if (this.props.handleVisitInfo !== undefined) {
            let weeklyTimePatterns = this.state.weeklyTimePatterns;
            const locale = LocaleContext._currentValue.currentLocale.shortName;

            weeklyTimePatterns = convertWeeklyTimePatterns(weeklyTimePatterns, resolveTimeZone(locale), 'UTC');

            this.props.handleVisitInfo(weeklyTimePatterns);
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
                    {this.state.weeklyTimePatterns.map((v, i) => {
                        let props = {};
                        if (this.props.handleVisitInfo !== undefined) {
                            props.isAddable = true;
                        }

                        if (v !== null) {
                            props.weekDay = v[0];
                            props.timePatterns = v[1];
                        }

                        if (this.state.workSchdule !== null) {
                            props.workSchdule = this.state.workSchdule;
                        }

                        return (
                            <WeeklyTimePattern
                                key={i}
                                id={i}

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

export default connect(mapStateToProps)(WeeklyTimePatterns)
