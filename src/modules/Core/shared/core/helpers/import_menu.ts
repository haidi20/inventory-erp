export function bindImportMenuDismiss(close: () => void) {
    const handler = () => close();
    document.addEventListener('click', handler);

    return () => {
        document.removeEventListener('click', handler);
    };
}
