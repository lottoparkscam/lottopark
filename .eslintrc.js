module.exports = {
    root: true,
    "env": {
        "browser": true,
        "es2021": true,
        "jquery": true,
        "node": true
    },
    "globals": {
        "lightbox": true,
        "gtag_checkout_events": true,
        "facebook_checkout_events": true,
        "WebFont": true,
        "menuLotteriesSlugs": true,
    },
    "extends": [
        "eslint:recommended",
        "prettier"
    ],
    "parserOptions": {
        "ecmaVersion": 13,
        "sourceType": "module"
    },
    "rules": {
        "no-console": 0
    }
};
