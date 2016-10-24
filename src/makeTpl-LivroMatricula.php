<?php
/**
 * Gera esboço preliminar dos templates de cada seção de um livro contábil.
 * EXEMPLOS DE USO (no terminal):
 *  php src/makeTpl.php matricula
 *  php src/makeTpl.php etc
 */

// use "composer update" in the same folder of json.
require  __DIR__.'/vendor/mustache/mustache/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

// config. by terminal:
$cmd = isset($argv[1])? $argv[1]: '';
$f = __DIR__;
if (substr($cmd,0,9)=='matricula')
  $f .= '/../data/livroMatricula-campos.csv';
else
  die("\n ERRO: argumento '$cmd' ainda desconhecido.\n (disponíveis matricula|x|y)\n");

$vars=[];

foreach (getCsvFields($f) as $r) {
  if ($r["bloco:titulo"] && substr($r["bloco:titulo"],0,1)!='*') {
    if (preg_match('/^\w+\s*(\d+)\s*:\s*(.+)$/i',$r["bloco:titulo"],$m))
      list($blkID,$blk_tit) = array_slice($m,1);
    else
      die("\nBLOCO COM FALHA ({$r["bloco:titulo"]}).\n");
    $vars[$blkID] = [['blk_tit'=>$blk_tit]];
  } elseif ($r["campoTitulo"]) {
    unset($r["bloco:titulo"]);
    $r["label"] = parseToLabel($r["campoTitulo"],'camel-by-original-pt');
    $vars[$blkID][] = $r;
  }
}


switch ($cmd) {

  case 'matricula-listvars':
  foreach($vars as $bloco_i=>$items) {
    echo "\n--- BLOCO-$bloco_i";
    foreach ($items as $i => $r) if (isset($r['label']))
      echo "\n\t\${$r['label']}";
  }
  die("\n");
  break;

  case 'matricula-vars':
  $f = __DIR__.'/../data/edi-senoConsseno-modelo2016Av1.csv';
  $layout = [];
  $ediVars = array_map('str_getcsv',file($f));
  for($linha=1; $linha<=count($ediVars); $linha++) if (isset($ediVars[$linha])) {
    for($col=1; $col<=count($ediVars[$linha]); $col++)
      if (isset($ediVars[$linha][$col]) && substr($ediVars[$linha][$col],0,1)=='$')
        $layout[substr($ediVars[$linha][$col],1)] = "l{$linha}c$col";
  }
  var_export($layout);
  die("\ndebig2\n");
  break;

  case 'matricula-html':
  echo "\n<article id='matricula-{{IDmatricula}}'>\n";
  foreach($vars as $i=>$r) {
    $m = new Mustache_Engine;
    $tmp = $m->render("
      {{#.}}{{#label}}\n\t<span class='{{label}}'><span>{{campoTitulo}}:</span><span class='{{campoTipo}}'>[var:{{label}}]</span></span>{{/label}}{{/.}}
    ", $r);
    $tmp = preg_replace('/\[var:(\w+)\]/', '{{$1}}', $tmp);
    echo "\n\n<section id='bloco-$i'><h2>{$r[0]['blk_tit']}</h2>\n\t$tmp</section>";
  }

  echo <<<EOF
  <table boder='1'>
  <thead>
    <tr><th rowspan='2'>Data</th> <th rowspan='2'>Histórico das subscrições de quotas-parte</th>
        <th colspan='3'>Movimento de Capital Social</th>
    </tr>
    <tr><th>Subscrito</th> <th>Realizado</th> <th>Saldo</th></tr>
  </thead>
  <tbody>
  <tr><td>{{Data}}</td> <td>{{HistoricoSubscricoesQP}}</td> <td>{{Subscrito}}</td> <td>{{Realizado}}</td> <td>{{Saldo}}</td></tr>
  <tr><td>2015-08-22</td> <td>.. descrição da operação ..</td> <td>123,50</td> <td>123,50</td> <td>123,50</td></tr>
  </tbody>
  </table>
EOF;
  echo "\n</article>";
  break;

  default:
    echo "\nERRO '$cmd' DESCONHECIDO.";
  break;
} // switch



////////////////// LIB ///////////////

function parseToLabel($s,$stdType='lex',$MAXLEN=20){
  $s = trim($s);
  $s = iconv('utf-8', 'ascii//TRANSLIT', $s);

  switch ($stdType) {
  case 'lex':
    $s = str_replace( ' ', '.', strtolower($s) );
    break;

  case 'camel-by-original-pt':
    $s = preg_replace('#\([^\)]+\)|/\w+#si',' ',$s);
    $s = str_replace( '-', ' ', $s );
    $s = ucwords(strtolower($s));
    $s = preg_replace('/(?:\s|^)(?:D[aeo]s?|Para|Em|Com|[OAE]s?)(?:\s|$)/u',' ',$s);
    $s = preg_replace('/\s+/u',' ',$s);
    if (strlen($s)>$MAXLEN) {
      $x = '';
      foreach(explode(' ',$s) as $i)
        if (strlen($x.$i)<=$MAXLEN) $x .= $i;
        else $x .= substr($i,0,1);
      $s=$x;
    } else
      $s = str_replace(' ', '', $s);
    break;

  default:
    die("\nERRO: tipo '$stdType' desconhecido\n");
  } // switch
  return $s;
} // func


/////  immport LIB

/**
 * Get data (array of associative arrays) from CSV file, only the listed keys.
 * @param $f string file (.csv) with path or CSV string (with more tham 1 line).
 * @param $flist array of column names, or NULL for "all columns".
 * @param $outJSON boolean true for JSON output.
 * @param $flenLimit integer 0 or limit of filename length (as in isFile function).
 * @return mix JSON or array of associative arrays.
 */
function getCsvFields($f,$flist=NULL,$outJSON=false,$flenLimit=600) {
	$t = getCsv($f,$flenLimit);
	$thead = array_shift($t);

	$r = [];
	foreach($t as $x) {
		$a = array_combine($thead,$x);
		if ($flist===NULL)
			$r[] = $a;
		elseif (isset($a[$flist[0]])) {  // ~ array_column()
			$tmp = [];  // NEED OPTIMIZE WITH array_intersection!
			foreach ($flist as $g) $tmp[$g] = $a[$g];
			$r[] = $tmp;
		}
	}
	return $outJSON? json_encode($r): $r;
}

/**
 * Standard "get array from CSV", file or CSV-string.
 * CSV conventions by default options of the build-in str_getcsv() function.
 * @param $f string file (.csv) with path or CSV string (with more tham 1 line).
 * @param $flenLimit integer 0 or limit of filename length (as in isFile function).
 * @return array of arrays.
 * @use isFile() at check.php
 */
function getCsv($f,$flenLimit=600) {
	return array_map(
		'str_getcsv',
		isFile($f,$flenLimit,"\n")? file($f): explode($f,"\n")
	);
}

/**
 * Check if is a filename string, not a CSV/XML/HTML/markup string.
 * @param $input string of filename or markup code.
 * @param $flenLimit integer 0 or limit of filename length.
 * @param $keyStr string '<' for XML, "\n" for CSV.
 * @return boolean true when is filename or path string, false when markup.
 */
function isFile($input,$flenLimit=600,$keyStr='<') {
	return strrpos($input,$keyStr)==false && (!$flenLimit || strlen($input)<$flenLimit);
}

?>
