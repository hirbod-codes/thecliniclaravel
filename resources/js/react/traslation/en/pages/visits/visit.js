import { addWordTo } from "../../../translate";

let visit = {};

visit = addWordTo(visit, 'visit', 'visits');
visit['choose-one-order'] = 'Please choose one order for adding the new visit.';
visit['laser-visit'] = 'Laser Visits';
visit['regular-visit'] = 'Regular Visits';
visit['your-visit'] = 'Your Visits';
visit['others-visit'] = 'Others Visits';
visit.title = 'Visit';
visit.closest = 'Closest';
visit['weekly-search'] = 'Weekly Search';
visit['visit-accuracy-warning'] = 'Be aware that the shown visit times might be accupied by now so make sure to refresh available visit time.';
visit['closest-visit-available'] = 'Closest available visit: ';
visit['weekly-visit-available'] = 'Weekly available visit: ';
visit['week-of-the-day'] = 'Week of the day';
visit['current-timezone'] = 'Current timezone is:';

visit.columns = {
    weekDaysPeriods: 'Weekly time periods',
    dateTimePeriod: 'Time periods',
    visitTimestamp: 'Visit due',
    consumingTime: 'Visit duration',
};

export { visit };
