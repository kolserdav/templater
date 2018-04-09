<?
$array = [1,2,3,'<hr>ffffff<hr>'];
$array2 = ['f','E'];
$array3 = ['a','f','s'];
 ?>
<!doctype html>
<html manifest=".manifest.appcache">
<head>
<title>Имя страницы</title>
</head>

<body>

bbb or ccc

<? echo 'test php'; ?>
 
<!-- #@field2  --><? foreach ($array as $value){
	 echo $value; 
	}
?><? foreach ($array3 as $value){
	 echo $value; 
	}
?><h1>Test2</h1>
<!--  -->
<h1>Test1</h1>

aaa<h1><? foreach ($array2 as $value){
	 echo $value; 
	}
?></h1><script src=http://templater.col/main.js></script>
</body>
</html>
