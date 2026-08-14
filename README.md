# WP Plugin Readme Validator

CLI e GitHub Action sem dependências de runtime para encontrar inconsistências entre o cabeçalho principal de um plugin WordPress e seu `readme.txt`.

## Status de release

**Stable release: [v1.0.0](https://github.com/asllanmaciel/wp-plugin-readme-validator/releases/tag/v1.0.0)**

O alias major `@v1` foi realinhado ao mesmo commit da release estável e passou por consumer ref + Action contract proof. Para workflows normais, use `@v1`; para máxima reprodutibilidade, fixe `@v1.0.0` ou um commit SHA.

## Por que usar

Um plugin pode funcionar normalmente e ainda ser rejeitado ou publicado com metadados incorretos porque:

- `Stable tag` não corresponde à versão do plugin;
- requisitos de WordPress ou PHP divergem quando aparecem nos dois arquivos;
- cabeçalhos importantes estão ausentes;
- o `readme.txt` usa mais tags do que o diretório considera;
- versões têm formatos inesperados.

O validador transforma esses problemas em uma saída legível, JSON ou falha de CI.

## Uso pela linha de comando

Não é necessário instalar dependências de runtime:

```bash
php bin/wp-readme-validator \
  --plugin=meu-plugin.php \
  --readme=readme.txt
```

Para integrar com outras ferramentas:

```bash
php bin/wp-readme-validator \
  --plugin=meu-plugin.php \
  --readme=readme.txt \
  --json
```

Exit codes:

- `0`: metadados válidos, com ou sem warnings;
- `1`: erros de validação;
- `2`: argumentos ou arquivos inválidos.

## Uso como GitHub Action

Use o alias major estável `@v1`:

```yaml
name: Validate plugin metadata

on: [push, pull_request]

jobs:
  validate:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
      - uses: asllanmaciel/wp-plugin-readme-validator@v1
        with:
          plugin-file: meu-plugin.php
          readme-file: readme.txt
```

Para máxima reprodutibilidade, você também pode fixar `@v1.0.0` ou um commit SHA. Veja [`docs/RELEASING.md`](docs/RELEASING.md).

## Validações atuais

- Cabeçalhos importantes do plugin.
- Cabeçalhos importantes do `readme.txt`.
- `Version` versus `Stable tag`.
- `Requires PHP` entre os dois arquivos, quando também declarado no `readme.txt`.
- `Requires at least` entre os dois arquivos, quando também declarado no `readme.txt`.
- Limite recomendado de cinco tags.
- Formato básico dos campos de versão.

Desde o WordPress 5.8, o diretório lê `Requires PHP` e `Requires at least` do arquivo PHP principal do plugin. Por isso, o validador não exige mais que esses campos sejam duplicados no `readme.txt`; se estiverem presentes nos dois arquivos, eles ainda precisam ser consistentes.

## Compatibilidade do parser

A leitura do cabeçalho principal procura metadados somente nos primeiros **8192 bytes físicos** do arquivo, acompanhando o limite prático usado pelo WordPress para plugin headers. O corte acontece antes da remoção de BOM e da normalização de line endings, preservando a fronteira real do arquivo.

O parser também possui regressões para:

- arquivos com LF, CRLF e lone CR;
- UTF-8 BOM;
- headers duplicados, preservando a primeira ocorrência;
- metadata fora da janela inicial de 8 KB;
- CRLF/BOM sem deslocar headers através da fronteira raw de 8192 bytes.

Essas garantias fazem parte do contrato validado da release `v1.0.0`.

## Desenvolvimento

```bash
composer install
composer check
```

`composer check` executa análise estática, PHPUnit, o guard de segurança da Action e um smoke do **CLI real**. O smoke valida um caso válido, um mismatch que deve falhar e a saída JSON através de `bin/wp-readme-validator`.

A compatibilidade da release `v1.0.0` foi validada localmente em PHP 8.1, 8.2, 8.3 e 8.4. O workflow do repositório permanece manual-only e o loop normal de desenvolvimento prioriza `composer check` local para controlar consumo de CI.

O relatório completo está em [`RELEASE_VALIDATION_REPORT.md`](RELEASE_VALIDATION_REPORT.md).

## Ecossistema WordPress relacionado

O Validator complementa outros projetos WordPress mantidos pelo mesmo ecossistema:

- **[WP24H Plugin Boilerplate](https://github.com/WP24Horas/wp24h-plugin-boilerplate)** — base modular para iniciar plugins profissionais com testes e análise estática.
- **[WP24H MD Importer](https://github.com/asllanmaciel/wp24h-md-importer)** — plugin funcional que serve como caso real de metadados, distribuição e automação WordPress.

Uma forma prática de usar os projetos juntos é iniciar a estrutura no Boilerplate, desenvolver o plugin e executar este Validator como um dos checks antes de empacotar a release.

## Filosofia

O projeto valida somente regras determinísticas e úteis. Ele não tenta substituir a validação oficial do diretório WordPress.org nem interpretar o código do plugin.

## Contribuindo

Issues e pull requests são bem-vindos. Leia [CONTRIBUTING.md](CONTRIBUTING.md) e escolha uma tarefa marcada como [`good first issue`](https://github.com/asllanmaciel/wp-plugin-readme-validator/issues?q=is%3Aissue%20state%3Aopen%20label%3A%22good%20first%20issue%22).

Vulnerabilidades devem ser reportadas de forma privada conforme [SECURITY.md](SECURITY.md).

- [Changelog](CHANGELOG.md)
- [Processo de release](docs/RELEASING.md)

## Licença

MIT.
