<?php
// cooperados-CopPlastcooper.csv  cooperados-tudo-modoBold.csv

$c= new getCoops();

$coops = $c->get_org('data/raw/cooperados-CopPlastcooper.csv');
$rg_coops = array_ofKey('rg' , $coops['cooperados'], true);

$blk = $c->get_allPersons('data/raw/cooperados-tudo-modoBold.csv',true);
$x = $c->blocks_parse($blk,$rg_coops,'rg_num');
var_export($x);

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
    $rec = [];
    for($i=1; $i<=count($blks); $i++) {
      $r = $blks[$i];
      $r1 = str_getcsv($r[1]);
      $nome = trim($r1[0]);
      if ($nome) {
        $r23 = str_getcsv($r[23]);
        $rec0 = [
          'nome'=>$nome,  'dump_n'=>$i,   'matricula'=>$r1[4],
          'rg_aux'=>$r23[3],
          'cpf'=>$r23[1], 'rg_num'=>RGformat_num($r23[3]), 'rg_exp'=>RGformat_exp($r23[5]),
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


?>
