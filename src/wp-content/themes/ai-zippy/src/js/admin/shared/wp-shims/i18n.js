// Shim for @wordpress/i18n → reads from window.wp.i18n.
const m = (typeof window !== "undefined" && window.wp && window.wp.i18n) || {};

export default m;
export const __ = m.__ || ((s) => s);
export const _x = m._x || ((s) => s);
export const _n = m._n || ((s, p, n) => (n === 1 ? s : p));
export const _nx = m._nx || ((s, p, n) => (n === 1 ? s : p));
export const sprintf = m.sprintf || ((s) => s);
export const setLocaleData = m.setLocaleData || (() => {});
