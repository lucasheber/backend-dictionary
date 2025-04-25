<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WordsApiService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.wordsapi.api_url');
        $this->apiKey = config('services.wordsapi.key');
    }

    public function getWordData(string $word)
    {
        $startTime = microtime(true);
        $cacheKey = "word_data_{$word}";

        if (cache()->has($cacheKey)) {
            $data = cache()->get($cacheKey);
            $duration = (microtime(true) - $startTime) * 1000;

            return [
                'data' => $data,
                'x-cache' => 'HIT',
                'x-response-time' => round($duration, 2) . 'ms',
            ];
        }

        $data = cache()->remember($cacheKey, 1440, function () use ($word) {
            $response = Http::withHeaders([
                'X-RapidAPI-Key' => $this->apiKey,
                'X-RapidAPI-Host' => 'wordsapiv1.p.rapidapi.com',
            ])->get("{$this->baseUrl}/words/{$word}");

            return $response->json();
        });

        $duration = (microtime(true) - $startTime) * 1000;

        return [
            'data' => $data,
            'x-cache' => 'MISS',
            'x-response-time' => round($duration, 2) . 'ms',
        ];
    }
}
