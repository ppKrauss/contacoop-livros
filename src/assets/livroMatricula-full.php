 <!DOCTYPE html>
 <html>
 <head>
 <meta charset="UTF-8">
 <title>NATRICULA COOP</title>
 <!--
CSS ver http://jsfiddle.net/3oaLd5fn/
ou usar   background-image:linear-gradient(to right, #fff 5px, transparent 1px),  linear-gradient(#ccc 1px, transparent 1px);
  background-size: 20px 30px;

parece ser melhor que https://www.w3.org/Style/Examples/007/leaders.en.html

 -->
  <?php
  echo "<style></style>";
  ?>

 </head>

 <body>
   <?php
   // use "composer update" in the same folder of json.
   require  __DIR__.'/vendor/mustache/mustache/src/Mustache/Autoloader.php';
   Mustache_Autoloader::register();
   $m = new Mustache_Engine;

/*   $m = new Mustache_Engine(array(
       'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/views'),
   ));

   // loads template from `views/hello_world.mustache` and renders it.
   echo $m->render('hello_world', array('planet' => 'world'));
*/

   echo "<style></style>";
   ?>


 </body>
</html>
