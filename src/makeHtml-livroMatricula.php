<!DOCTYPE html>
<html>
<?php

require __DIR__.'/lib.php';

  // CONFIGS:
  $cssMode='print';
  $prjPath = dirname(__DIR__);
  $baseUrnPath = "$prjPath/data/raw";
  $input = "$prjPath/data/raw/livroMatricula-dados.json";

$livroContents = [
'org_nameshort'=>'PLASTCOOPER'
,'org_namefull'=>'COOPERATIVA IND. DE TRAB. PLÁSTICOS'
,'org_cnpj'=>'003.852.353/0001-08'
,'org_email'=>'plastcooper@plastcooper.com.br' // (dominio na Locaweb sob responsabilidade de Marcos Silva da http://gvfinfo.com.br/)
,'org_end_rua'=>'Rua Eugênia Sá Vitalle, 883'
,'org_end_bairroCidade'=>'Taboão - São Bernardo do Campo - SP'
,'org_end_cep'=>'09665-000'
,'org_telfax'=>'(11) 4173-3594'
,'org_adm_mail'=>'adm@plastcooper.com.br'
,'org_livro'=>'LvrMtr-01'  // pode ter mais de um pois são permitidas atualizações
,'org_urn'=>'br;sp:cooperativa;plastcooper'
,'livro-matricula'=>[
  'estatuto'=>'compilado', // usando Coopcent para demo
  //'ata-fundacao'=>'2016-05-02'  // usando Coopcent para demo
]
];

$urn_path = urn2path($livroContents['org_urn']);
$f_path = "$baseUrnPath/$urn_path";
?>

<head>
 <meta charset="UTF-8">
 <title>NATRICULA COOP</title>
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
        . file_get_contents("$prjPath/src/assets/livroMatricula-v02-print1.css")
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

<!-- CAPA -->
<?php
 $template = $m->loadTemplate('livroMatricula-capa-v01');
 echo $template->render($livroContents);
?>


<!-- TERMO DE ABERTURA -->
<?php
 $template = $m->loadTemplate('livroMatricula-termoAbertura-v01');
 echo $template->render($livroContents);
?>


<!-- ATA DE FUNDAÇÃO -->
<?php
  $atas = preg_grep( '/^\d\d\d\d-\d\d-\d\d\.htm/', scandir("$f_path/ata") ); // glob("*.htm"));
  sort($atas);
  $htm_ataFund = file_get_contents("$f_path/ata/$atas[0]"); // a primeira é a de fundação.
  echo $htm_ataFund;
?>

<!-- ESTATUTO -->
<?php
  $htName = $livroContents['livro-matricula']['estatuto'];
  $f = "/home/peter/gits/contacoop-livros/data/raw/$urn_path/estatuto/$htName.htm";
  $htm_estatuto = file_get_contents($f);
  echo $htm_estatuto;
  // die("\ndebg\n");
?>


<!-- FICHAS DE MATRICULA -->
 <?php
 // use "composer update" in the same folder of json.
  $input = json_decode(file_get_contents($input),true);
  $template = $m->loadTemplate('livroMatricula-ficha-v01.mustache');

  foreach (array_values($input) as $r) {
    echo $template->render($r);
  }
?>

<!-- TERMO DE ENCERRAMENTO -->
<?php
 $template = $m->loadTemplate('livroMatricula-termoFechamento-v01');
 echo $template->render($livroContents);
?>

</body>

</html>
