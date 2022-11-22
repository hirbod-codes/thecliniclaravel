import store from "../../../redux/store";
import { translate } from "../../traslation/translate";
import { localizeDate } from "../helpers";

function formatToNumber(params) {
    if (params.value === undefined) {
        return '';
    }

    return Number(params.value);
}

function formatToTime(params) {
    if (params.value === undefined) {
        return '';
    }

    const locale = store.getState().local.local.shortName;
    let date = null;
    switch (params.field) {
        case 'consuming_time':
            return Math.floor(params.value / 3600) + ':' + Math.floor((params.value % 3600) / 60) + ':' + Math.floor((params.value % 3600) % 60);

        case 'needed_time':
            return Math.floor(params.value / 3600) + ':' + Math.floor((params.value % 3600) / 60) + ':' + Math.floor((params.value % 3600) % 60);

        default:
            date = localizeDate('utc', params.value, locale, true);
            break;
    }


    return date;
}

function foramtColumnsFromBackEnd(columns) {
    let formattedColumns = [];
    let found = false;
    for (let i = 0; i < columns.length; i++) {
        found = false;
        for (let j = 0; j < formattedColumns.length; j++) {
            if (formattedColumns[j].field === columns[i].name) {
                found = true;
                break;
            }
        }

        if (found) {
            continue;
        }

        let type = formatColumnTypeFromBackEnd(columns[i].type);
        let headerName = '';
        try {
            headerName = translate('general/columns/' + columns[i].name + '/single/ucFirstLetterFirstWord');
        } catch (error) {
            try {
                headerName = translate('general/columns/account/' + columns[i].name + '/single/ucFirstLetterFirstWord');
            } catch (error1) {
                continue;
            }
        }

        let column = {
            field: columns[i].name,
            headerName: headerName,
            description: headerName,
            type: type,
            valueFormatter: type === 'number' ? formatToNumber : (type === 'dateTime' ? formatToTime : (params) => params.value),
            minWidth: type === 'dateTime' ? 190 : 150,
        };

        formattedColumns.push(column);
    }

    return formattedColumns;
}

function formatColumnTypeFromBackEnd(type) {
    switch (type) {
        case 'integer':
            return 'number';

        case 'bigint':
            return 'number';

        case 'datetime':
            return 'dateTime';

        default:
            return type;
    }
}

export { formatColumnTypeFromBackEnd, foramtColumnsFromBackEnd, formatToNumber, formatToTime };
