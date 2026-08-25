<?php

declare(strict_types=1);

it('loads the project bootstrap', function (): void {
    expect(file_exists(__DIR__ . '/../../bootstrap.php'))->toBeTrue();
});
