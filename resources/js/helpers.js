export const isImage = (attachment) => {
    const mime = attachment?.mime ?? attachment?.type ?? '';

    return mime.startsWith('image/');
};