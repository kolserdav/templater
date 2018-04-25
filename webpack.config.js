const path = require('path');

module.exports = {
    mode: 'production',
    entry: ["./js/index.js"],

    output: {

        path: path.resolve(__dirname, "storage"),

        filename: "templater.js",

        libraryTarget: 'var',
        library: 'ui'
    },
    watch : true
};
