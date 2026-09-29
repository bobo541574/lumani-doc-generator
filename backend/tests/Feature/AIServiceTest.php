<?php

use App\Services\AIService;

test('ai service throws when no driver is configured', function () {
    config(['ai.driver' => 'none']);

    (new AIService())->generate('hello');
})->throws(RuntimeException::class, 'AI driver not configured');

test('ai service exposes a configured driver', function () {
    config(['ai.driver' => 'openai']);
    config(['ai.openai.api_key' => 'test-key']);

    expect(config('ai.driver'))->toBe('openai')
        ->and(config('ai.openai.api_key'))->toBe('test-key');
});
