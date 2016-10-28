<?php
// usar php src/makeIntermedData.php -e -j > data/raw/livroMatricula-dados.json
// cooperados-CopPlastcooper.csv  cooperados-tudo-modoBold.csv

$opt = [
  // revisar com '' visto que opcional vazio é false, e value vazio não passa nada.
    'e'=>true,  // use EDI (reconfigurar array quando for usar)
    'j'=>true,  // saidas json
    'c'=>true,  // usar com -e para saida CSV
    'd'=>true,  // $debugResto, 1=1linha, 2=2 linhas, etc.
    'v'=>true,  // validar dados brutos, 1=tudo, 2=filtrado
    'h'=>true,  // help
];
$opt   = array_merge( $opt, getopt("jd:v:ehc") );
$optNo = array_reduce( $opt, function($c,$x){$c = $c&&($x===true); return $c;}, true ); // !==''

if ($optNo || !$opt['h']) die(<<<EOT
  ---- Make Intermediary Data ----
  OPITIONS:
  -j saida json para opcao -e;
  -c saida CSV para opcao -e
  -v comando validar dados para CSV, 1=full, 2=filtrado;
  -e comando use EDI;
  -d comando debug 1,2,...N, para o resto (sem EDI);
  -h este help.\n\n
EOT
);

$codIBGE2cidade = [];
$ufOk = [];
$csv = array_map('str_getcsv',file(__DIR__.'/../data/municipios-IBGE.csv'));
$head = array_shift($csv);
foreach($csv as $r) {
    $x = array_combine($head,$r); //cod-IBGE,nome,UF,cod-munic-IBGE,creation,cod-lex
    $codIBGE2cidade[$x['cod-IBGE']] = $x['nome'];
    $ufOk[$x['UF']]=1;
}

/////////////////////////////

