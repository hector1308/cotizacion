<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mi App')</title>
    <style>
        /* Estilos básicos */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
            line-height: 1.6;
        }

        header {
            background: #333;
            color: #fff;
            padding: 1rem 2rem;
        }

        header a {
            color: #fff;
            text-decoration: none;
            margin-right: 1rem;
        }

        nav {
            margin-bottom: 2rem;
        }

        .container {
            width: 90%;
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            padding: 2rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th, td {
            padding: 0.5rem;
            text-align: left;
        }

        th {
            background: #eee;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        h1 {
            margin-top: 0;
        }
    </style>
</head>
<body>

<header>
    <a href="{{ url('/') }}">Inicio</a>
    <a href="{{ route('cotizacion.vista') }}">Cotización</a>
</header>

<div class="container">
    @yield('content')
</div>

</body>
</html>
