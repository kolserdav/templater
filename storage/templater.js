var ui =
    /******/ (function(modules) { // webpackBootstrap
    /******/ 	// The module cache
    /******/ 	var installedModules = {};
    /******/
    /******/ 	// The require function
    /******/ 	function __webpack_require__(moduleId) {
        /******/
        /******/ 		// Check if module is in cache
        /******/ 		if(installedModules[moduleId]) {
            /******/ 			return installedModules[moduleId].exports;
            /******/ 		}
        /******/ 		// Create a new module (and put it into the cache)
        /******/ 		var module = installedModules[moduleId] = {
            /******/ 			i: moduleId,
            /******/ 			l: false,
            /******/ 			exports: {}
            /******/ 		};
        /******/
        /******/ 		// Execute the module function
        /******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
        /******/
        /******/ 		// Flag the module as loaded
        /******/ 		module.l = true;
        /******/
        /******/ 		// Return the exports of the module
        /******/ 		return module.exports;
        /******/ 	}
    /******/
    /******/
    /******/ 	// expose the modules object (__webpack_modules__)
    /******/ 	__webpack_require__.m = modules;
    /******/
    /******/ 	// expose the module cache
    /******/ 	__webpack_require__.c = installedModules;
    /******/
    /******/ 	// define getter function for harmony exports
    /******/ 	__webpack_require__.d = function(exports, name, getter) {
        /******/ 		if(!__webpack_require__.o(exports, name)) {
            /******/ 			Object.defineProperty(exports, name, {
                /******/ 				configurable: false,
                /******/ 				enumerable: true,
                /******/ 				get: getter
                /******/ 			});
            /******/ 		}
        /******/ 	};
    /******/
    /******/ 	// define __esModule on exports
    /******/ 	__webpack_require__.r = function(exports) {
        /******/ 		Object.defineProperty(exports, '__esModule', { value: true });
        /******/ 	};
    /******/
    /******/ 	// getDefaultExport function for compatibility with non-harmony modules
    /******/ 	__webpack_require__.n = function(module) {
        /******/ 		var getter = module && module.__esModule ?
            /******/ 			function getDefault() { return module['default']; } :
            /******/ 			function getModuleExports() { return module; };
        /******/ 		__webpack_require__.d(getter, 'a', getter);
        /******/ 		return getter;
        /******/ 	};
    /******/
    /******/ 	// Object.prototype.hasOwnProperty.call
    /******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
    /******/
    /******/ 	// __webpack_public_path__
    /******/ 	__webpack_require__.p = "";
    /******/
    /******/
    /******/ 	// Load entry module and return exports
    /******/ 	return __webpack_require__(__webpack_require__.s = 0);
    /******/ })
