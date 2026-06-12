<?php

require_once __DIR__ . '/../../Entities/SoilType.php';
require_once __DIR__ . '/../../Entities/WaterSourceType.php';
require_once __DIR__ . '/../../Dto/CreateDataResponseDTO.php';
require_once __DIR__ . '/../../Dto/GeologicalPlantDataDTO.php';
require_once __DIR__ . '/../../Dto/GeoLocationPreviewDTO.php';

class GeologicalPlantService { 
    private PlantRepositoryFacade $plantRepositoryFacade; 

    public function __construct(PlantRepositoryFacade $plantRepositoryFacade) { 
        $this->plantRepositoryFacade = $plantRepositoryFacade; 
    }

    public function findByPlantId(string $plantId): ?GeologicalPlantData { 
        return $this->plantRepositoryFacade->getGeologicalDataByPlantId($plantId); 
    }

    public function getGeologicalData(string $plantId): ?GeologicalPlantDataDTO {
        $entity = $this->findByPlantId($plantId);
        if (!$entity) return null;
        return GeologicalPlantDataDTO::fromEntity($entity);
    }

    public function runAutoGeolocation(float $lat, float $lon): GeoLocationPreviewDTO {
        $latNorm = round($lat, 6);
        $lonNorm = round($lon, 6);

        $result = [
            'country' => null,
            'soilType' => null,
            'waterSourceType' => WaterSourceType::FRESH_WATER,
            'seismicStability' => null,
            'floodRisk' => null,
            'groundwaterLevel' => null,
            'waterProximity' => null,
            'waterFlowRate' => null,
            'populationDensity' => null,
            'transportInfrastructureScore' => null,
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
                $result['country'] = $geoData['countryName'] ?? null;
                if (isset($geoData['localityInfo']['administrative'])) {
                    $adminLevels = count($geoData['localityInfo']['administrative']);
                    $result['populationDensity'] = (float) min(1000.0, $adminLevels * 120.0);
                    $result['transportInfrastructureScore'] = (float) round(min(10.0, $adminLevels * 2.0), 2);
                }
            }
        } catch (Throwable $e) {
            LogService::instance()->error("[GEO SERVICE ERROR] BigDataCloud a crăpat: " . $e->getMessage());
        }

        try {
            $seismicQuery = http_build_query(['format' => 'geojson', 'starttime' => '2000-01-01', 'latitude' => $latNorm, 'longitude' => $lonNorm, 'maxradiuskm' => 120, 'minmagnitude' => 4.0]);
            $seismicUrl = "https://earthquake.usgs.gov/fdsnws/event/1/query?{$seismicQuery}";
            $seismicResp = file_get_contents($seismicUrl, false, $context);

            if ($seismicResp !== false) {
                $seismicData = json_decode($seismicResp, true);
                $totalEvents = $seismicData['metadata']['count'] ?? 0;
                $result['seismicStability'] = (float) round(max(0.0, min(10.0, 10.0 - ($totalEvents * 1.5))), 2);
            }
        } catch (Throwable $e) {
            LogService::instance()->error("[SEISMIC SERVICE ERROR] USGS a crăpat: " . $e->getMessage());
        }

        try {
            $floodQuery = http_build_query(['latitude' => $latNorm, 'longitude' => $lonNorm, 'daily' => 'river_discharge', 'forecast_days' => 1]);
            $floodUrl = "https://flood-api.open-meteo.com/v1/flood?{$floodQuery}";
            $floodResp = file_get_contents($floodUrl, false, $context);

            if ($floodResp !== false) {
                $floodData = json_decode($floodResp, true);
                $currentDischarge = $floodData['daily']['river_discharge'][0] ?? null;
                if ($currentDischarge !== null) {
                    $result['waterFlowRate'] = (float) round($currentDischarge, 2);
                    if ($result['waterFlowRate'] > 0) {
                        $result['waterProximity'] = 1.2;
                        $result['floodRisk'] = (float) round(min(10.0, ($result['waterFlowRate'] / 15)), 2);
                    } else {
                        $result['waterProximity'] = 15.0;
                        $result['floodRisk'] = 0.0;
                    }
                }
            }
        } catch (Throwable $e) {
            LogService::instance()->error("[FLOOD SERVICE ERROR] Open-Meteo a crăpat: " . $e->getMessage());
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
            LogService::instance()->error("[GROUNDWATER SERVICE ERROR] Open-Meteo a crăpat: " . $e->getMessage());
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
            LogService::instance()->error("[SOILGRIDS SERVICE ERROR] SoilGrids a crăpat: " . $e->getMessage());
        }

