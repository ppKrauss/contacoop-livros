# contacoop-livros

Projeto de editoração padronizada dos Livros Contábeis de Cooperativas.

Contém,
* pasta [`data`](data): definições de campos em CSV e dados ilustrativos simulando dados reais.
* pasta [`src`](src): geradores de templates e templates (ver pasta `assets`).

## Arquivos e pastas de dados
A pasta [`data`](data) contém todos os dados em arquivos e subpastas, algumas delas disponíveis apenas localmente (dados temporários e/ou sigilosos).

### Formatos preferenciais:

* Códigos-fonte dos formatos abertos (TXT, CSV, JSON, HTML, XML, etc.) *UTF-8*, sem BOM e com quebras de linha simples tipo UNIX.

* Conteúdo estruturado em HTML5,`.htm`, e (quando demandar) pasta com mesmo nome cotendo imagen, etc. Marcação semântica com Microdata, layout (exceto ênfases) com CSS.

* Conteúdo original em PDF+imagem indexado (text copiável), `-original.pdf`.

* Conteúdo para impressão opcional, `-print.pdf`.

* Imagens originais em [JPEG-2000-lossless](http://softwareengineering.stackexchange.com/q/195359/84349),`.jp2`.

* Imagens para visualização online ou derivadas das originais: `.png` ou `.jpg`.

### Dados em CSV

* `livroMatricula-campos.csv` define os nomes de cada campo válido nos templates dos livros de matrícula.
* `spec02-filenames.csv` define os nomes de pasta e path's válidos na pasta local de documentos (indiretamente define a URN dos documentos). 

* EDI:

  * arquivos com sintaxe `edi-{fornecedor}-modelo{ano}{versao}.{ext}`  com `ext` JSON ou CSV. Contém o nome de campo padronizado e a posição linha-colina de cada dado de uma planilha de [EDI](https://en.wikipedia.org/wiki/Electronic_data_interchange). Estabelece o "modelo de troca de dados" nas planilhas enviadas pelo fornecedor (autoridade responsável pelo dado). Exemplo: `edi-senoConsseno-modelo2016Av1.csv`.

  * `edi-{fornecedor}-layout{ano}{versao}.csv` dump de um registro sem dados para apoiar contratos de fornecimento baseados no `edi-{fornecedor}-modelo{ano}{versao}.csv` de mesmo `{ano}{versao}`. Exemplo: `edi-senoConsseno-layout2016Av1.csv`. 

  * Datasets de apoio ao edi: edi/[municipios-IBGE.csv](data/municipios-IBGE.csv) e edi/[stdCods-grauInstrucao.csv](data/stdCods-grauInstrucao.csv)... datasets usados como "dicionário" (tipicamente tabelas de-para com código-termo) para um ou mais tipos de dados fixados por EDI dos fornecedores. Os dicionários requerem atualização contínua.


### Pasta local data/raw
Organização da pasta local (não-mantida pelo git), `data/raw`: 

* Pastas: path com sintaxe `{jurisdição-uf}/{autoridade}/{documento-tipo}`
* Arquivos nas respectivas pastas de `tipo-documento`: `{documento-data}-{documento-descritor}.ext`
* (somente downloads) Arquivos soltos: `{jurisdição-uf},{autoridade},{documento-tipo},{documento-data}-{documento-descritor}.ext`
* Detalhes e exemplos de ortografia de cada campo, ver [convenções de nomenclatura nesta planilha](https://docs.google.com/spreadsheets/d/13pz0MDDlrDdHWLRGi5JRAQIfTJbM0B_T7XGozJ_5e6c/).

## Etc

Este projeto encontra-se em construção, alguns elementos já se encontram [melhor definidos na Wiki](https://github.com/ppKrauss/contacoop-livros/wiki).

### Protocolo de atualização e ficha online

A versão online da ficha de matrícula do cooperado possui um ID transparente compacto (código base36 no formato `{jurisdicao}{autoridade-cooperativa}{matricula}`), empregado em URLs curtas e representado na forma de QR-Code. Na ficha online são apresentados dados atualizados, e um link (aba) para o histórico de alterações. Por se tratar de uma interface padronizada de consulta e atualização, ela é também referida através do seu ícone.

![](src/assets/logoAtualizacoes2c-70px.png) Similar ao [padrão CrossMark](http://www.crossref.org/crossmark/) de artigos científicos, porém aplicado a documentos oficiais da coopertativa.

No protocolo dois modos de consulta (HTTP GET) são previstos:

* solicitação de HTML: pode ser feita pelas URLs `http://qr-c.org/{idBase36}`,  `http://qr-c.org/{idBase36}.htm` ou `http://qr-c.org/urn:{urnCanonica}.htm`. As duas primeiras redirecionarão para a terceira, que apresentará uma página com a interface geral. 

  * Alternativamente, para AJAX de *dialog boxes* ([exemplo](https://jqueryui.com/dialog/#modal-message)) oferece apenas fragmentos HTML em `http://qr-c.org/dialog/{idBase36}` (ou `http://qr-c.org/dialog/{idBase36}.htm`).

* solicitação de JSON: pode ser feita pelas URLs `http://qr-c.org/{idBase36}` (agente JSON consultando),  `http://qr-c.org/{idBase36}.json` (qualquer agente) ou `http://qr-c.org/urn:{urnCanonica}` (agente JSON ou indepentente ao usar `.json`). Em todas elas é  obtido o mesmo retorno  JSON contendo os dados em formato padronizado.

