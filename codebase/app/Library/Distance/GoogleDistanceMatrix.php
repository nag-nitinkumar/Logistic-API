<?php

namespace App\Library\Distance;

class GoogleDistanceMatrix implements DistanceMatrixInterface
{
    /**
     * {@inheritedDocs}
     */
    public function getDistance($origin, $destination)
    {
        $googleApiKey = env('MAP_KEY');

        $queryString =  env('MAP_API_URL') . "?units=imperial&origins=" . implode(",", $origin). "&destinations=" . implode(",", $destination) . "&key=" . $googleApiKey;

        try {
            $data = file_get_contents($queryString);

            $data = json_decode($data);
            if (!empty($data) && isset($data->status)) {
                //if response received as OK then pas distance value else error
                if ('OK' == trim($data->status)) {
                    $dataElements = $data->rows[0]->elements[0];
                    if (isset($dataElements->distance->value)) {
                        return (int) $dataElements->distance->value;
                    } else {
                        return "GOOGLE_API.NO_RESPONSE";
                    }
                } else {
                    return "GOOGLE_API.".$data->status;
                }
            } else {
                return "GOOGLE_API.NO_RESPONSE";
            }
        } catch (\Exception $e) {
            return (isset($dataElements->status)) ? $dataElements->status : 'GOOGLE_API.NO_RESPONSE';
        }
    }
}