        return new GeoLocationPreviewDTO(
            country: $result['country'],
            soilType: self::stringifyEnum($result['soilType']),
            waterSourceType: self::stringifyEnum($result['waterSourceType']),
            seismicStability: $result['seismicStability'],
            floodRisk: $result['floodRisk'],
            groundwaterLevel: $result['groundwaterLevel'],
            waterProximity: $result['waterProximity'],
            waterFlowRate: $result['waterFlowRate'],
            populationDensity: $result['populationDensity'],
            transportInfrastructureScore: $result['transportInfrastructureScore'],
        );
    }

    private static function stringifyEnum(mixed $value): ?string {
        if ($value === null || is_string($value)) {
            return $value;
        }
        if (is_object($value) && enum_exists($value::class)) {
            return $value instanceof \BackedEnum ? $value->value : $value->name;
        }
        return (string) $value;
    }

    public function save(array $data, string $plantId): CreateDataResponseDTO { 
        $latitude = null;
        if (isset($data['latitude']) && $data['latitude'] !== '') {
            $latitude = (float) $data['latitude'];
            if ($latitude < -90 || $latitude > 90) {
                throw new Exception("Latitudinea trebuie să fie între -90 și 90.");
            }
        }

        $longitude = null;
        if (isset($data['longitude']) && $data['longitude'] !== '') {
            $longitude = (float) $data['longitude'];
            if ($longitude < -180 || $longitude > 180) {
                throw new Exception("Longitudinea trebuie să fie între -180 și 180.");
            }
        }

        $soilType = null;
        if (isset($data['soilType']) && $data['soilType'] !== '') {
            $soilType = SoilType::tryFrom($data['soilType']);
            if (!$soilType) throw new Exception("Tip sol invalid.");
        }

        $waterSourceType = null;
        if (isset($data['waterSourceType']) && $data['waterSourceType'] !== '') {
            $waterSourceType = WaterSourceType::tryFrom($data['waterSourceType']);
            if (!$waterSourceType) throw new Exception("Tip sursă apă invalid.");
        }

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

        $country = (isset($data['country']) && $data['country'] !== '') ? $data['country'] : null;

        if ($latitude !== null && $longitude !== null) {
            $autoResult = $this->runAutoGeolocation($latitude, $longitude);

            if ($soilType === null && $autoResult->soilType !== null) {
                $soilType = SoilType::tryFrom($autoResult->soilType);
            }
            if ($waterSourceType === null && $autoResult->waterSourceType !== null) {
                $waterSourceType = WaterSourceType::tryFrom($autoResult->waterSourceType);
            }
            if ($seismicStability === null) $seismicStability = $autoResult->seismicStability;
            if ($floodRisk === null) $floodRisk = $autoResult->floodRisk;
            if ($groundwaterLevel === null) $groundwaterLevel = $autoResult->groundwaterLevel;
            if ($waterProximity === null) $waterProximity = $autoResult->waterProximity;
            if ($waterFlowRate === null) $waterFlowRate = $autoResult->waterFlowRate;
            if ($populationDensity === null) $populationDensity = $autoResult->populationDensity;
            if ($transportInfrastructureScore === null) $transportInfrastructureScore = $autoResult->transportInfrastructureScore;
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

        $inserted = $this->plantRepositoryFacade->saveGeologicalData($geologicalPlantData); 
        if (!$inserted) {
            throw new Exception("Există deja date geologice pentru această centrală.");
        }
        return new CreateDataResponseDTO($geologicalPlantData->getId()); 
    }

    public function update(array $data, string $plantId): void { 
        $geologicalData = $this->plantRepositoryFacade->getGeologicalDataByPlantId($plantId); 
        if ($geologicalData === null) {
            throw new Exception("Nu s-au găsit date geologice existente pentru actualizare.");
        }

        if (isset($data['country'])) {
            $geologicalData->setCountry($data['country'] !== '' ? $data['country'] : null);
        }

        if (isset($data['latitude'])) {
            $lat = $data['latitude'] !== '' ? (float) $data['latitude'] : null;
            if ($lat !== null && ($lat < -90 || $lat > 90)) {
                throw new Exception("Latitudinea trebuie să fie între -90 și 90.");
            }
            $geologicalData->setLatitude($lat);
        }

        if (isset($data['longitude'])) {
            $lon = $data['longitude'] !== '' ? (float) $data['longitude'] : null;
            if ($lon !== null && ($lon < -180 || $lon > 180)) {
                throw new Exception("Longitudinea trebuie să fie între -180 și 180.");
            }
            $geologicalData->setLongitude($lon);
        }

        if (isset($data['soilType'])) {
            if ($data['soilType'] !== '') {
                $soilType = SoilType::tryFrom($data['soilType']);
                if (!$soilType) throw new Exception("Tip sol invalid.");
                $geologicalData->setSoilType($soilType);
            } else {
                $geologicalData->setSoilType(null);
            }
        }

        if (isset($data['waterSourceType'])) {
            if ($data['waterSourceType'] !== '') {
                $waterSourceType = WaterSourceType::tryFrom($data['waterSourceType']);
                if (!$waterSourceType) throw new Exception("Tip sursă apă invalid.");
                $geologicalData->setWaterSourceType($waterSourceType);
            } else {
                $geologicalData->setWaterSourceType(null);
            }
        }

        if (isset($data['seismicStability'])) {
            $geologicalData->setSeismicStability($data['seismicStability'] !== '' ? (float) $data['seismicStability'] : null);
        }

        if (isset($data['floodRisk'])) {
            $geologicalData->setFloodRisk($data['floodRisk'] !== '' ? (float) $data['floodRisk'] : null);
        }

        if (isset($data['groundwaterLevel'])) {
            $geologicalData->setGroundwaterLevel($data['groundwaterLevel'] !== '' ? (float) $data['groundwaterLevel'] : null);
        }

        if (isset($data['waterProximity'])) {
            $geologicalData->setWaterProximity($data['waterProximity'] !== '' ? (float) $data['waterProximity'] : null);
        }

        if (isset($data['waterFlowRate'])) {
            $geologicalData->setWaterFlowRate($data['waterFlowRate'] !== '' ? (float) $data['waterFlowRate'] : null);
        }

        if (isset($data['populationDensity'])) {
            $geologicalData->setPopulationDensity($data['populationDensity'] !== '' ? (float) $data['populationDensity'] : null);
        }

        if (isset($data['transportInfrastructureScore'])) {
            $geologicalData->setTransportInfrastructureScore($data['transportInfrastructureScore'] !== '' ? (float) $data['transportInfrastructureScore'] : null);
        }

        if (isset($data['geologicalRiskScore'])) {
            $geologicalData->setGeologicalRiskScore($data['geologicalRiskScore'] !== '' ? (float) $data['geologicalRiskScore'] : null);
        }

        $this->plantRepositoryFacade->updateGeologicalData($geologicalData); 
    }
}
