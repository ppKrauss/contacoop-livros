<?php
/**
 * Make dados e interfaces.
 */
// usar
//  php src/makeIntermedData.php -e -j > data/raw/livroMatricula-dados.json
//  php src/makeIntermedData.php -v -c | more
// saidas cooperados-CopPlastcooper.csv  cooperados-tudo-modoBold.csv

require __DIR__.'lib.php';

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

$EDI = [  // obtido do EDI.CVS já preenchido, php src/makeTpl.php matricula-vars
  // FALTA parser indicar tipo/subtipo, ex. 'cpf' => 'l23c1#string-cpf','Endereco-codIBGE' => 'l23c1#string-codIBGE',
  // novos em 2016-10: residPropria, codCBO, codCategoriaX, codRacaCor.
  'matricula' => 'l1c4',
  'NomeCooperado' => 'l1c0',  // cuidado não está pegando
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
  'codRacaCor' => 'l11c4',
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
  'residPropria' => 'l29c1',
  'codCBO' => 'l46c7',
  'codCategoriaX' => 'l49c7',
  'dependentes-qt' => 'l50c1',
  'codRetencao' => 'l52c1',
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
    } // if
    $x=array_values($r);
// falta map ignorar valores quesão array!
    $x[]= fotoFilename($r['NomeCooperado']);
    fputcsv($stdout,$x);
    {var_dump($x);die("\n??error\n");}
  } // for
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
?>
