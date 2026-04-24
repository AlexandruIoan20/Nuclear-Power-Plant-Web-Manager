<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plant Details Form</title>
</head>
<body>
    <h2>
        Details for the Power Plant
    </h2>

    <form action = "/power-plant-create" method = "POST">
        <div>
            <label>
                Name:
            </label>
            <input
                type = "text" 
                id = "name" 
                name = "name" 
                required
            />
        </div> 
        <div>
            <label>
                Country: 
            </label>
            <input
                type = "text" 
                id = "country" 
                name = "country" 
                list = "country-list" 
                placeholder = "Type to search..." 
                required
            />
            <datalist id = "country-list">
                <?php foreach ($countries as $country) : ?>
                    <option value="<?= htmlspecialchars($country) ?>">
                        <?= htmlspecialchars($country) ?>
                    </option>
                <?php endforeach; ?>
            </datalist>
        </div>

        <div>
            <div>
                <label>
                    Latitude: 
                </label>
                <input
                    type = "number" 
                    id = "latitude" 
                    name = "latitude" 
                    step = "0.01" 
                    min = "0" 
                />
            </div>
            <div>
                <label>
                    Longitude: 
                </label>
                <input
                    type = "number" 
                    id = "longitude" 
                    name = "longitude" 
                    step = "0.01" 
                    min = "0" 
                />
            </div>
        </div>

        <button
            type = "submit" 
        > Save </button>
    </form>
</body>
</html>