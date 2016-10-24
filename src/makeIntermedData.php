<?php
// cooperados-CopPlastcooper.csv  cooperados-tudo-modoBold.csv

$opt = [
    'j'=>true,  // saidas json
    'd'=>true,  // $debugResto
    'v'=>true,  // validar dados brutos
    'e'=>true,  // use EDI (reconfigurar array quando for usar)
    'h'=>true,  // help
];
$opt   = array_merge( $opt, getopt("jdveh") );
$optNo = array_reduce( $opt, function($c,$x){$c = $c&&$x; return $c;}, true );

if ($optNo || !$opt['h']) die(<<<EOT
  ---- Make Intermediary Data ----
  OPITIONS:
  -j saidas json
  -d debug resto;
  -v validar dados full
  -e use EDI;
  -h este help.\n\n
EOT
);


/////////////////////////////

$EDI = [  // obtido do EDI.CVS já preenchido
  'matricula' => 'l1c4',
  'Endereco-logradouro-tipo' => 'l3c1',
  'Endereco-logradouro-nome' => 'l3c4',
  'Endereco-logradouro-num' => 'l3c8',
  'Endereco-cidade' => 'l5c8',
  'Endereco-cep' => 'l7c1',
  'Endereco-codIBGE' => 'l7c4',
  'Endereco-uf' => 'l7c7',
  'Endereco-paisCod' => 'l7c8',
  'email' => 'l9c4',
  'sexo' => 'l11c1',
  'EstadoCivil' => 'l11c2',
  'grauInstrucao' => 'l11c6',
  'NomePai' => 'l13c1',
  'NomeMae' => 'l13c5',
  'DataNascimento' => 'l16c1',
  'DataNascimento-uf' => 'l16c2',
  'DataNascimento-codIBGE' => 'l16c5',
  'DataNascimento-codPais' => 'l16c7',
  'cpf' => 'l23c1',
  'rg_num' => 'l23c3',
  'rg_exp' => 'l23c5',
  'dependentes-qt' => 'l50c1',
];

$c= new getCoops();

$coops = $c->get_org(__DIR__.'/../data/raw/cooperados-CopPlastcooper.csv');
$rg_coops = array_ofKey('rg' , $coops['cooperados'], true);

$blk = $c->get_allPersons(__DIR__.'/../data/raw/cooperados-tudo-modoBold.csv',true);

if (!$opt['v']) {
  $c->blocks_parse($blk,'validar');
  die("\n");
}

$x = $c->blocks_parse($blk,$rg_coops,'rg_num');

if (!$opt['d']) {  // gera planilha para preencher EDI.CSV
  foreach ($x as $r){
    $z = $r['resto'];
    $sep = join(',', array_fill(1, count(str_getcsv($z[0])), '--') );
    foreach($z as $linha)
      echo "\n$linha";
    echo "\n$sep";
  } // for
  die("\n");
} elseif (!$opt['j']) echo json_encode($x); else var_export($x);





//// LOGIC


class getCoops {

