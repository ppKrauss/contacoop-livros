<!DOCTYPE html>
<html>
<?php
/**
 * Gera seção isolada na forma de documento (separata).
 * Requer URN ou parâmetros -d (fonte de dados), -c (fonte de conteudo), -t (template)
 */

 require __DIR__.'/lib.php';

  // CONFIGS:
  $cssMode='print';
  $prjPath = dirname(__DIR__);
  $baseUrnPath = "$prjPath/data/raw";
  // $urn = "br;sp:cooperativa;coopcent-abc:estatuto:compilado";
  $urn = "br;sp:cooperativa:livro-contabil:matricula:capa";


// CONTROLLER:
$aurn = splitUrnLex($urn);
$urn_path = urn2path($urn);
$f_path1 = "$baseUrnPath/$urn_path.htm";
$f_path2 = "$mustacheFolder/$urn_path.mustache";

if (file_exists($f_path1))
  $htm_separata = file_get_contents($f_path1);
else { // ver se isset($aurn['subdoc'])
  echo "\nSEM FILE $f_path1, $f_path2\n";
}

?>

<head>
 <meta charset="UTF-8">
 <title>SEPARATA <?=$urn?></title>
 <!-- style ou [link crossorigin="anonymous" href="etc" media="$X" rel="stylesheet"] -->
 <style rel="stylesheet">
    <?php
    if ($cssMode=='all' || $cssMode=='screen')
      // paged com http://www.princexml.com/samples/webarch/forprint.css
      // ver PDF e HTML em http://www.princexml.com/samples/#webarch
      print "\n@media screen and (min-width: 480px) {\n"
        . file_get_contents("$prjPath/src/assets/livroMatricula-v02-screen1.css")
        . "\n}"
      ;
    else  // MEDIA PRINT (for Prince)
      print "@media {\n"
        . file_get_contents("$prjPath/src/assets/livroMatricula-v02-print1-estatuto.css")
        . "\n}"
      ;
    ?>
  </style>
</head>

<body lang="pt">

   <p>waste page</p>
   <p>pdftk original.pdf cat 2-end output semPrimeira.pdf</p>
   <hr>
   <div style="page-break-after: always"></div>


<!-- SEPARATA -->
<?php
  echo $htm_separata;
?>



</body>

</html>
