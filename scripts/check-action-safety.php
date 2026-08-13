<?php

declare(strict_types=1);

$actionPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'action.yml';
$contents = file_get_contents($actionPath);

if ($contents === false) {
    fwrite(STDERR, "Could not read action.yml.\n");
    exit(1);
}

if (preg_match('/^\s*run:\s*.*\$\{\{\s*(?:inputs\.|github\.action_path)/m', $contents) === 1) {
    fwrite(STDERR, "Unsafe GitHub expression interpolation found directly in a shell run step.\n");
    exit(1);
}

$required = [
    'WP_README_VALIDATOR_ACTION_PATH: ${{ github.action_path }}',
    'WP_README_VALIDATOR_PLUGIN_FILE: ${{ inputs.plugin-file }}',
    'WP_README_VALIDATOR_README_FILE: ${{ inputs.readme-file }}',
    '$WP_README_VALIDATOR_ACTION_PATH/bin/wp-readme-validator',
    '$WP_README_VALIDATOR_PLUGIN_FILE',
    '$WP_README_VALIDATOR_README_FILE',
];

foreach ($required as $needle) {
    if (!str_contains($contents, $needle)) {
        fwrite(STDERR, "action.yml is missing expected safe input handling: {$needle}\n");
        exit(1);
    }
}

fwrite(STDOUT, "Composite action input handling looks safe.\n");
