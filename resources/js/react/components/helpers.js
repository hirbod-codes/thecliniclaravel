function iterateRecursively(data, beforeHandling, handling, handled) {
    if (beforeHandling() === false) {
        return;
    }

    let i = 0;
    for (const k in data) {
        if (Object.hasOwnProperty.call(data, k)) {
            const v = data[k];

            if (handling(data, v, k, i) === false) {
                return;
            }
        }
        i++;
    }

    handled();
}

function updateState(obj, state) {
    return new Promise(() => obj.setState(state));
}

export { iterateRecursively, updateState };

