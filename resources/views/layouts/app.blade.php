<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Therapists Management')</title>
    <link href="{{ asset('css/reset.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <div class="header">
        <div class="container">
            <div class="header-content">
                <div>
                    <h1>Therapists Management</h1>
                </div>
                <div class="button-group">
                    <a class="link" href="{{ route('therapists.index') }}">All Therapists</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        @if(session('success'))
        <div style="background: #d4edda; color: #155724; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div style="background: #f8d7da; color: #721c24; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            {{ session('error') }}
        </div>
        @endif

        @yield('content')
    </div>
</body>

</html>