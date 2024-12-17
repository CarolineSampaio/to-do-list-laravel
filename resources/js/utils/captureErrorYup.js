export const captureErrorYup = (error) => {
    return error.inner.reduce((acc, currentValue) => {
        if (!acc[currentValue.path]) {
            acc[currentValue.path] = currentValue.message;
        }
        return acc;
    }, {});
};