  /**
   * Pega blocos do CSV contendo TUDO, e faz parse, opcionalmente filtrando itens.
   * @param $blk array blocos.
   * @param $filt_lst array das keys selecionadas ou NULL para pegar tudo.
   * @param $filt_key string chave usada pelo filtro (quando not NULL).
   * @param $json boolean true para JSON output.
   * @param $json boolean true para output de mensagens de debug.
   */
  function blocks_parse($blks,$filt_lst=NULL,$filt_key='rg_num',$json=false,$debug=0) {
    global $EDI;
    $useEDI = (count($EDI)>1);
    $rec = [];
    $validar=false;
    if (is_string($filt_lst)) {
      $validar = ($filt_lst=='validar');
      $filt_lst=NULL;
    }
    for($i=1; $i<=count($blks); $i++) {
      $r = $blks[$i];
      $r1 = str_getcsv($r[1]);
      $nome = trim($r1[0]);
      if ($validar && $nome) {
        $r23 = str_getcsv($r[23]);
        $matricula = $r1[4];
        $cpf    = CPFformat($r23[1]);
        $rg_num = RGformat_num($r23[3]);
        $rg_exp = RGformat_exp($r23[5]);
        $aux = "\n$matricula,$nome,$cpf,$rg_num,$rg_exp";
        if (strrpos($aux,'?')) echo $aux;
      } elseif ($nome && $useEDI) {
        $rec0 = [];
        foreach($EDI as $varname=>$ref) if (preg_match('/^l(\d+)c(\d+)$/',$ref,$m)) {
          list($lin,$col) = array_splice($m,1);
          $cols = str_getcsv($r[$lin]);
          $rec0[$varname]=$cols[$col];
        } // foreach
        // falta automatizar a partir de campoTipo de livroMatricula-campos.csv
        $rec0['cpf']    = CPFformat($rec0['cpf']);
        $rec0['rg_num'] = RGformat_num($rec0['rg_num']);
        $rec0['rg_exp'] = RGformat_exp($rec0['rg_exp']);

        if (  !$filt_lst || in_array($rec0[$filt_key],$filt_lst)  )
          $rec[] = $rec0;
      } elseif ($nome) {
        $r23 = str_getcsv($r[23]);
        $rec0 = [
          'nome'=>$nome,  'dump_n'=>$i,   'matricula'=>$r1[4],
          'cpf'=>CPFformat($r23[1]), 'rg_num'=>RGformat_num($r23[3]), 'rg_exp'=>RGformat_exp($r23[5]),
          'resto'=>$r
        ];
        if ($debug) echo "\n$i=\n\t$r1[0] matricula=$r1[4]\n\t$r[26]\n\t$r[27]\n\t$r[28]\n\t$r[29]";
        if (  !$filt_lst || in_array($rec0[$filt_key],$filt_lst)  )
          $rec[] = $rec0;
      } // if nome
    } // for
    return $rec;
  } // func


  function get_org($coopList_file,$json=false) {
    $lines = file($coopList_file);
    $line0 = trim( array_shift($lines) );
    if ( preg_match('/^(.+?)\((\d+)\),/',$line0,$m) )
      list($org,$org_id) = array_slice($m,1);
    else
      die("\nERRO1: sem header institucional esperado.");
    $coop = ['org'=>$org, 'org_id'=>$org_id, 'cooperados'=>[]];
    //echo "\n  RESULTS:\norg=$org, org_id=$org_id\n";
    $escaPageHead = false;
    foreach($lines as $l) {
      if (trim($l)==$line0) {
        $escaPageHead = true;
        //echo "\nPAGE----------\n";
      } else if (!$escaPageHead || !preg_match('/,,,,,,,,/',$l)) {
          $r = str_getcsv($l);
          if ($r[0]=='Identidade:' && $r[1]) {
            //echo "\n\tRG=$r[1],$r[4]";
            $coop['cooperados'][] = ['rg'=>RGformat_num($r[1]),'rg_exp'=>RGformat_exp($r[4])];
          }
          $escaPageHead = false;
      }
    } // map
    $coop['cooperados_n'] = count($coop['cooperados']);
    return $json? json_encode($coop,true): $coop;
  }

  function get_allPersons($coopList_file,$json=false) {
    //configs:
    $line0 = ",,,,,,,,SENO E COSSENO CONTABILIDADE EIRELI - ME,";

    $escaPageHead = false;
    $blocks = [];
    $blocks_n = 0;
    foreach(file($coopList_file) as $l) {
      $l = trim($l);
      if ($l==$line0) {
        $escaPageHead = true;
        //echo "\nPAGE----------\n";
      } else if (!$escaPageHead || !preg_match('/,,,,,,,,Emiss|Relatório de Autônomos,,,,,,,,,P/u',$l)) {
          if ($l=="Autônomo,,,,Matrícula,,,,,")
            $blocks_n++;
          $blocks[$blocks_n][] = $l;
          $escaPageHead = false;
      }
    } // map
    return $blocks;
  }

}

//////////////  RG lib (falta validar digito verificador)