$EDI = [  // obtido do EDI.CVS já preenchido
  // FALTA parser indicar tipo/subtipo, ex. 'cpf' => 'l23c1#string-cpf','Endereco-codIBGE' => 'l23c1#string-codIBGE',
  'matricula' => 'l1c4',
  'NomeCooperado' => 'l1c0',
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

$EDI_tipo = [  // obtido do parser de variaveis no makeTpl.php
  'matricula' => 'integer',
  'NomeCooperado' => 'string-nomePtBR',
  'NomePai' => 'string-nomePtBR',
  'NomeMae' => 'string-nomePtBR',
  'Endereco-cep' => 'string-cep',
  'Endereco-codIBGE' => 'string-codIBGE',
  'Endereco-uf' => 'string-uf',
  'Endereco-paisCod' => 'string-codIBGE-pais',
  'email' => 'string-email',
  'EstadoCivil' => 'integer',
  'grauInstrucao' => 'integer',
  'DataNascimento' => 'string-date-ptBR',
  'DataNascimento-uf' => 'string-uf',
  'DataNascimento-codIBGE' => 'string-codIBGE2cidade',
  'DataNascimento-codPais' => 'string-codIBGE-pais2nacionalidade',
  'cpf' => 'string-cpf',
  'rg_num' => 'string-rgNum',
  'rg_exp' => 'string-rgExp',
  'dependentes-qt' => 'integer',
  'Endereco-logradouro-tipo'=>'string-tr_upper',
  'Endereco-logradouro-nome'=>'string-tr_upper',
];

$c= new getCoops();

$coops = $c->get_org(__DIR__.'/../data/raw/cooperados-CopPlastcooper.csv');
$rg_coops = array_ofKey('rg' , $coops['cooperados'], true);

$blk = $c->get_allPersons(__DIR__.'/../data/raw/cooperados-tudo-modoBold.csv',true);

if ($opt['v']!==true) {
  $c->blocks_parse($blk, 'validar', ($opt['v']==2)? NULL: $rg_coops, 'rg_num');
  die("\n");
}

if ($opt['d']!==true) $EDI = NULL;

$coopBlocks = $c->blocks_parse($blk,'e',$rg_coops,'rg_num','saldo1',24);

if ($opt['d']!==true) {  // gera planilha para preencher EDI.CSV
  for($nrecs = 0; $nrecs<$opt['d']; $nrecs++){
    $z = $coopBlocks[$nrecs]['dump'];
    $sep = join(',', array_fill(1, count(str_getcsv($z[0])), '--') );
    foreach($z as $linha)
      echo "\n$linha";
    echo "\n$sep";
  } // for
  die("\n");
} elseif (!$opt['j'] && !$opt['e'])
  echo json_encode($coopBlocks,JSON_PRETTY_PRINT);
elseif (!$opt['e'] && !$opt['c']) {
  $um = 1;
  $stdout = fopen('php://stdout','w');
  foreach($coopBlocks as $r){
    if ($um) {
      $x=array_keys($r);
      $x[]='arquivo-foto';
      fputcsv($stdout,$x);
      $um = 0;
    }
    $x=array_keys($r);
    $aux = fotoFilename($r['NomeCooperado']);
    $x[]=$aux;
    fputcsv($stdout,$r);
  }
  fclose($stdout);

} elseif (!$opt['e']) {
  //var_export($coopBlocks);
  foreach($coopBlocks as $r){
    $aux = fotoFilename($r['NomeCooperado']);
    echo "\n$aux,{$r['cpf']},{$r['matricula']},{$r['NomeCooperado']}";
  }
}
else
  echo "ERRO, sem opção de comando. Use -h\n";

//die("\nEDI.json=\n".json_encode($EDI,JSON_PRETTY_PRINT));



//// LOGIC


class getCoops {

  /**
   * Pega blocos do CSV contendo TUDO, e faz parse, opcionalmente filtrando itens.
   * @param $blk array blocos.
   * @param $filt_lst array das keys selecionadas ou NULL para pegar tudo.
   * @param $filt_key string chave usada pelo filtro (quando not NULL).
   */
  function blocks_parse(
    $blks,              // input data
    $cmd='validar',     // optional command or array of keys
    $filt_lst=NULL,     // valid values for primary key
    $filt_key='rg_num', // primary key name to $blks filtering
    $recloop_name='',   // optional record name for loop (tabela saldo1)
    $recloop_n=0,       // number of itens
    $debug=0
  ) {
    global $EDI;
    global $EDI_tipo;
    $useEDI = (count($EDI)>1);
    $useDump = false;
    $rec = [];
    $validar = ($cmd=='validar')? true: false;
    for($i=1; $i<=count($blks); $i++) {
      $r = $blks[$i]; // raw data
      $r1 = str_getcsv($r[1]);
      $nome = trim($r1[0]);
      if ($validar && $nome) {
        $r23 = str_getcsv($r[23]);
        $matricula = $r1[4];
        $xx['cpf']    = CPFformat($r23[1]);
        $xx['rg_num'] = RGformat_num($r23[3]);
        $xx['rg_exp'] = RGformat_exp($r23[5]);
        $aux = "\n$matricula,$nome,$xx[cpf],$xx[rg_num],$xx[rg_exp]";
        if (
            strrpos($aux,'?')
            &&
            (!$filt_lst || in_array($xx[$filt_key],$filt_lst))
        ) echo $aux;

      } elseif ($nome && $useEDI) {
        $rec0 = [];
        foreach($EDI as $varname=>$ref) if (preg_match('/^l(\d+)c(\d+)$/',$ref,$m)) {
          list($lin,$col) = array_splice($m,1);
          $cols = str_getcsv($r[$lin]);
          $rec0[$varname]= isset($EDI_tipo[$varname])?
            formatar($cols[$col],$EDI_tipo[$varname]):
            $cols[$col]
          ;
        } // foreach
        // $rec0['etcFuncional']: funcionais como idade(nascimento,dataRef)
        $rec0['Idade'] = 2015 - (int) preg_replace('#^\d\d/\d\d/#','',$rec0['DataNascimento']);
        $ft = $rec0['Foto'] = fotoFilename($rec0['NomeCooperado']);
        if (!file_exists(__DIR__."/assets/_local/fotos-cooperados/$ft"))
          $rec0['Foto'] = "";
        if ($useDump) $rec0['dump']=$r;
        if ($recloop_name) {
          // certo passar primeiros vals como parâmetro e usar mesmos campos.
          $aux = [
            'data'=>'', 'operacao'=>'', 'subscrito'=>'', 'integralizado'=>'', 'saldo'=>''
          ];
          $rec0[$recloop_name] = array_fill(0,$recloop_n,$aux);
        }
        if (  !$filt_lst || in_array($rec0[$filt_key],$filt_lst)  )
          $rec[] = $rec0;

      } elseif ($nome) {
        $r23 = str_getcsv($r[23]);
        $rec0 = [
          'nome'=>$nome,  'dump_n'=>$i,   'matricula'=>$r1[4],
          'cpf'=>CPFformat($r23[1]), 'rg_num'=>RGformat_num($r23[3]), 'rg_exp'=>RGformat_exp($r23[5]),
          'dump'=>$r
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
?>
