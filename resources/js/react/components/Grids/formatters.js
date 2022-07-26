function formatToNumber(props) {
    if (props.value === undefined) {
        return '';
    }

    return Number(props.value);
}

function formatToTime(props) {
    if (props.value === undefined) {
        return '';
    }

    return Math.floor(props.value / 3600) + ':' + Math.floor((props.value % 3600) / 60) + ':' + Math.floor((props.value % 3600) % 60);
}

export { formatToNumber, formatToTime };