function RGformat_exp($v0,$errChar='?') {
  $v = preg_replace('/\W/', '', strtoupper($v0) );
  $v = substr($v,0,3).'-'.substr($v,3);
  return (strlen($v)==6)? $v: "$errChar$v0";
}

function RGformat_num($v0,$errChar='?') {
  // source http://pt.stackoverflow.com/a/22432/4186
  $v = preg_replace('/[^\dX]/','',strtoupper($v0));
  $len = strlen($v);
  return ($len==8 || $len==9)?
    preg_replace('/^(\d{1,2})(\d{3})(\d{3})([\dX])$/','$1.$2.$3-$4',$v):
    "$errChar$v0"
  ;
  // SSP (Secretaria de Segurança Pública) MG não tem dígito verificador?!
}

/*
function RGvalid($v) {
  $v = preg_replace('/[^\dX]/', '', strtoupper($v0));
  if preg_match('/^(\d{1,2})(\d{3})(\d{3})([\dX])$/','$1.$2.$3-$4',$v):
  return true;
}

// preenche zero se preciso e combine com [2,3,4,5,6,7,8, 9]
*/


///////////// LIB-Utils ()

/**
 * Get only specified key of an array of associative arrays.
 * @param $key string with the key.
 * @param $a array to be scanned.
 * @param $ignoreNulls boolean true for ignore nulls, falses and empties.
 * Hum... there are a native PHP funcion to do it?? reduce? map?
 */
function array_ofKey($key,$a,$ignoreNulls=true) {
  $r = [];
  foreach ($a as $x)
		if ( array_key_exists($key,$x)  &&  (!$ignoreNulls || $x[$key]) )
			$r[]=$x[$key];
  return $r;
}


function validar_cpf($cpf,$clean=true) {
  // baseado em https://gist.github.com/guisehn/3276015
  // completo em http://www.geradorcpf.com/script-validar-cpf-php.htm
	if ($clean)
    $cpf = preg_replace('/[^0-9]/', '', (string) $cpf); // \D
  else $cpf = (string) $cpf;
	if (strlen($cpf) != 11 || $cpf == '00000000000' ||
        $cpf == '11111111111' ||
        $cpf == '22222222222' ||
        $cpf == '33333333333' ||
        $cpf == '44444444444' ||
        $cpf == '55555555555' ||
        $cpf == '66666666666' ||
        $cpf == '77777777777' ||
        $cpf == '88888888888' ||
        $cpf == '99999999999'
  ) return false;  // usar /^(?:1+|2+|3+|4+|5+|6+|7+|8+|9+)$/
	for ($i = 0, $j = 10, $soma = 0; $i < 9; $i++, $j--)
		$soma += $cpf{$i} * $j;
	$resto = $soma % 11;
	if ($cpf{9} != ($resto < 2 ? 0 : 11 - $resto))
		return false;
	for ($i = 0, $j = 11, $soma = 0; $i < 10; $i++, $j--)
		$soma += $cpf{$i} * $j;
	$resto = $soma % 11;
	return $cpf{10} == ($resto < 2 ? 0 : 11 - $resto);
}

function mask($val, $mask) {
  // REVISAR com sprintf!
   if (!$val) return '?';
   elseif ($mask=='cnpj')    $mask = '##.###.###/####-##';
   elseif ($mask=='cpf') $mask = '###.###.###-##';
   elseif ($mask=='cep') $mask = '#####-###';
   elseif ($mask=='data') $mask = '##/##/####';
   $maskared = '';
   $k = 0;
   for($i = 0; $i<=strlen($mask)-1; $i++) {
     if($mask[$i] == '#') {
       if (isset($val[$k])) $maskared .= $val[$k++];
     } else {
       if(isset($mask[$i])) $maskared .= $mask[$i];
     }
   }
   return $maskared;
}

function CPFformat($val) {
  $val = preg_replace('/[^0-9]/', '', (string) $val); // \D
  if (validar_cpf($val,false))
    return mask($val,'cpf');
  else return "?$val";
}

?>
