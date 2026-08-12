# WP Plugin Readme Validator

CLI e GitHub Action sem dependências de runtime para encontrar inconsistências entre o cabeçalho principal de um plugin WordPress e seu `readme.txt`.

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

Enquanto a primeira release estável ainda não foi publicada, use `@main` para avaliação:

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
      - uses: asllanmaciel/wp-plugin-readme-validator@main
        with:
          plugin-file: meu-plugin.php
          readme-file: readme.txt
```

A primeira release estável está planejada como `v1.0.0`. Depois dela, a documentação passará a recomendar o alias major `@v1`. Veja [`docs/RELEASING.md`](docs/RELEASING.md).

## Validações atuais

- Cabeçalhos importantes do plugin.
- Cabeçalhos importantes do `readme.txt`.
- `Version` versus `Stable tag`.
- `Requires PHP` entre os dois arquivos, quando também declarado no `readme.txt`.
- `Requires at least` entre os dois arquivos, quando também declarado no `readme.txt`.
- Limite recomendado de cinco tags.
- Formato básico dos campos de versão.

Desde o WordPress 5.8, o diretório lê `Requires PHP` e `Requires at least` do arquivo PHP principal do plugin. Por isso, o validador não exige mais que esses campos sejam duplicados no `readme.txt`; se estiverem presentes nos dois arquivos, eles ainda precisam ser consistentes.

## Desenvolvimento

```bash
composer install
composer check
```

A matriz de compatibilidade cobre PHP 8.1, 8.2, 8.3 e 8.4 quando o workflow manual é executado. O loop normal de desenvolvimento prioriza `composer check` local para controlar consumo de CI.

## Filosofia

O projeto valida somente regras determinísticas e úteis. Ele não tenta substituir a validação oficial do diretório WordPress.org nem interpretar o código do plugin.

## Contribuindo

Issues e pull requests são bem-vindos. Leia [CONTRIBUTING.md](CONTRIBUTING.md) e escolha uma tarefa marcada como [`good first issue`](https://github.com/asllanmaciel/wp-plugin-readme-validator/issues?q=is%3Aissue%20state%3Aopen%20label%3A%22good%20first%20issue%22).

Vulnerabilidades devem ser reportadas de forma privada conforme [SECURITY.md](SECURITY.md).

- [Changelog](CHANGELOG.md)
- [Processo de release](docs/RELEASING.md)

## Licença

MIT.
