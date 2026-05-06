<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <h1> Creare cont </h1>
    <form action = "/register" method = "POST">
        <div>
            <label>Nume: </label>
            <input 
                type = "text" 
                name = "name" 
                required
            />
        </div>
        <div>
            <label>Email: </label>
            <input 
                type = "email" 
                name = "email" 
                required
            />
        </div>
        <div>
            <label>Parola: </label>
            <input 
                type = "password" 
                name = "password" 
                required
            />
        </div>
        <button type = "submit"> Register </button>
    </form>
</body>
</html>