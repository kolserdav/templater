**Simple template engine**  

_Installation_  

~$ `composer require kolserdav/templater`  

_Dependencies_ 
 
`"php" : "^7.0"`  
--dev `"phpunit" : "^7.0"`  
 

_Using in template_

At the moment the following structures are supported

```$xslt
{{ variable }}  //some variable need sent to render(['variable' => 'value'],[])
 
@field  //HTML block field, need sent to render([],['@field' => 'path/patch.file.html'])

{% for value in array %} //for in, need sent to render(['for_array' => [1,2,3], ['@field' => 'path/patch.file.html']])  
{{ value }}
{% endfor  %}
``` 


**_`If you use control characters as '@' in the comments to your template files, then escape them with '#'`_**


_Using_

For use this module need some dependencies write in your index file
or controller file...  
  
Optional (if you need the cache of pages) 

```php
use Avir\Templater\Config;

$config = new Config();
$config->setConfig([
    'cache' => '/path/cache/catalog' //default : false
]);
```
Require (to include template)

```php
use Avir\Templater\Render;

$obj = new Render('/path/template/catalog', '/template.file.php'); 
$obj->render(
    [
        'first_variabe' => 'string', //{{ keys }} in patch files
        'second_variable' => 111,
        'for_array1' => [1,2,3,4], //arrays need have 'for_' after
        'for_array2' => [4,3,2,1]
    ],
    [
        '@example_field1' => 'patch.file.html', //patches repository /template-catalog/views
        '@example_field2' => 'path/patch.file.html' //patches repository /template-catalogviews/path
        ]);
```


