const path = require('path');

module.exports = {
    mode: 'development',
    entry: ["./js/index.js"],

    output: {

        path: path.resolve(__dirname, "public"),

        filename: "main.js",

        libraryTarget: 'var',
        library: 'ui'
    },
    watch : true
};
