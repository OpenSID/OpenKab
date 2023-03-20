window._ = import ('lodash');
try {
    window.Popper = import ('popper.js').default;

    import ('bootstrap');
} catch (e) {
}