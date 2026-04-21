<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista Utilizatori</title>
    <style>table, th, td { border: 1px solid black; border-collapse: collapse; padding: 8px; }</style>
</head>
<body>
    <h1>Registered Users </h1>
    <a href = "/register"> Adauga utilizator nou</a> <br><br> 

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?=  htmlspecialchars($user->getId()) ?></td> 
                    <td><?=  htmlspecialchars($user->getName()) ?></td> 
                    <td><?=  htmlspecialchars($user->getEmail()) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
