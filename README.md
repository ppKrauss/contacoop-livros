# contacoop-livros

Projeto de editoração padronizada dos Livros Contábeis de Cooperativas (contacoop-livros).

* pasta [`data`](data) contém as definições de campos em CSV e dados ilustrativos simulando dados reais.
* pasta [`src`](src) geradores de templates e templates (ver pasta `assets`).

## Arquivos e pastas de dados

Organização da pasta local `data/raw` (dados locais e sigilosos): 

* Pastas: `{jurisdição-uf}/{autoridade}/{documento-tipo}`
* Arquivos nas respectivas pastas de `tipo-documento`: `{documento-data}-{documento-descritor}.ext`
* (somente downloads) Arquivos soltos: `{jurisdição-uf},{autoridade},{documento-tipo},{documento-data}-{documento-descritor}.ext`
* Detalhes e exemplos de ortografia de cada campo, ver [convenções de nomenclatura nesta planilha](https://docs.google.com/spreadsheets/d/13pz0MDDlrDdHWLRGi5JRAQIfTJbM0B_T7XGozJ_5e6c/).

Formatos preferenciais:

* Códigos-fonte dos formatos abertos (TXT, CSV, JSON, HTML, XML, etc.) *UTF-8*, sem BOM e com quebras de linha simples tipo UNIX.

* Conteúdo estruturado em HTML5,`.htm`, e (quando demandar) pasta com mesmo nome cotendo imagen, etc. Marcação semântica com Microdata, layout (exceto ênfases) com CSS.

* Conteúdo original em PDF+imagem indexado (text copiável), `-original.pdf`.

* Conteúdo para impressão opcional, `-print.pdf`.

* Imagens originais em [JPEG-2000-lossless](http://softwareengineering.stackexchange.com/q/195359/84349),`.jp2`.

* Imagens para visualização online ou derivadas das originais: `.png` ou `.jpg`.

## Etc

Este projeto encontra-se em construção, alguns elementos já se encontram [melhor definidos na Wiki](https://github.com/ppKrauss/contacoop-livros/wiki).
