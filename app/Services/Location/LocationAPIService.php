<?php

namespace App\Services\Location;

use Illuminate\Support\Facades\Http;

class LocationAPIService
{

    /**
     * Fetches locations using the PositionStack API.
     *
     * @author Growexx
     * @param string $apiAccessKey 
     * @param string $query
     * @return array|null
     */
    public function getLocations($apiAccessKey, $query)
    {
        $response = Http::get(env('POSITIONSTACK_URL'), [
            'access_key' => $apiAccessKey,
            'query' => $query,
        ]);

        if ($response->successful()) {
            return $response->json();
        } else {
            return null;
        }
    }
}