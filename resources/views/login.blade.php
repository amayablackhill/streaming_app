<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Netflick Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #66c0ff;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-container {
            background: none;
            text-align: center;
            width: 100%;
            max-width: 350px;
        }
        .login-container h1 {
            font-weight: bold;
            color: white;
        }
        .form-control {
            margin-bottom: 15px;
        }
        .btn-login {
            background-color: black;
            color: white;
        }
        .btn-register {
            background-color: blue;
            color: white;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>NETFLICK</h1>
        <form action="home" method="post">
            @csrf   
            <div class="mb-3">
                <label class="form-label text-white">Correo electrónico</label>
                <input type="email" class="form-control" >
            </div>
            <div class="mb-3">
                <label class="form-label text-white">Contraseña</label>
                <input type="password" class="form-control" >
            </div>
            <button type="submit" class="btn btn-login w-100">LOGIN</button>
        </form>
        <p class="text-white mt-3">¿Todavía no tienes cuenta?</p>
        <button class="btn btn-register w-100">REGÍSTRATE</button>
    </div>
</body>
</html>