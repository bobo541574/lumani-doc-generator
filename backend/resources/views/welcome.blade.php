<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        <style>
            /*! normalize.css v8.0.1 | MIT License | github.com/necolas/normalize.css */
            html{line-height:1.15;-webkit-text-size-adjust:100%}body{margin:0}a{background-color:transparent}[hidden]{display:none}html{font-family:system-ui,-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji'}code{font-family:Menlo,Monaco,Consolas,'Liberation Mono','Courier New',monospace}html{color:#333;background-color:#f4f4f4}.container{padding:1rem;max-width:1200px;margin:0 auto}.hero{padding:4rem 0}.hero h1{font-size:3rem;font-weight:700;line-height:1.1;margin:0 0 1rem}.hero p{font-size:1.2rem;color:#666;margin:0 0 2rem}.btn{display:inline-block;padding:.75rem 1.5rem;border-radius:.5rem;background-color:#333;color:#fff;text-decoration:none;font-weight:600}
        </style>
    </head>
    <body class="antialiased">
        <div class="container">
            <div class="hero">
                <h1>{{ $title ?? config('app.name', 'Laravel') }}</h1>

                <p>{{ $slot ?? __('Welcome to your application.') }}</p>

                @if (Route::has('dashboard'))
                    <a class="btn" href="{{ route('dashboard') }}">Dashboard</a>
                @endif
            </div>
        </div>
    </body>
</html>
