<?php
/**
 * Lib de uso geral do projeto, para implificar includes.
 */

require  __DIR__.'/vendor/prince.php';

// use "composer update" in the same folder of json.
require  __DIR__.'/vendor/mustache/mustache/src/Mustache/Autoloader.php';
$mustacheFolder = __DIR__."/assets";
Mustache_Autoloader::register();
$m = new Mustache_Engine;
$m = new Mustache_Engine(array(
  'loader' => new Mustache_Loader_FilesystemLoader($mustacheFolder),
));

///// convenções

/**
 * Retorna as partes, devidamente interpretadas, de uma URN LEX.
 * Jurisdição, autoridade, tipo de documento, e descritor do documento.
 * Opcionalmente, e fora do padrão LEX, "sec" é seção tratada como separata.
 * As separatas, conforme função urn2path(), ficam numa subpasta.
 * @param $urn string input URN LEX ou similar.
 * @param $abrev boolean para usar ou não rótulos abreviados.
 * @return associative array com componentes na forma [completo,parte1,parte2,...].
 */
function splitUrnLex($urn,$abrev=true) {
  $p = explode(':',$urn); //
  $base = $abrev?
    ['jur', 'aut', 'tip', 'dsc', 'sec']:
    ['jurisdiction', 'authority', 'measure', 'details', 'annex']  # draft-spinosa-urn-lex v9
  ;
  return array_map(
    function($v) {return array_merge( [$v], explode(';',$v) );},
    array_combine(
      (count($p)>4)? $base: array_slice($base,0,3),
      $p
    )
  );
}

function urn2path($urn) { // URN LEX to ELI path
  return str_replace(['-',':',';',],['_','/','-'],$urn);
}
function path2urn($urn) { // ELI path to URN LEX
  return str_replace(['_','/','-'],['-',':',';',],$urn);
}


/////////////////////////
///////// usado por makeIntermedData:



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
  if (strlen($cpf) == 10) $cpf = "0$cpf";

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
  $val = preg_replace('/[^0-9]/', '', $val); // \D
  if (validar_cpf($val,false))
    return mask($val,'cpf');
  else return "?$val";
}

function CEPformat($val) {
  $val = preg_replace('/\D/', '', $val);
  return (strlen($val)==8)? mask($val,'cep'): "?$val";
}

/**
 * formata conforme convenção de rótulo de tipo.
 * Falta criar classe com array de funções, associando direto os rótulos.
 * 1) case, 2) isset(funcassoc); 3) else $tipoDefault.
 */
function formatar($val,$tipo,$tipoDefault='nada') {
  global $codIBGE2cidade;
  global $ufOk;

  switch ($tipo) {
    case 'string-cep':   return CEPformat($val);
    case 'string-cpf':   return CPFformat($val);
    case 'string-codIBGE':  return isset($codIBGE2cidade[$val])?
      $val:
      "?$val"
      ;
    case 'string-codIBGE2cidade':  return isset($codIBGE2cidade[$val])?
      $codIBGE2cidade[$val]:
      "?cidade cod. $val"
      ;
    case 'string-codIBGE-pais': return ($val==105)? 'Brasil': "exterior ($val)";
    case 'string-codIBGE-pais2nacionalidade': return ($val==105)? 'brasileiro': "estrangeiro ($val)";
    case 'string-rgNum':  return RGformat_num($val);
    case 'string-rgExp':  return RGformat_exp($val);
    case 'string-uf': $v=strtoupper(trim($val)); return isset($ufOk[$v])? $v: "?$val=$v";
    case 'integer':       return preg_replace('/[^\d]+/','',$val);
    case 'string-nomePtBR':  return format_nomePtBR($val);
    case 'string-trim':  return trim($val);
    case 'string-upper':  return mb_strtoupper(trim($val),'UTF-8');
    case 'string-tr_upper':  return mb_strtoupper(trim($val),'UTF-8');
    case 'nada':
    default:             return $val;
  }
}

/**
 * Formata string de nome próprio brasileiro.  Falta tratar "Papa Pio XXIII"e outras.
 * regex romano simples = '^M{0,4}(CM|CD|D?C{0,3})(XC|XL|L?X{0,3})(IX|IV|V?I{0,3})$';
 */
function format_nomePtBR($val, $stdSufix=true, $useOthers=true) {
  // $curloc = setlocale(LC_ALL, 0);...  nem assim!
  // if (substr($curloc,0,20)!='LC_CTYPE=pt_BR.UTF-8') die("\nERRO323: Brazil requer LC_CTYPE=pt_BR.UTF-8!!");
  $minuscular = [
    'de',  'do', 'da', 'dos', 'das',
     'e', 'em', 'na', 'no', 'nas', 'nos'
  ];
  if ($useOthers) // outros populares no BR mas que não são pt.
    $minuscular = array_merge($minuscular,[
        'di','dello', 'della', 'dalla','dal', 'del','van', 'von', 'y'
    ]);
  $r=[];
  $partes = preg_split( '/\s+/u', mb_strtolower(trim($val),'UTF-8') );
  // pode-se usar $partes[0] para qualificar gênero via heurística. Ver Gender.pm.
  $n = count($partes)-1;
  if ($stdSufix && ($partes[$n]=='jr'||$partes[$n]=='jr.')) $partes[$n]='júnior';
  foreach ($partes as $w) $r[] = in_array($w,$minuscular)?
      $w:
      mb_convert_case($w,MB_CASE_TITLE,'UTF-8') . (
        (mb_strlen($w,'UTF-8')==1)? '.': ''
      )
  ;
  return join(' ',$r);
}

function fotoFilename($nome) {
  $ps = explode(' ',$nome);
  $pnome = $ps[0];
  $sobrenome = array_pop($ps);
  $aux = iconv('utf-8', 'ascii//TRANSLIT', "$pnome$sobrenome"); // desacent
  return "$aux.jpg";
}

/**
 * Separa string de nome próprio em primeiro nome e sobrenome.
 * Faz uso de cache gerado por ..., ver pasta data.
 */
?>
