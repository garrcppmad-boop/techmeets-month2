<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@auto' => true,
        '@auto:risky' => true,
    ])
    // チェック対象は自分たちが書くソースコードだけに絞る
    // （storage/ の Blade キャッシュや bootstrap/cache などの自動生成ファイルは対象外）
    ->setFinder(
        (new Finder())
            ->in([
                __DIR__.'/app',
                __DIR__.'/config',
                __DIR__.'/database',
                __DIR__.'/routes',
                __DIR__.'/tests',
            ])
    )
;
