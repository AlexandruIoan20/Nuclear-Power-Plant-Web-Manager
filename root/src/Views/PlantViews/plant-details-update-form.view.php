<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Plant Details</title>
</head>
<body>
    <h2>
        Update Details for the Power Plant: <?= htmlspecialchars($plant->getName() ?? 'New Plant') ?>
    </h2>

    <form action="/power-plants/<?= $plant->getId(); ?>/details" method="POST">
        <div>
            <label for="name">Name:</label>
            <input
                type="text" 
                id="name" 
                name="name" 
                value="<?= htmlspecialchars($plant->getName() ?? '') ?>"
                required
            />
        </div> 

        <div>
            <label for="country">Country:</label>
            <input
                type="text" 
                id="country" 
                name="country" 
                list="country-list" 
                placeholder="Type to search..." 
                value="<?= htmlspecialchars($plant->getCountry() ?? '') ?>"
                required
            />
            <datalist id="country-list">
                <?php foreach ($countries as $country) : ?>
                    <option value="<?= htmlspecialchars($country) ?>">
                        <?= htmlspecialchars($country) ?>
                    </option>
                <?php endforeach; ?>
            </datalist>
        </div>

        <div>
            <div>
                <label for="latitude">Latitude:</label>
                <input
                    type="number" 
                    id="latitude" 
                    name="latitude" 
                    step="0.01" 
                    value="<?= $plant->getLatitude() ?>"
                />
            </div>
            <div>
                <label for="longitude">Longitude:</label>
                <input
                    type="number" 
                    id="longitude" 
                    name="longitude" 
                    step="0.01" 
                    value="<?= $plant->getLongitude() ?>"
                />
            </div>
        </div>

        <button type="submit"> Save Changes </button>
    </form>

    <footer>
        <a disabled> Back </a>
        <a
            href = "/power-plants/<?= $plant->getId(); ?>/basics"
        > 
            Next 
        </a>
    </footer>
</body>
</html> 