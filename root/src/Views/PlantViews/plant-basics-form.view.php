<?php 
/**
 *  @var string $formAction
 *  @var string $plantId 
 *  @var boolean $isUpdate
 *  @var BasicPlantData $basicPlantData
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Plant Basics</title>
</head>
<body>
    <h2>
        <?= $isUpdate ? 'Update' : 'Add' ?> Basic Details for the Power Plant
    </h2>

    <form action="<?= htmlspecialchars($formAction) ?>" method="POST">
        
        <div>
            <label for="capacity">Planned Capacity (MW):</label>
            <input
                type="number" 
                id="capacity" 
                name="capacity" 
                step="0.01" 
                value="<?= $isUpdate ? htmlspecialchars($basicPlantData->getCapacity() ?? '') : '' ?>"
            />
        </div> 

        <div>
            <label for="constructionDurationYears">Construction Duration (Years):</label>
            <input
                type="number" 
                id="constructionDurationYears" 
                name="constructionDurationYears"
                step="1" 
                value="<?= $isUpdate ? htmlspecialchars($basicPlantData->getConstructionDurationYears() ?? '') : '' ?>"
            />
        </div>

        <div>
            <label for="description">Description:</label>
            <textarea
                id="description" 
                name="description" 
                rows="4"
                placeholder="Enter general details about the power plant..."
            ><?= $isUpdate ? htmlspecialchars($basicPlantData->getDescription() ?? '') : '' ?></textarea>
        </div>

        <button type="submit"> 
            <?= $isUpdate ? 'Update Basics' : 'Save Basics' ?> 
        </button>
    </form>

    <footer>
        <a href="/power-plants/<?= htmlspecialchars($plantId); ?>/details"> 
            Back 
        </a>
        
        <a href="/power-plants/<?= htmlspecialchars($plantId); ?>/geological"> 
            Next 
        </a>
    </footer>
</body>
</html>