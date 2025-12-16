<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
    ])
    ->withSkip([
        __DIR__.'/app/*/**.blade.php',
    ])
    ->withPhpSets()
    ->withSkip([
        PostIncDecToPreIncDecRector::class,
        CatchExceptionNameMatchingTypeRector::class,
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        rectorPreset: true,
    );
