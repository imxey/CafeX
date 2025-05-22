<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>CafeX</title>
    <link rel="icon" type="image/png" href="{{ asset('images/coffee.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#D4D4D9] flex justify-center items-center min-h-screen">
    @yield('content')
</body>

</html>
