<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$temp = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'wp-readme-validator-smoke-' . bin2hex(random_bytes(4));

if (! mkdir($temp, 0777, true) && ! is_dir($temp)) {
    throw new RuntimeException('Could not create temporary smoke directory.');
}

try {
    [$helpExit, $helpOut, $helpErr] = runCliCommand($root, ['--help']);
    assertSame(0, $helpExit, 'help CLI exit code');
    assertContains('--plugin=<path>', $helpOut, 'help plugin option');
    assertContains('--readme=<path>', $helpOut, 'help readme option');
    assertContains('--json', $helpOut, 'help JSON option');
    assertContains('--help', $helpOut, 'help option');
    assertContains('0  Metadata is valid; warnings are allowed.', $helpOut, 'help exit code 0');
    assertContains('1  Validation errors were found.', $helpOut, 'help exit code 1');
    assertContains('2  Invalid arguments or unreadable input files.', $helpOut, 'help exit code 2');
    assertSame('', trim($helpErr), 'help CLI stderr');

    $plugin = $temp . DIRECTORY_SEPARATOR . 'example-plugin.php';
    $readme = $temp . DIRECTORY_SEPARATOR . 'readme.txt';

    file_put_contents(
        $plugin,
        "<?php\n/**\n * Plugin Name: Example Plugin\n * Version: 1.2.3\n * Requires at least: 6.5\n * Requires PHP: 8.1\n * License: GPL-2.0-or-later\n * Text Domain: example-plugin\n */\n"
    );

    file_put_contents(
        $readme,
        "=== Example Plugin ===\nContributors: example\nTags: example, metadata\nRequires at least: 6.5\nTested up to: 7.0\nRequires PHP: 8.1\nStable tag: 1.2.3\nLicense: GPL-2.0-or-later\n\n== Description ==\nExample.\n"
    );

    [$validExit, $validOut, $validErr] = runCli($root, $plugin, $readme, false);
    assertSame(0, $validExit, 'valid CLI exit code');
    assertContains('OK: plugin headers and readme.txt are consistent.', $validOut, 'valid CLI output');
    assertSame('', trim($validErr), 'valid CLI stderr');

    file_put_contents(
        $readme,
        str_replace('Stable tag: 1.2.3', 'Stable tag: 9.9.9', (string) file_get_contents($readme))
    );

    [$invalidExit, $invalidOut] = runCli($root, $plugin, $readme, false);
    assertSame(1, $invalidExit, 'invalid CLI exit code');
    assertContains('[ERROR] version.mismatch:', $invalidOut, 'invalid CLI output');

    [$jsonExit, $jsonOut] = runCli($root, $plugin, $readme, true);
    assertSame(1, $jsonExit, 'JSON CLI exit code');
    $decoded = json_decode($jsonOut, true, 512, JSON_THROW_ON_ERROR);
    $codes = array_column($decoded['issues'] ?? [], 'code');
    if (! in_array('version.mismatch', $codes, true)) {
        throw new RuntimeException('JSON output did not contain version.mismatch.');
    }

    fwrite(STDOUT, "CLI smoke test passed.\n");
} finally {
    removeTree($temp);
}

/** @return array{0:int,1:string,2:string} */
function runCli(string $root, string $plugin, string $readme, bool $json): array
{
    $arguments = [
        '--plugin=' . $plugin,
        '--readme=' . $readme,
    ];

    if ($json) {
        $arguments[] = '--json';
    }

    return runCliCommand($root, $arguments);
}

/** @param list<string> $arguments
 *  @return array{0:int,1:string,2:string}
 */
function runCliCommand(string $root, array $arguments): array
{
    $command = array_merge(
        [PHP_BINARY, $root . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'wp-readme-validator'],
        $arguments
    );

    $process = proc_open(
        array_map('strval', $command),
        [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ],
        $pipes,
        $root
    );

    if (! is_resource($process)) {
        throw new RuntimeException('Could not start CLI smoke process.');
    }

    fclose($pipes[0]);
    $stdout = (string) stream_get_contents($pipes[1]);
    $stderr = (string) stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    return [proc_close($process), $stdout, $stderr];
}

function assertSame(mixed $expected, mixed $actual, string $label): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(sprintf('%s mismatch. Expected %s, got %s.', $label, var_export($expected, true), var_export($actual, true)));
    }
}

function assertContains(string $needle, string $haystack, string $label): void
{
    if (! str_contains($haystack, $needle)) {
        throw new RuntimeException(sprintf('%s is missing expected text: %s', $label, $needle));
    }
}

function removeTree(string $path): void
{
    if (! file_exists($path)) {
        return;
    }

    if (is_file($path) || is_link($path)) {
        @unlink($path);
        return;
    }

    foreach (new DirectoryIterator($path) as $item) {
        if ($item->isDot()) {
            continue;
        }
        removeTree($item->getPathname());
    }

    @rmdir($path);
}
