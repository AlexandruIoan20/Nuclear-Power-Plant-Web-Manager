<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plant List</title>
</head>
<body>
    <h2> Plant List </h2>
    <ul>
        <?php foreach ($powerPlants as $powerPlant): ?> 
            <li>
                <div>
                    <p>
                        <span>
                            Name: 
                        </span> 
                        <?=  htmlspecialchars($powerPlant->getName()); ?>
                    </p>
                    <p>
                        <span>
                            Country: 
                        </span> 
                        <?=  htmlspecialchars($powerPlant->getCountry()); ?>
                    </p>
                </div>
                <a href="/power-plants/<?= $powerPlant->getId(); ?>/details">
                    Edit Plant
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>