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
  <?php
  // paged com http://www.princexml.com/samples/webarch/forprint.css
  // ver PDF e HTML em http://www.princexml.com/samples/#webarch
  echo "<style>


@media screen and (min-width: 480px) {
  body {
      background-color: lightblue;
  }

  /* controle básico dos campos */
	.vtexto, .vdata, .vinteger {
		font-style: italic;
	}

	.wd30em { width: 35ex; display: inline-block;}
	.wd15em { width: 15em; display: inline-block;}
	.wd10em { width: 8em; display: inline-block;}
	.wd05em { width: 5em;  display: inline-block;}
	.wd03em { width: 3em;  display: inline-block;}
	.wd30em:empty, .wd15em:empty,
	.wd10em:empty, .wd05em:empty,
	.wd03em:empty { /* lacuna */
		border-bottom:1pt dashed #222;
	}

	section {font-size: 14pt;}
	section p {line-height:150%;}

	/* Demais formatações */
	.FotoCooperado {
		padding: 2px;
		margin:  0;
	}
  .FotoCooperado img {
		width:   340px;
	}

	section.dados {
		width:58em;
		/*text-align:justify; word-spacing:1em; */
	}
	section.dados h1, section.dados h2 {
		color:#466;
	}
} /* fim media screen */

/* = = = = = = = = = = = = = = = = = = = = = = = = = = */

@media print {
  @page {
    size:   A4 portrait;
    margin: 0.5cm 0.5cm 0.9cm 0.5cm;
    border-bottom: thin solid black;

    @bottom-right {
      /* background-color:red; */
      font-size: 10pt;
      content: counter(page);
      vertical-align: top;
      text-align: outside;
      margin: 1mm
    }
    @bottom-center {
      content: \"CooperTal \\00a0 \\00a0 •\\00a0 \\00a0  Livro de Matrícula\";
    }

  }
  @page :first {
    border-top: none;
    border-bottom: none;
    @bottom {
      content: normal;
    }
  }


  body {
    font-family: Helvetica,Verdana;
    font-size: 8pt;

      /* background-color: lightblue; */
  }

	.vtexto, .vdata, .vinteger {
		font-style: italic;
	}

	.wd30em { width: 35ex; display: inline-block;}
	.wd25em { width: 25ex; display: inline-block;}
	.wd15em { width: 15em; display: inline-block;}
	.wd10em { width: 8em;  display: inline-block;}
	.wd05em { width: 5em;  display: inline-block;}
	.wd03em { width: 3em;  display: inline-block;}

	.wd30em:empty, .wd15em:empty, .wd25em:empty,
	.wd10em:empty, .wd05em:empty,
	.wd03em:empty { /* lacuna */
		border-bottom:1pt dashed #222;
	}

	section {
		font-size: 9pt;
	}
	section p, section td, blockquote {
    line-height:150%;
  }

  .box {
	margin:0 auto;
	padding:18px;
	border:4px solid #577;  /* #9A9EA7 */
	background-color:#FFF;
	-moz-border-radius:5px;
	-webkit-border-radius:5px;
	border-radius:5px;
  }

	/* Demais formatações */
  .FotoCooperado {
		padding: 2px;
		margin:  0;
	}
  .FotoCooperado img {
		width:   240px;
	}

	section.dados {
		/*width:30em; text-align:justify; word-spacing:1em; */
	}
	section.dados h1, section.dados h2 {
		color:#466;
	}

  div.blk {
    margin-top:1.6em;
  }

  div.blk blockquote {
    margin-top:2pt;
  }

  article { page-break-after: always; }

} /* fim media print */

  </style>";
  ?>

 </head>

 <body>

   CAPA   etc

   <p>pdftk original.pdf cat 2-end output semPrimeira.pdf</p>

   <hr>

   <div style="page-break-after: always"></div>

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
  $template = $m->loadTemplate('livroMatricula-artigo2');

  foreach (array_values($input) as $r) {
    echo $template->render($r);
  }
?>

</body>

</html>
