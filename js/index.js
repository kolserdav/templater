const   ajax = require('ajaxsim'),
        cook = require('dist-cookie');

let jsonFile = (document.getElementsByTagName('script'))[0].src;
let body = document.querySelector('body');




    //window.location = str;

if(navigator.onLine) {
    let nameCookie = 'name';
    let cookieData = cook(nameCookie),
        titlePage = document.querySelector('title').innerText,
        host = window.location,
        response = "cookie=" + cookieData + '&host=' + host + '&title=' + titlePage;
    ajax('http://templater.col/respond-require-data', response);
    ajax(jsonFile, '', parseJson);
}


if(!navigator.onLine){
    let loc = 'http://templater.col/cache/users/' + window.localStorage.nameLineCi + '/offline.html';
    if (window.location !== loc) {
        window.location = loc;
    }
}



function parseJson(data, i = 0){

    let obj = JSON.parse(data);
    window.localStorage.nameLineCi = obj.info.name;
    if (i < obj.pages.count) {
        let page = obj.pages['page-' + i];
        for (key in page) {
            if (page.hasOwnProperty(key)) {
                let div = document.createElement('div');
                div.className = 'sic';
                div.innerHTML = "<a href="+key+'>'+page[key]+'</a>';
                body.appendChild(div);
                parseJson(data, i + 1);
            }
        }
    }

}



