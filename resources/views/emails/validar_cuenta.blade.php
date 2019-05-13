<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Mouvers</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700" rel="stylesheet">
    <style type="text/css" media="screen">
        img{
            margin: auto;
            display: block;
            width: 100%;
        }
        .content{
            border: 25px solid #04213e;
            padding: 0px;
            width: 35%;
            min-width: 400px;
            margin: auto;
            background: linear-gradient(rgba(255,255,255,.8), rgba(255,255,255,.9)),url(https://mouvers.mx/terminos/imgs/edificios.png) #fff;
            background-size: 100%;
            background-position: bottom;
            background-repeat: no-repeat;
            font-family: 'Roboto', sans-serif;
        }
        .title{
            text-align: center;
            margin: 0px;
            color: #00BCD4;
        }
        .content-text{
            margin-top: 20px;
            padding: 30px;
            text-align: justify;
        }
        .button-cta{
            text-decoration: none;
            margin: auto;
            display: block;
            background-color: #0b417a;
            padding: 15px;
            text-align: center;
            border-radius: 4px;
            color: #fff;
            text-transform: uppercase;
            width: 65%;
        }
    </style>
</head>
<body>
    <div class="content">
        <img src="https://mouvers.mx/terminos/imgs/bg.jpg" alt="">
        <div class="content-text">
            <h2 class="title">BIENVENIDO A MÖUVERS</h2>
            <br>
            <p>Entra en el siguiente enlace para validar tu cuenta: </p>
            <br>
            <a target="_blank" href="{{$enlace}}" class="button-cta">Activar Cuenta</a>
        <br>
        <p>Si no validas tu cuenta, es posible que no puedas acceder a ciertas funciones de Möuvers.</p>
        <br>
        <p>Saludos cordiales, el equipo de Möuvers.</p>
        </div>
    </div>
</body>
</html>