/************************************************************************/
/******/ ({

    /***/ "./js/index.js":
    /*!*********************!*\
      !*** ./js/index.js ***!
      \*********************/
    /*! no static exports found */
    /***/ (function(module, exports, __webpack_require__) {

        eval("const   ajax = __webpack_require__(/*! ajaxsim */ \"./node_modules/ajaxsim/index.js\"),\r\n        cook = __webpack_require__(/*! dist-cookie */ \"./node_modules/dist-cookie/index.js\");\r\n\r\nlet jsonFile = (document.getElementsByTagName('script'))[0].src;\r\nlet body = document.querySelector('body');\r\n\r\n\r\n\r\n\r\n    //window.location = str;\r\n\r\nif(navigator.onLine) {\r\n    let nameCookie = 'nameCookie1';\r\n    let cookieData = cook(nameCookie),\r\n        titlePage = document.querySelector('title').innerText,\r\n        host = window.location,\r\n        response = \"cookie=\" + cookieData + '&host=' + host + '&title=' + titlePage;\r\n    ajax('http://templater.col/respond-require-data', response);\r\n    ajax(jsonFile, '', parseJson);\r\n}\r\nelse {\r\n    let data = window.localStorage.nameLine;\r\n    let div = document.createElement('div');\r\n    div.className = 'title';\r\n    div.innerHTML = \"<h3>Offline pages</h3>\";\r\n    body.appendChild(div);\r\n    write(data);\r\n}\r\n\r\n\r\nfunction write(data, i = 0) {\r\n    let obj = JSON.parse(data);\r\n    if (i < obj.pages.count) {\r\n        let page = obj.pages['page-' + i];\r\n        for (key in page) {\r\n            if (page.hasOwnProperty(key)) {\r\n                let div = document.createElement('div');\r\n                div.className = 'sic';\r\n                div.innerHTML = \"<a href=\"+key+'>'+page[key]+'</a>';\r\n                body.appendChild(div);\r\n                write(data, i + 1);\r\n            }\r\n        }\r\n    }\r\n}\r\n\r\nfunction parseJson(data, i = 0){\r\n\r\n    window.localStorage.nameLine = data;\r\n\r\n}\r\n\r\n\r\n\r\n\n\n//# sourceURL=webpack://ui/./js/index.js?");

        /***/ }),

    /***/ "./node_modules/ajaxsim/index.js":
    /*!***************************************!*\
      !*** ./node_modules/ajaxsim/index.js ***!
      \***************************************/
    /*! no static exports found */
    /***/ (function(module, exports, __webpack_require__) {

        "use strict";
        eval("\r\n\r\nmodule.exports = ajax;\r\n\r\nfunction ajax(url, request = null, responseBack = null, method = 'POST')\r\n{\r\n    let xhr = new XMLHttpRequest();\r\n    if (responseBack === null || responseBack === ''){\r\n        responseBack = function (response){\r\n            console.log(response);\r\n        };\r\n    }\r\n\r\n    if (request === null || request === ''){\r\n        request = \"test=ajaxS works\";\r\n    }\r\n\r\n    if (method === 'GET' || method === 'get'){\r\n        let params = request;\r\n        request = null;\r\n        url = url+'?'+params;\r\n    }\r\n\r\n    function reqHeader(xhr)\r\n    {\r\n        if (method === 'GET' || method === 'get')\r\n        {\r\n            return null;\r\n        }\r\n        else {\r\n            return xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');\r\n        }\r\n    }\r\n\r\n    sendRequest(url, request, responseBack, method);\r\n\r\n    function sendRequest(url, request)\r\n    {\r\n        xhr.onreadystatechange = setGo;\r\n        xhr.open(method, url, true);\r\n        reqHeader(xhr);\r\n        xhr.send(request);\r\n    }\r\n    function setGo()\r\n    {\r\n        if (xhr.readyState === 4 && xhr.status === 200) {\r\n            let response = xhr.responseText;\r\n            responseBack(response);\r\n        }\r\n        else {\r\n            return false;\r\n        }\r\n    }\r\n\r\n}\r\n\r\n\r\n\n\n//# sourceURL=webpack://ui/./node_modules/ajaxsim/index.js?");

        /***/ }),

    /***/ "./node_modules/dist-cookie/index.js":
    /*!*******************************************!*\
      !*** ./node_modules/dist-cookie/index.js ***!
      \*******************************************/
    /*! no static exports found */
    /***/ (function(module, exports, __webpack_require__) {

        eval("/**\r\n * Connecting default name generator\r\n * @type {fu}\r\n */\r\nlet fullName = __webpack_require__(/*! pseudo-name */ \"./node_modules/pseudo-name/index.js\");\r\n\r\n/**\r\n * Export module\r\n * @type {setCookie}\r\n */\r\nmodule.exports = setCookie;\r\n\r\n/**\r\n *Cookie processing\r\n * @param name\r\n * @param cookie\r\n * @param time\r\n * @returns {{name: {nameCookie: {clear: string, encode: string}, time: Date, newCookie: bool}}}\r\n */\r\nfunction setCookie(name = 'name', cookie = 'cookie', time = 1){\r\n\r\n    time = time*(1000*60*60*24);\r\n    name = (name === '')? 'name' : name;\r\n\r\n    let nameCookie = name + \"=\",\r\n        date = new Date(new Date().getTime() + time),\r\n        newCookie;\r\n\r\n    if (!document.cookie) {\r\n        newCookie = true;\r\n        cookie = (cookie === 'cookie' || cookie === '')? btoa(fullName()) : cookie;\r\n        document.cookie = nameCookie + cookie + \"; \" + \"expires=\" + date.toUTCString();\r\n    }\r\n    else {\r\n        newCookie = false;\r\n        cookie = (document.cookie.match(/\\w*=\\w*/))[0].replace((name+'='), '');\r\n        document.cookie = nameCookie + cookie + \"; \" + \"expires=\" + date.toUTCString();\r\n    }\r\n    let cookieClear = atob(cookie);\r\n    return JSON.stringify({\r\n        name : {\r\n            cookie : name,\r\n            \"nameCookie\" : {\r\n                \"clear\": cookieClear,\r\n                \"encode\": cookie\r\n            },\r\n            \"time\": date,\r\n            \"newCookie\" : newCookie\r\n        }\r\n    });\r\n\r\n}\r\n\n\n//# sourceURL=webpack://ui/./node_modules/dist-cookie/index.js?");

        /***/ }),

    /***/ "./node_modules/pseudo-name/index.js":
    /*!*******************************************!*\
      !*** ./node_modules/pseudo-name/index.js ***!
      \*******************************************/
    /*! no static exports found */
    /***/ (function(module, exports, __webpack_require__) {

        "use strict";
        eval("\r\n\r\nmodule.exports = fu;\r\n\r\n/**\r\n *\r\n * @returns {string}\r\n */\r\nfunction fu()\r\n{\r\n    /**\r\n     *\r\n     * @constructor\r\n     */\r\n    function H(){}\r\n    H.prototype.name = null;\r\n    H.prototype.firstName = null;\r\n    H.prototype.lastName = null;\r\n    H.prototype.fullName = null;\r\n    let h = new H();\r\n\r\n    /**\r\n     *\r\n     * @type {string}\r\n     */\r\n    let uppercases = 'QWERTYUIOPASDFGHJKLMNBVCXZ',\r\n        vovels = /[EYUIOAeyuioa]/,\r\n        symVovels = 'eyuioa',\r\n        symConsonans = 'qwrtpsdfghjklzxcvbnm';\r\n\r\n    /**\r\n     *\r\n     * @type {string}\r\n     */\r\n    let lengthName = (Math.random()*12).toFixed(0);\r\n    lengthName = (lengthName < 3)? 6 : lengthName;\r\n\r\n    /**\r\n     * Name generator\r\n     */\r\n    getName();\r\n\r\n    return h.fullName;\r\n\r\n    /**\r\n     *\r\n     * @param i\r\n     * @returns {null}\r\n     */\r\n    function getName(i = 0) {\r\n\r\n        if (i < lengthName){\r\n            if (h.name === null){\r\n                h.name = uppercases[(Math.random()*(uppercases.length - 1)).toFixed(0)];\r\n                return getName(i +1);\r\n            }\r\n            else {\r\n                if (h.name[i -1].match(vovels)){\r\n                    h.name += symConsonans[(Math.random()*(symConsonans.length - 1)).toFixed(0)];\r\n                    return getName(i + 1);\r\n                }\r\n                else{\r\n                    h.name += symVovels[(Math.random()*(symVovels.length - 1)).toFixed(0)];\r\n                    return getName(i + 1);\r\n                }\r\n            }\r\n        }\r\n        else {\r\n            if (h.firstName === null){\r\n                h.firstName = h.name;\r\n                h.name = null;\r\n                lengthName = (Math.random()*12).toFixed(0);\r\n                lengthName = (lengthName < 3)? 6 : lengthName;\r\n                return getName();\r\n            }\r\n            if (h.lastName === null && h.firstName !== null) {\r\n                h.lastName = h.name;\r\n                h.fullName = h.firstName +'_' +h.lastName;\r\n            }\r\n        }\r\n        return h.fullName;\r\n    }\r\n}\n\n//# sourceURL=webpack://ui/./node_modules/pseudo-name/index.js?");

        /***/ }),

    /***/ 0:
    /*!***************************!*\
      !*** multi ./js/index.js ***!
      \***************************/
    /*! no static exports found */
    /***/ (function(module, exports, __webpack_require__) {

        eval("module.exports = __webpack_require__(/*! ./js/index.js */\"./js/index.js\");\n\n\n//# sourceURL=webpack://ui/multi_./js/index.js?");

        /***/ })

    /******/ });