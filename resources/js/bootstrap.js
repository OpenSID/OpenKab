window._ = import ('lodash');
try {
    window.Popper = import ('popper.js').default;
    window.$ = window.jQuery = import ('jquery');

    import ('bootstrap');
} catch (e) {
}