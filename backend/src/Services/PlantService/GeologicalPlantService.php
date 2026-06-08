<?php

require_once __DIR__ . '/../../Entities/SoilType.php';
require_once __DIR__ . '/../../Entities/WaterSourceType.php';
require_once __DIR__ . '/../../Dto/CreateDataResponseDTO.php'; 

class GeologicalPlantService { 
    private PlantRepositoryFacade $plantRepositoryFacade; 

    public function __construct(PlantRepositoryFacade $plantRepositoryFacade) { 
        $this->plantRepositoryFacade = $plantRepositoryFacade; 
    }

    public function findByPlantId(string $plantId) { 
        return $this->plantRepositoryFacade->getGeologicalDataByPlantId($plantId); 
    }

    private function runAutoGeolocation(float $lat, float $lon): array {
        $latNorm = round($lat, 6);
        $lonNorm = round($lon, 6);

        $result = [
            'soilType' => null,
            'waterSourceType' => WaterSourceType::FRESH_WATER,
            'seismicStability' => null,
            'floodRisk' => null,
            'groundwaterLevel' => null,
            'waterProximity' => null,
            'waterFlowRate' => null,
            'populationDensity' => null,
            'transportScore' => null,
        ];

        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => "Accept: application/json\r\nUser-Agent: NuclearProjectBackend/1.0\r\n",
                'ignore_errors' => true,
                'timeout' => 3
            ]
        ];
        $context = stream_context_create($opts);

        try {
            $geoQuery = http_build_query(['latitude' => $latNorm, 'longitude' => $lonNorm, 'localityLanguage' => 'ro']);
            $geoUrl = "https://api.bigdatacloud.net/data/reverse-geocode-client?{$geoQuery}";
            $geoResp = file_get_contents($geoUrl, false, $context);

            if ($geoResp !== false) {
                $geoData = json_decode($geoResp, true);
                if (isset($geoData['localityInfo']['administrative'])) {
                    $adminLevels = count($geoData['localityInfo']['administrative']);
                    $result['populationDensity'] = (float) min(100.0, $adminLevels * 20.0);
                    $result['transportScore'] = (float) min(10.0, $adminLevels * 2.0);
                }
            }
        } catch (Throwable $e) {
            error_log("[GEO SERVICE ERROR] BigDataCloud a crăpat: " . $e->getMessage());
        }

        try {
            $seismicQuery = http_build_query(['format' => 'geojson', 'starttime' => '2000-01-01', 'latitude' => $latNorm, 'longitude' => $lonNorm, 'maxradiuskm' => 120, 'minmagnitude' => 4.0]);
            $seismicUrl = "https://earthquake.usgs.gov/fdsnws/event/1/query?{$seismicQuery}";
            $seismicResp = file_get_contents($seismicUrl, false, $context);

            if ($seismicResp !== false) {
                $seismicData = json_decode($seismicResp, true);
                $totalEvents = $seismicData['metadata']['count'] ?? 0;
                $result['seismicStability'] = (float) round(max(0.0, 10.0 - ($totalEvents * 1.5)), 2);
            }
        } catch (Throwable $e) {
            error_log("[SEISMIC SERVICE ERROR] USGS a crăpat: " . $e->getMessage());
        }

        try {
            $floodQuery = http_build_query(['latitude' => $latNorm, 'longitude' => $lonNorm, 'daily' => 'river_discharge', 'forecast_days' => 1]);
            $floodUrl = "https://flood-api.open-meteo.com/v1/flood?{$floodQuery}";
            $floodResp = file_get_contents($floodUrl, false, $context);

            if ($floodResp !== false) {
                $floodData = json_decode($floodResp, true);
                $currentDischarge = $floodData['daily']['river_discharge'][0] ?? 0.0;
                $result['waterFlowRate'] = (float) round($currentDischarge, 2);

                if ($result['waterFlowRate'] > 0) {
                    $result['waterProximity'] = 1.2;
                    $result['floodRisk'] = (float) round(min(100.0, ($result['waterFlowRate'] / 150) * 100), 2);
                } else {
                    $result['waterProximity'] = 15.0;
                    $result['floodRisk'] = 0.0;
                }
            }
        } catch (Throwable $e) {
            error_log("[FLOOD SERVICE ERROR] Open-Meteo a crăpat: " . $e->getMessage());
        }

        try {
            $soilQuery = http_build_query([
                'latitude' => $latNorm, 
                'longitude' => $lonNorm, 
                'hourly' => 'soil_moisture_27_to_81cm', 
                'forecast_days' => 1
            ]);
            $soilUrl = "https://api.open-meteo.com/v1/forecast?{$soilQuery}";
            $soilResp = file_get_contents($soilUrl, false, $context);

            if ($soilResp !== false) {
                $soilData = json_decode($soilResp, true);
                $moisture = $soilData['hourly']['soil_moisture_27_to_81cm'][0] ?? 0.3;
                $result['groundwaterLevel'] = (float) round(max(0.2, (1.0 - $moisture) * 8), 2);
            }
        } catch (Throwable $e) {
            error_log("[GROUNDWATER SERVICE ERROR] Open-Meteo a crăpat: " . $e->getMessage());
        }

        try {
            $soilUrl = "https://rest.isric.org/soilgrids/v2.0/properties/query?lon={$lonNorm}&lat={$latNorm}&property=clay&property=sand&property=silt&depth=30-60cm&value=mean";
            $soilResp = file_get_contents($soilUrl, false, $context);

            if ($soilResp !== false) {
                $sData = json_decode($soilResp, true);
                $layers = $sData['properties']['layers'] ?? [];
                $clayPct = 0; $sandPct = 0; $siltPct = 0;

                foreach ($layers as $layer) {
                    $val = $layer['depths'][0]['values']['mean'] ?? 0;
                    $pct = $val / 10;
                    if ($layer['name'] === 'clay') $clayPct = $pct;
                    if ($layer['name'] === 'sand') $sandPct = $pct;
                    if ($layer['name'] === 'silt') $siltPct = $pct;
                }

                if (($clayPct + $sandPct + $siltPct) > 0) {
                    if ($clayPct > 40) $result['soilType'] = SoilType::STIFF_CLAY;
                    elseif ($sandPct > 50) $result['soilType'] = SoilType::LOOSE_SAND;
                    elseif ($siltPct > 40) $result['soilType'] = SoilType::SILT;
                    else $result['soilType'] = SoilType::LOAM;
                }
            }
        } catch (Throwable $e) {
            error_log("[SOILGRIDS SERVICE ERROR] SoilGrids a crăpat: " . $e->getMessage());
        }

        return $result;
    }

    public function save(array $data, string $plantId): CreateDataResponseDTO { 
        $existingData = $this->plantRepositoryFacade->getGeologicalDataByPlantId($plantId); 
        if ($existingData !== null) { 
            throw new Exception("Există deja date geologice pentru această centrală. Te rugăm să folosești metoda de UPDATE (PUT/PATCH).");
        }

        $country = (isset($data['country']) && $data['country'] !== '') ? $data['country'] : null;
        $latitude = (isset($data['latitude']) && $data['latitude'] !== '') ? (float) $data['latitude'] : null;
        $longitude = (isset($data['longitude']) && $data['longitude'] !== '') ? (float) $data['longitude'] : null;

        $soilType = (isset($data['soilType']) && $data['soilType'] !== '') 
            ? SoilType::from($data['soilType']) 
            : null;

        $waterSourceType = (isset($data['waterSourceType']) && $data['waterSourceType'] !== '') 
            ? WaterSourceType::from($data['waterSourceType']) 
            : null;

        $seismicStability = (isset($data['seismicStability']) && $data['seismicStability'] !== '') 
            ? (float) $data['seismicStability'] 
            : null;

        $floodRisk = (isset($data['floodRisk']) && $data['floodRisk'] !== '') 
            ? (float) $data['floodRisk'] 
            : null;

        $groundwaterLevel = (isset($data['groundwaterLevel']) && $data['groundwaterLevel'] !== '') 
            ? (float) $data['groundwaterLevel'] 
            : null;

        $waterProximity = (isset($data['waterProximity']) && $data['waterProximity'] !== '') 
            ? (float) $data['waterProximity'] 
            : null;

        $waterFlowRate = (isset($data['waterFlowRate']) && $data['waterFlowRate'] !== '') 
            ? (float) $data['waterFlowRate'] 
            : null;

        $populationDensity = (isset($data['populationDensity']) && $data['populationDensity'] !== '') 
            ? (float) $data['populationDensity'] 
            : null;

        $transportInfrastructureScore = (isset($data['transportInfrastructureScore']) && $data['transportInfrastructureScore'] !== '') 
            ? (float) $data['transportInfrastructureScore'] 
            : null;

        $geologicalRiskScore = (isset($data['geologicalRiskScore']) && $data['geologicalRiskScore'] !== '') 
            ? (float) $data['geologicalRiskScore'] 
            : null;

        if ($latitude !== null && $longitude !== null) {
            $autoResult = $this->runAutoGeolocation($latitude, $longitude);

            if ($soilType === null) $soilType = $autoResult['soilType'];
            if ($seismicStability === null) $seismicStability = $autoResult['seismicStability'];
            if ($floodRisk === null) $floodRisk = $autoResult['floodRisk'];
            if ($groundwaterLevel === null) $groundwaterLevel = $autoResult['groundwaterLevel'];
            if ($waterProximity === null) $waterProximity = $autoResult['waterProximity'];
            if ($waterFlowRate === null) $waterFlowRate = $autoResult['waterFlowRate'];
            if ($populationDensity === null) $populationDensity = $autoResult['populationDensity'];
            if ($transportInfrastructureScore === null) $transportInfrastructureScore = $autoResult['transportScore'];
            if ($waterSourceType === null) $waterSourceType = $autoResult['waterSourceType'];
        }

        $geologicalPlantData = new GeologicalPlantData(
            $plantId, 
            null, 
            $country,
            $latitude,
            $longitude,
            $soilType, 
            $waterSourceType,
            $seismicStability,
            $floodRisk,
            $groundwaterLevel, 
            $waterProximity,
            $waterFlowRate,
            $populationDensity,
            $transportInfrastructureScore,
            $geologicalRiskScore
        ); 

        $this->plantRepositoryFacade->saveGeologicalData($geologicalPlantData); 
        return new CreateDataResponseDTO($geologicalPlantData->getId()); 
    }

    public function update(array $data, string $plantId): void { 
        $currentData = $this->plantRepositoryFacade->getGeologicalDataByPlantId($plantId); 
        if ($currentData === null) {
            throw new Exception("Nu s-au găsit date geologice existente pentru a efectua actualizarea.");
        }
        
        $country = (isset($data['country']) && $data['country'] !== '') ? $data['country'] : $currentData->getCountry();
        $latitude = (isset($data['latitude']) && $data['latitude'] !== '') ? (float) $data['latitude'] : $currentData->getLatitude();
        $longitude = (isset($data['longitude']) && $data['longitude'] !== '') ? (float) $data['longitude'] : $currentData->getLongitude();

        $soilTypeRaw = $data['soilType'] ?? '';
        $soilType = ($soilTypeRaw !== '') ? SoilType::from($soilTypeRaw) : null;

        $waterSourceTypeRaw = $data['waterSourceType'] ?? '';
        $waterSourceType = ($waterSourceTypeRaw !== '') ? WaterSourceType::from($waterSourceTypeRaw) : null;

        $seismicStability = (isset($data['seismicStability']) && $data['seismicStability'] !== '') ? (float)$data['seismicStability'] : null;
        $floodRisk = (isset($data['floodRisk']) && $data['floodRisk'] !== '') ? (float)$data['floodRisk'] : null;
        $groundwaterLevel = (isset($data['groundwaterLevel']) && $data['groundwaterLevel'] !== '') ? (float)$data['groundwaterLevel'] : null;
        $waterProximity = (isset($data['waterProximity']) && $data['waterProximity'] !== '') ? (float)$data['waterProximity'] : null;
        $waterFlowRate = (isset($data['waterFlowRate']) && $data['waterFlowRate'] !== '') ? (float)$data['waterFlowRate'] : null;
        $populationDensity = (isset($data['populationDensity']) && $data['populationDensity'] !== '') ? (float)$data['populationDensity'] : null;
        $transportInfrastructureScore = (isset($data['transportInfrastructureScore']) && $data['transportInfrastructureScore'] !== '') ? (float)$data['transportInfrastructureScore'] : null;
        $geologicalRiskScore = (isset($data['geologicalRiskScore']) && $data['geologicalRiskScore'] !== '') ? (float)$data['geologicalRiskScore'] : null;

        $geologicalPlantData = new GeologicalPlantData(
            $plantId, 
            $currentData->getId(), 
            $country,
            $latitude,
            $longitude,
            $soilType, 
            $waterSourceType,
            $seismicStability, 
            $floodRisk,
            $groundwaterLevel, 
            $waterProximity, 
            $waterFlowRate,
            $populationDensity,
            $transportInfrastructureScore,
            $geologicalRiskScore
        ); 

        $this->plantRepositoryFacade->updateGeologicalData($geologicalPlantData); 
    }
}
