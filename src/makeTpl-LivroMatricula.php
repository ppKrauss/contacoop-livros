<?php
/**
 * Gera esboço preliminar dos templates de cada seção de um livro contábil.
 * EXEMPLOS DE USO (no terminal):
 *  php src/makeTpl.php matricula
 *  php src/makeTpl.php matricula
 */

// use "composer update" in the same folder of json.
require  __DIR__.'/vendor/mustache/mustache/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

// config. by terminal:
$cmd = isset($argv[1])? $argv[1]: '';
$f = __DIR__;
if ($cmd=='matricula')
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
  }
  return $s;
}

/**
 * Get data from CSV file, only the listed keys.
 * @param $f string file (.csv) with path.
 * @param $flist array of column names.
 * @return array of assoc with only flist keys.
 */
function getCsvFields($f,$flist=NULL) {
	$r = [];
	$t = array_map('str_getcsv', file($f));
	$thead = array_shift($t);
	foreach($t as $x) {
		$a = array_combine($thead,$x);
		if ($flist===NULL)
      $r[]=$a;
    elseif (isset($a[$flist[0]])) {  // ~ array_column()
			$tmp = [];
			foreach ($flist as $g) $tmp[$g] = $a[$g];
			$r[] = $tmp;
		}
	}
	return $r;
}
?>
