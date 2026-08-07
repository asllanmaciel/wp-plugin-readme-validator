# WP Plugin Readme Validator

CLI e GitHub Action sem dependências de runtime para encontrar inconsistências entre o cabeçalho principal de um plugin WordPress e seu `readme.txt`.

## Por que usar

Um plugin pode funcionar normalmente e ainda ser rejeitado ou publicado com metadados incorretos porque:

- `Stable tag` não corresponde à versão do plugin;
- requisitos de WordPress ou PHP divergem entre os arquivos;
- cabeçalhos obrigatórios estão ausentes;
- o `readme.txt` usa mais tags do que o diretório considera;
- versões têm formatos inesperados.

O validador transforma esses problemas em uma saída legível, JSON ou falha de CI.

## Uso pela linha de comando

Não é necessário instalar dependências:

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

## Validações atuais

- Cabeçalhos obrigatórios do plugin.
- Cabeçalhos obrigatórios do `readme.txt`.
- `Version` versus `Stable tag`.
- `Requires PHP` entre os dois arquivos.
- `Requires at least` entre os dois arquivos.
- Limite recomendado de cinco tags.
- Formato básico dos campos de versão.

## Desenvolvimento

```bash
composer install
composer check
```

A matriz de CI cobre PHP 8.1, 8.2, 8.3 e 8.4.

## Filosofia

O projeto valida somente regras determinísticas e úteis. Ele não tenta substituir a validação oficial do diretório WordPress.org nem interpretar o código do plugin.

## Licença

MIT.

