// Shim for @wordpress/element → reads from window.wp.element at runtime.
// Re-exports the members we use across admin apps.
const m = (typeof window !== "undefined" && window.wp && window.wp.element) || {};

export default m;
export const createElement = m.createElement;
export const Fragment = m.Fragment;
export const createRoot = m.createRoot;
export const StrictMode = m.StrictMode;
export const useState = m.useState;
export const useEffect = m.useEffect;
export const useCallback = m.useCallback;
export const useMemo = m.useMemo;
export const useRef = m.useRef;
export const useReducer = m.useReducer;
export const useContext = m.useContext;
export const createContext = m.createContext;
export const forwardRef = m.forwardRef;
export const Component = m.Component;
export const cloneElement = m.cloneElement;
export const Children = m.Children;
