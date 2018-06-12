**Simple template engine**  
This component has the ability to cache on the client side, 
with the availability of visited pages in offline.

_Installation_  

~$ `composer require kolserdav/templater`  

_Dependencies_ 
 
`"php" : "^7.0"`  
`"kolserdav/router": "^0.2.0"`  
--dev `"phpunit" : "^7.0"`  
 
 _package.json_
` "dependencies": {
     "ajaxsim": "^1.0.0",
     "dist-cookie": "^0.0.7"
   },
   "devDependencies": {
     "webpack": "^4.5.0"`
     
    
 Component templater use kolserdav/router module, and working project must be used single point access.
 For module kolserdav/router settings can be read on: https://github.com/kolserdav/router. 

_Using in template_

At the moment the following structures are supported

```$xslt
{{ variable }}  //some variable need sent to render(['variable' => 'value'],[])
 
{% field %}  //HTML block field, need sent to render([],['field' => 'path/patch.file.html'])

{% for value in array %}{{ value }}{% endfor %} //for in, need sent to render(['for_array' => [1,2,3])  
``` 
To enable syntax highlighting in your IDE, you can use the .twig extension.

Construction for in supported using with tags. For example:
```
{% for value in array %}<h3>{{ value }}</h3><br>{% endfor %}
```

Bud for correct work name 'value' must be unique for one page. 
And it is written in one line. 
For example
```
{% for value1 in array_one %}{{ value1 }}{% endfor %}

{% for value2 in array_two %}{{ value2 }}{% endfor %}
```

_Using_

For use this module need some dependencies write in your index file
or controller file...  
  
Optional (if you need the cache of pages) 

```php
use Avir\Templater\Module\Config;

$config = new Config();
$config->setConfig([
    'cache' => '/path/cache/catalog/+{pages}' //default : false  {pages} - auto create catalog
    'userCache' => '/path/usrCache/catalog/+{users}' //default : false {users} - auto create catalog
]);
```

Require (to include template)

```php
use Avir\Templater\Module\Render;

$obj = new Render('/path/template/catalog', '/template.file'); 
$obj->render(
    [
        'first_variabe' => 'string', //{{ key }} 
        'second_variable' => 111,
        'for_array1' => [1,2,3,4], //arrays need have 'for_' before
        'for_array2' => [4,3,2,1]
    ],
    [
        'field1' => 'patch.file', //patches repository /template-catalog/views
        'field2' => 'path/patch.file' //patches repository /template-catalog/views/path
        ]);
```

It works.


