function formatToNumber(props) {
    if (!Object.hasOwnProperty.call(props, 'value') && !props.value) {
        return '';
    }

    return Number(props.value);
}

function formatToTime(props) {
    if (!Object.hasOwnProperty.call(props, 'value') && !props.value) {
        return '';
    }

    return Math.floor(props.value / 3600) + ':' + Math.floor((props.value % 3600) / 60) + ':' + Math.floor((props.value % 3600) % 60);
}

export { formatToNumber, formatToTime };
