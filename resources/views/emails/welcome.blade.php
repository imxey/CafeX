@component('mail::message')
# Selamat Datang di {{ config('app.name') }}

Halo **{{ $user->name }}**,
Gunakan password sementara berikut untuk login:

@component('mail::panel')
**Password Sementara**: `{{ $password }}`
@endcomponent

Harap ganti password setelah login.

@component('mail::button', ['url' => route('login')])
Login Sekarang
@endcomponent
@endcomponent