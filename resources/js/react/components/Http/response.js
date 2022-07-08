import { FormHelperText } from "@mui/material";

function collectMessagesFromResponse(data) {
    if (typeof data === 'string') {
        return [data];
    }

    if (typeof data === 'object') {
        if (data.hasOwnProperty('errors')) {
            let messages = [];
            for (const k in data.errors) {
                if (data.errors.hasOwnProperty(k)) {
                    const val = data.errors[k];

                    messages = messages.concat(val);
                }
            }
            return messages;
        } else {
            if (data.hasOwnProperty('message')) {
                return [data.message];
            } else {
                return false;
            }
        }
    }
}

function makeFormHelperTextComponents(data, isError = true) {
    let components = [];
    let i = 0;
    for (const k in data) {
        if (Object.hasOwnProperty.call(data, k)) {
            const v = data[k];

            components.push(<FormHelperText key={i} error={isError}>{v}</FormHelperText>);
        }
        i++;
    }
    return components;
}

export { collectMessagesFromResponse, makeFormHelperTextComponents }
