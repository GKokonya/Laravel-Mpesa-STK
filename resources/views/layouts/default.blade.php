<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-gray-200">
      <!-- navbar -->
      <header aria-label="Site Header" class="bg-white shadow-lg">
        <div class="mx-auto flex h-16 max-w-screen-xl items-center gap-8 px-4 sm:px-6 lg:px-8">
          <div class="flex flex-1 items-center justify-end md:justify-between">
            <nav aria-label="Site Nav" class="hidden md:block">
              <ul class="flex items-center gap-6 text-sm">
                <li>
                  <a class="text-gray-500 transition hover:text-gray-500/75" href="/"> Home </a>
                </li>
                <li>
                  <a class="text-gray-500 transition hover:text-gray-500/75" href="{{ route('payments.index') }}"> Payments </a>
                </li>
                <li>
                  <a class="text-gray-500 transition hover:text-gray-500/75" href="{{ route('stk-requests.index') }}"> STK Requests </a>
                </li>
              </ul>
            </nav>


          </div>
        </div>
      </header>

      <!--main content -->
      <div class="main my-12 mx-12">

          @yield('content')
      </div>


    </body>
</html>
