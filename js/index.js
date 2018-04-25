const   ajax = require('ajaxsim'),
        cook = require('dist-cookie');

let jsonFile = (document.querySelector('#jsonFile'));
if (jsonFile){
    jsonFile = jsonFile.getAttribute('src');
}
let body = document.querySelector('body');


if(navigator.onLine) {
    let nameCookie = 'name';
    let cookieData = cook(nameCookie),
        pregWww = /www./,
        hostName = window.location.hostname,
        titlePage = document.querySelector('title').innerText,
        host = window.location,
        response = "cookie=" + cookieData + '&host=' + host + '&title=' + titlePage;
    if (host.toString().match(pregWww)){
        hostName = 'www.'+hostName;
    }
    ajax(hostName+'/respond-require-data', response, console);
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

function console(w) {
    return (w);
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
        if (i === obj.pages.count - 1) {
            let div = document.createElement('div');
            div.className = 'sic2';
            div.innerHTML = "<a href=" + window.localStorage.nameLineLast + '>' + window.localStorage.nameLineLastTitle + '</a>';
            body.appendChild(div);
        }
    }
}

function parseJson(data, i = 0){

    window.localStorage.nameLineLastTitle = document.querySelector('title').innerHTML;
    window.localStorage.nameLineLast = window.location;
    window.localStorage.nameLine = data;

}



