<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Your Role</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            text-align: center;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        h1 {
            margin-bottom: 20px;
            color: #333;
        }
        .btn-custom {
            width: 200px;
            padding: 10px;
            font-size: 18px;
            margin: 10px;
            border-radius: 5px;
        }
        .btn-admin {
            background-color: #dc3545;
            color: white;
        }
        .btn-admin:hover {
            background-color: #c82333;
        }
        .btn-user {
            background-color: #007bff;
            color: white;
        }
        .btn-user:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Select Your Role</h1>
    <p>Please choose whether you want to enter as an Admin or a User.</p>
    
    <a href="backend/public/index.php" class="btn btn-custom btn-admin">Admin</a>
    <a href="frontend/public/index.php" class="btn btn-custom btn-user">User</a>
</div>

</body>
</html>
