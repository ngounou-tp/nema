<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

test('lang/fr.json and lang/en.json have exactly the same keys', function () {
    $french = File::json(lang_path('fr.json'));
    $english = File::json(lang_path('en.json'));

    expect(array_keys(array_diff_key($french, $english)))->toBe([], 'Keys missing from lang/en.json')
        ->and(array_keys(array_diff_key($english, $french)))->toBe([], 'Keys missing from lang/fr.json')
        ->and(array_filter($french, fn (string $value): bool => trim($value) === ''))->toBe([])
        ->and(array_filter($english, fn (string $value): bool => trim($value) === ''))->toBe([]);
});

test('each PHP translation file has the same keys in French and English', function (string $group) {
    $french = array_keys(Arr::dot(require lang_path("fr/{$group}.php")));
    $english = array_keys(Arr::dot(require lang_path("en/{$group}.php")));

    expect(array_values(array_diff($french, $english)))->toBe([], "Keys missing from lang/en/{$group}.php")
        ->and(array_values(array_diff($english, $french)))->toBe([], "Keys missing from lang/fr/{$group}.php");
})->with(fn (): array => array_map(
    fn (string $path): string => pathinfo($path, PATHINFO_FILENAME),
    glob(__DIR__.'/../../../lang/fr/*.php'),
));

test('no .vue file shows text outside the translations', function () {
    $process = new Process(['node', 'tests/Frontend/check-hardcoded-strings.mjs'], base_path());
    $process->run();

    expect($process->getExitCode())->toBe(0, $process->getErrorOutput());
})->skip(fn (): bool => (new ExecutableFinder)->find('node') === null, 'Node.js is not installed.');
