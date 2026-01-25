<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MeteoService
{
    private HttpClientInterface $client;
    private string $apiKey;
    private CacheInterface $cache;

    public function __construct(HttpClientInterface $client, CacheInterface $cache, string $openWeatherKey)
    {
        $this->client = $client;
        $this->cache = $cache;
        $this->apiKey = $openWeatherKey;
    }

    public function getMeteoForCity(string $city): array
    {
        return $this->cache->get('meteo_' . strtolower($city), function (ItemInterface $item) use ($city) {

            $item->expiresAfter(3600);

            $response = $this->client->request(
                'GET',
                'https://api.openweathermap.org/data/2.5/weather',
                [
                    'query' => [
                        'q' => $city,
                        'appid' => $this->apiKey,
                        'units' => 'metric',
                        'lang' => 'fr'
                    ]
                ]
            );

            $data = $response->toArray();

            return [
                'ville' => $city,
                'temperature' => $data['main']['temp'] . '°C',
                'description' => $data['weather'][0]['description'],
                'humidite' => $data['main']['humidity'] . '%'
            ];
        });
    }

    /**
     * Supprime le cache météo pour une ville donnée (Admin)
     */
    public function clearCache(string $city): void
    {
        $cacheKey = 'meteo_' . strtolower($city);

        $this->cache->delete($cacheKey);
    }
}
