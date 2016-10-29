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

 <style>
  <?php
  // paged com http://www.princexml.com/samples/webarch/forprint.css
  // ver PDF e HTML em http://www.princexml.com/samples/#webarch
  echo "\n@media screen and (min-width: 480px) {\n";
  echo file_get_contents(__DIR__.'/assets/livroMatricula-v02-screen1.css');
  echo "
}

/* = = = = = = = = = = = = = = = = = = = = = = = = = = */

@media print {\n";
  echo file_get_contents(__DIR__.'/assets/livroMatricula-v02-print1.css');
  echo "\n}";
?>
</style>

 </head>

 <body>

   CAPA   etc

   <p>pdftk original.pdf cat 2-end output semPrimeira.pdf</p>

   <hr>

   <div style="page-break-after: always"></div>


<article class="capa" style="page-break-after: always"><!-- capa-->
<center>
  CAPA OU ANTECAPA: usando dados "fake" ...
  <b>Aguardando OCR dos documentos da PLASTCOOPER</b>, usando apenas ata e nome da COOPECENT...

  <br/>

<hr/>
  <br/>
  <br/>PLASTCOOPER - COOPERATIVA IND.DE TRAB. PLÁSTICOS
  <br/>CNPJ 003.852.353/0001-08
  <br/>plastcooper@plastcooper.com.br  (dominio na Locaweb sob responsabilidade de Marcos Silva da http://gvfinfo.com.br/)
  <br/>
  <br>Rua Eugênia Sá Vitalle, 883 • Taboão - São Bernardo do Campo - SP • CEP 09665-000
  <br/>Tel/Fax. (11) 4173-3594 • E-mail: adm@plastcooper.com.br


  <hr/>
  ...
  <h1>COOPERATIVA CENTRAL DE CATADORES E CATADORAS DE MATERIAIS RECICLÁVEIS DO GRANDE ABC - <b>COOPECENT ABC</b></h1>
  <h2><font face="Calibri">CNPJ <b>10.203.963/0001-46</b></font></h2>
  <h2><font face="Calibri">INSCRIÇÃO ESTADUAL <b>286.289.344.110</b></font></h2>
  <h2><font face="Calibri">NIRE <b>35.400.097.477</b></font></h2>
  <p><font face="Calibri" size="3"><i>
      rua Caracas, 120 &#160;-&#160; DIADEMA &#160;-&#160; SP
      &#160;•&#160; CEP: 09921-090
      &#160;•&#160; Tel: (11) 4054-2263
      &#160;•&#160; <a href="http://www.coopcentabc.org.br">www.coopcentabc.org.br</a>
  </i></font></p>

</center>
</article>

<article class="ata" style="page-break-after: always">
  <header>
    <p><font face="Calibri" size="4"><b>ATA DA ASSEMBLEIA GERAL EXTRAORDINÁRIA DA COOPECENT ABC</b></font></p>
  </header>
  <p><font face="Calibri" size="3">Ao segundo dia do mês de maio de 2016, realizou-se esta Assembleia Geral Extraordinária da Cooperativa Central de Catadore e Catadoras de Materiais Recicláveis do Grande ABC -Coopecent ABC, na rua Paulo Lazzuri, número 01 - Jardim Bela Alvarenga, em São Bernardo do Campo, conforme o edital de convocação publicado no jornal ABCD Maior, edição número 1035, de vinte e dois de abril de 2016. Reuniram-se cooperados e cooperadas representantes das Cooperativas Singulares que integram a Coopecent ABC: Cooperativa de Trabalho dos Catadores de Materiais Recicláveis de Ribeirão Pires - Cooperpires; Cooperativa de Catadores de Papel, Papelão e Material Reciclável do Município de Mauá- Coopercata; Cooperativa de Reciclagem Cidade Limpa - Cooperlimpa; Cooperativa de Trabalho dos Catadores em Coleta e Triagem de Material Reciclável - Reluz; Cooperativa de Trabalho de Catadores de Material Reciclável de São Bernardo do Campo - Cooperluz e membros de outros grupos que atuam na Coopecent ABC.</font></p>
  <p><font face="Calibri" size="3">A Assembleia Geral Extraordinária teve início às 11 horas, em terceira convocação, com a seguinte ordem do dia: 1º Alteração do Estatuto Social e aprovação do Regimento Interno; 2º</font><font face="Arial" size="2"><i> </i></font><font face="Calibri" size="3">Prestação de contas do exercício de 2015; 3º Eleição dos membros do Conselho Fiscal e 4º Eleição dos membros da Diretoria.</font></p>
  <p><font face="Calibri" size="3">Dando início aos trabalhos o Sr. Francisco Inácio da Costa, Presidente da Coopecent ABC, deu as boas vindas aos participantes, fez a leitura da ordem do dia e convidou a mim, Cleide Fiore, colaboradora da Coopecent ABC, para secretariar a reunião. Em seguida passou a palavra para a senhora Patrícia Frazão da Silva Santos, Tesoureira da Coopecent ABC, que agradeceu a presença de todos, fez considerações sobre o momento atual enfatizando a necessidade das cooperativas integrantes da Coopecent ABC se organizarem, para exigir das Prefeituras a contratação pelos serviços prestados de coleta seletiva, conforme determina a Política Nacional de Resíduos Sólidos. Patrícia, salientou que dois municípios do ABCDMRR já haviam realizado a contratação, Ribeirão Pires e São Caetano do Sul, e os demais também deveriam seguir o mesmo caminho. Inclusive, segundo Patrícia, o Ministério Público e as Defensorias Públicas do ABC, já estão atuando para fazer com que os municípios cumpram a legislação vigente e contratem as cooperativas de catadores e catadoras de suas cidades.</font></p>
</article>
<?php
$f = '/home/peter/gits/contacoop-livros/data/raw/br-sp/cooperativa-coopcent_abc/estatuto/asArticle.htm';
$htm = file_get_contents($f);
echo $htm;
 ?>
 
 <?php
 // use "composer update" in the same folder of json.
  require  __DIR__.'/vendor/mustache/mustache/src/Mustache/Autoloader.php';
  Mustache_Autoloader::register();
  $m = new Mustache_Engine;

  $input = __DIR__.'/../data/raw/livroMatricula-dados.json';
  $input = json_decode(file_get_contents($input),true);
  $m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__)."/assets"),
  ));
  $template = $m->loadTemplate('livroMatricula-artigo-v02');

  foreach (array_values($input) as $r) {
    echo $template->render($r);
  }
?>

</body>

</html>
