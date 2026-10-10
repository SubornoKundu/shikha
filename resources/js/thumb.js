/*
 * Smaller copies of local pictures (made on first request by ThumbController).
 * srcset('/images/x.jpg', [480, 720]) -> '/thumbs/480/images/x.jpg.webp 480w, /thumbs/720/images/x.jpg.webp 720w'
 * Pictures from other sites, or other kinds of file, get no srcset and just use their normal address.
 */
const LOCAL_PICTURE = /^\/images\/[^?#]+\.(jpe?g|png|webp)$/i;

export function srcset(src, widths, original = null) {
    if (typeof src !== 'string' || !LOCAL_PICTURE.test(src)) return undefined;

    // Smaller copies are WebP ("x.jpg.webp"); a picture that already is WebP keeps its own name.
    const ext = /\.webp$/i.test(src) ? '' : '.webp';
    const list = widths.map((w) => `/thumbs/${w}${src}${ext} ${w}w`);
    if (original) list.push(`${src} ${original}w`);

    return list.join(', ');
}
