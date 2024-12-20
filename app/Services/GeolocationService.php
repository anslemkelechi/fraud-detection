<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeolocationService
{
    public function getLocationFromIp($ip)
    {
        // Construct the API endpoint
        $url = env("GEOLOCATION_URL", "http://ip-api.com/json");

        // Make the request to the ipstack API
        $response = Http::get($url . "/{$ip}");

        // Check if the response is successful
        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    public function getPublicIp()
    {
        // Construct the API endpoint
        $url = env("GEOLOCATION_URL", "http://ip-api.com/json");

        // Make the request to the ipstack API
        $response = Http::get($url);

        if ($response->ok()) {
            $data = $response->json();
            return $data['query'] ?? null; //Contails Public IP
        }

        return null;
    }
}
