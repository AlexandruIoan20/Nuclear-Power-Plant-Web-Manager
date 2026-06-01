<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success</title>
</head>
<body>
    <h1><?= htmlspecialchars($message ?? 'Raspuns inregistrat') ?></h1>
    <a href="<?= htmlspecialchars($homeUrl ?? '/') ?>">Mergi la homepage</a>
</body>
</html>