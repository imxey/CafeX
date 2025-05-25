<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>KafeX</title>
    <link rel="icon" type="image/ico" href="{{ asset('images/coffee.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#D4D4D9] flex justify-center items-center min-h-screen">
    @yield('content')
</body>

</html>