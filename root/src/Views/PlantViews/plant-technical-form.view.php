<?php 
/**
 * @var string $formAction
 * @var string $plantId 
 * @var boolean $isUpdate
 * @var TechnicalPlantData|null $technicalPlantData
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isUpdate ? 'Update' : 'Add' ?> Technical Data</title>
</head>
<body>
    <h2>
        <?= $isUpdate ? 'Update' : 'Add' ?> Technical Data for the Power Plant
    </h2>

    <form action="<?= htmlspecialchars($formAction) ?>" method="POST">
        
        <div class="form-group">
            <label for="number_of_reactors">Number of Reactors:</label>
            <input
                type="number" 
                id="number_of_reactors" 
                name="number_of_reactors" 
                min="0"
                value="<?= $isUpdate ? htmlspecialchars($technicalPlantData->getNumberOfReactors() ?? '') : '' ?>"
            />
        </div>

        <div class="form-group">
            <label for="estimated_efficiency">Estimated Efficiency (%):</label>
            <input
                type="number" 
                id="estimated_efficiency" 
                name="estimated_efficiency" 
                step="0.01" 
                min="0"
                max="100"
                value="<?= $isUpdate ? htmlspecialchars($technicalPlantData->getEstimatedEfficiency() ?? '') : '' ?>"
            />
        </div>

        <div class="form-group">
            <label for="operational_risk_level">Operational Risk Level (0.00 - 1.00):</label>
            <input
                type="number" 
                id="operational_risk_level" 
                name="operational_risk_level" 
                step="0.01" 
                min="0"
                max="1"
                value="<?= $isUpdate ? htmlspecialchars($technicalPlantData->getOperationalRiskLevel() ?? '') : '' ?>"
            />
        </div>

        <hr style="border: 0; border-top: 1px solid #27272a; margin: 2rem 0;">
        
        <h3>Reactor Configurations</h3>
        <div id="reactor-configurations-container">
            <?php 
                $existingConfigurations = $isUpdate ? $technicalPlantData->getReactorConfigurations() : [];
                foreach ($existingConfigurations as $index => $config): 
            ?>
                <div class="reactor-block">
                    <h4>Reactor Configuration <?= $index + 1 ?></h4>
                    <div class="form-group">
                        <label>Reactor Type:</label>
                        <select name="reactor_configurations[<?= $index ?>][reactor_type]" required>
                            <option value="">-- Select Reactor Type --</option>
                            <?php foreach (ReactorType::cases() as $type): ?>
                                <option value="<?= $type->value ?>" <?= ($config->getType() === $type) ? 'selected' : '' ?>>
                                    <?= $type->name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Cooling Type:</label>
                        <select name="reactor_configurations[<?= $index ?>][cooling_type]" required>
                            <option value="">-- Select Cooling Type --</option>
                            <?php foreach (CoolingType::cases() as $type): ?>
                                <option value="<?= $type->value ?>" <?= ($config->getCooling() === $type) ? 'selected' : '' ?>>
                                    <?= $type->name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="button" class="btn-danger" onclick="this.parentElement.remove()">Remove</button>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="btn-outline" onclick="addReactor()">+ Add Reactor Configuration</button>

        <hr style="border: 0; border-top: 1px solid #27272a; margin: 2rem 0;">

        <button type="submit"> 
            <?= $isUpdate ? 'Update Technical Data' : 'Save Technical Data' ?> 
        </button>
    </form>

    <footer>
        <a href="/power-plants/<?= htmlspecialchars($plantId); ?>/geological"> 
            &larr; Back 
        </a>
        <a href="/power-plants/<?= htmlspecialchars($plantId); ?>/summary"> 
            Next &rarr;
        </a>
    </footer>

    <?php
        $reactorTypes = [];
        foreach (ReactorType::cases() as $type) {
            $reactorTypes[] = ['value' => $type->value, 'name' => $type->name];
        }

        $coolingTypes = [];
        foreach (CoolingType::cases() as $type) {
            $coolingTypes[] = ['value' => $type->value, 'name' => $type->name];
        }
    ?>

    <script>
        const reactorTypes = <?= json_encode($reactorTypes) ?>;
        const coolingTypes = <?= json_encode($coolingTypes) ?>;
        
        let reactorIndex = <?= isset($existingConfigurations) ? count($existingConfigurations) : 0 ?>;

        function addReactor() {
            const container = document.getElementById('reactor-configurations-container');
            const block = document.createElement('div');
            block.className = 'reactor-block';
            
            const reactorOptionsHtml = reactorTypes.map(t => `<option value="${t.value}">${t.name}</option>`).join('');
            const coolingOptionsHtml = coolingTypes.map(c => `<option value="${c.value}">${c.name}</option>`).join('');
            
            block.innerHTML = `
                <h4>New Reactor Configuration</h4>
                <div class="form-group">
                    <label>Reactor Type:</label>
                    <select name="reactor_configurations[${reactorIndex}][reactor_type]" required>
                        <option value="">-- Select Reactor Type --</option>
                        ${reactorOptionsHtml}
                    </select>
                </div>
                <div class="form-group">
                    <label>Cooling Type:</label>
                    <select name="reactor_configurations[${reactorIndex}][cooling_type]" required>
                        <option value="">-- Select Cooling Type --</option>
                        ${coolingOptionsHtml}
                    </select>
                </div>
                <button type="button" class="btn-danger" onclick="this.parentElement.remove()">Remove</button>
            `;
            
            container.appendChild(block);
            reactorIndex++;
        }
    </script>
</body>
</html>