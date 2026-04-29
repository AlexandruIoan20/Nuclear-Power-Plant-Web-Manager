<?php 
/**
 *  @var string $formAction
 *  @var string $plantId 
 *  @var boolean $isUpdate
 *  @var GeologicalPlantData $geologicalPlantData
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isUpdate ? 'Update' : 'Add' ?> Geological Data</title>
</head>
<body>
    <h2>
        <?= $isUpdate ? 'Update' : 'Add' ?> Geological Data for the Power Plant
    </h2>

    <form action="<?= htmlspecialchars($formAction) ?>" method="POST">
        
        <div>
            <label for="soil_type">Soil Type:</label>
            <select name="soil_type" id="soil_type">
                <option value="">-- Select Soil Type --</option>
                <?php foreach (SoilType::cases() as $type): ?>
                    <option 
                        value="<?= $type->value ?>" 
                        <?= ($isUpdate && $geologicalPlantData->getSoilType() === $type) ? 'selected' : '' ?>
                    >
                        <?= $type->value ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="seismic_stability">Seismic Stability (0.00 - 1.00):</label>
            <input
                type="number" 
                id="seismic_stability" 
                name="seismic_stability" 
                step="0.01" 
                min="0"
                max="1"
                value="<?= $isUpdate ? htmlspecialchars($geologicalPlantData->getSeismicStability() ?? '') : '' ?>"
            />
        </div>

        <div>
            <label for="groundwater_level">Groundwater Level (meters):</label>
            <input
                type="number" 
                id="groundwater_level" 
                name="groundwater_level" 
                step="0.1" 
                value="<?= $isUpdate ? htmlspecialchars($geologicalPlantData->getGroundwaterLevel() ?? '') : '' ?>"
            />
        </div>

        <div>
            <label for="water_proximity">Water Proximity (km):</label>
            <input
                type="number" 
                id="water_proximity" 
                name="water_proximity" 
                step="0.1" 
                value="<?= $isUpdate ? htmlspecialchars($geologicalPlantData->getWaterProximity() ?? '') : '' ?>"
            />
        </div>

        <div>
            <label for="geological_risk_score">Geological Risk Score (0 - 100):</label>
            <input
                type="number" 
                id="geological_risk_score" 
                name="geological_risk_score" 
                step="1" 
                min="0"
                max="100"
                value="<?= $isUpdate ? htmlspecialchars($geologicalPlantData->getGeologicalRiskScore() ?? '') : '' ?>"
            />
        </div>

        <button type="submit"> 
            <?= $isUpdate ? 'Update Geological Data' : 'Save Geological Data' ?> 
        </button>
    </form>

    <footer>
        <a href="/power-plants/<?= htmlspecialchars($plantId); ?>/basics"> 
            Back 
        </a>
        
        <a href="/power-plants/<?= htmlspecialchars($plantId); ?>/technical"> 
            Next 
        </a>
    </footer>
</body>
</html>