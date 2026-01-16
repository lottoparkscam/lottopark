const path = require('path');

module.exports = {
    paths: function (paths) {
        paths.appIndexJs = path.resolve(__dirname, 'resources/crm/index.js');
        paths.appSrc = path.resolve(__dirname, 'resources/crm/src');
        return paths;
    },
    jest: function (config) {
        config.setupFilesAfterEnv = [path.resolve(__dirname, 'resources/crm/src/setupTests.js')];
        return config;
    },
}
