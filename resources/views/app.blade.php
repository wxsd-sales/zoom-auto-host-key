<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ config('app.description', '') }}">
    <meta name="keywords" content="{{ implode(",", config('app.keywords', [])) }}">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    {{--  <link rel="preconnect" href="https://fonts.bunny.net">--}}
    {{--  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />--}}

    <!-- Scripts -->
    @routes
    @vite(['resources/css/app.scss', 'resources/js/app.ts', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{asset('/images/favicons/apple-touch-icon.png')}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('/images/favicons/favicon-32x32.png')}}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('/images/favicons/favicon-16x16.png')}}">
    <link rel="manifest" href="{{asset('/images/favicons/site.webmanifest')}}">
    <link rel="mask-icon" href="{{asset('/images/favicons/safari-pinned-tab.svg')}}" color="#5bbad5">
    <link rel="shortcut icon" href="{{asset('/images/favicons/favicon.ico')}}">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="msapplication-config" content="{{asset('/images/favicons/browserconfig.xml')}}">
    <meta name="theme-color" content="#ffffff">
</head>
<body class="has-navbar-fixed-top">
<nav class="navbar is-fixed-top has-shadow" role="navigation" aria-label="{{ __('Main Navigation') }}">
    <div class="navbar-brand">
        <a class="navbar-item" href="{{ url('/') }}">
            <figure class="image is-32x32 mr-2">
                <img src="{{asset('/images/favicons/favicon.svg')}}" alt="{{ config('app.name', 'Laravel') }}">
            </figure>
            <strong>{{ config('app.name', 'Laravel') }}</strong>
        </a>
        <a role="button" id="main-burger" class="navbar-burger burger" data-target="navbar"
           aria-controls="navbar"
           aria-expanded="false"
           aria-label="{{ __('Toggle navigation') }}"
        >
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
        </a>
    </div>

    <div id="navbar" class="navbar-menu">
        <!-- Right Side Of Navbar -->
        <div class="navbar-end">
            <!-- Authentication Links -->
            <div class="navbar-item">
                @guest
                    @if (Route::has('login'))
                        <a @class(["button is-link is-fullwidth is-rounded", "is-active" => Request::is('login')])
                           href="{{ route('login') }}"
                        >
                            <span class="icon"><i class="mdi mdi-login"> </i></span>
                            <span>{{ __('Login') }}</span>
                        </a>
                    @endif
                @endguest
                @auth
                    <div class="navbar-item has-dropdown is-hoverable">
                        <a id="navbarDropdown" class="navbar-link is-size-5" href="#" data-toggle="dropdown"
                           aria-haspopup="true" aria-expanded="false"
                        >
                            <b>{{ Auth::user()->email }}</b>
                        </a>
                        <div class="navbar-dropdown">
                            @if(Route::has('dashboard'))
                                <a @class(["navbar-item is-size-6", "is-active" => Request::is('dashboard')])
                                   href="{{ route('dashboard') }}"
                                >
                                    <span class="icon"><i class="mdi mdi-view-dashboard"> </i></span>
                                    <span>{{ __('Dashboard') }}</span>
                                </a>
                            @endif
                            <hr class="navbar-divider">
                            <a class="navbar-item is-size-6 has-text-danger" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                            >
                                <span class="icon"><i class="mdi mdi-logout"> </i></span>
                                <span>{{ __('Logout') }}</span>
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="is-hidden">
                                @csrf
                            </form>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>

@if (Auth::check() && str_ends_with(Auth::user()->email, 'cisco.com'))
    <section class="hero is-small is-warning">
        <div class="hero-body px-4">
            <div class="container">
                <h2 class="subtitle">
                    You have logged in using your {{ Auth::user()->email }} Cisco account. This is usually not
                    recommended.
                </h2>
            </div>
        </div>
    </section>
@endif

<noscript id="javascript-warning" class="hero is-danger is-bold">
    <div class="hero-body">
        <div class="container">
            <h1 class="title">
                <span class="icon"><i class="mdi mdi-alert"></i></span>
                <span>Javascript is disabled.</span>
            </h1>
            <h2 class="subtitle">
                This site requires Javascript for its core functionality. Please enable Javascript in browser settings
                and reload this page.
            </h2>
        </div>
    </div>
</noscript>

<section id="cookies-warning" class="hero is-danger is-bold is-hidden">
    <div class="hero-body">
        <div class="container">
            <h1 class="title">
                <span class="icon"><i class="mdi mdi-alert"></i></span>
                <span>Cookies are disabled.</span>
            </h1>
            <h2 class="subtitle">
                This site requires cookies for its core functionality. Please enable cookies in browser settings and
                reload this page.
            </h2>
        </div>
    </div>
</section>

<main id="app" class="is-invisible mt-6 mb-6">
    @inertia("main")
</main>

<footer class="footer">
    <div class="content has-text-centered">
        <p>
            <strong>{{ config('app.name', 'Laravel') }}</strong> by
            <a href="https://github.com/wxsd-sales">WXSD-Sales</a>.
            <br />
            &copy; {{ date('Y') }} Webex by Cisco
        </p>
    </div>
</footer>

<!-- Scripts -->
<script>
    document.addEventListener("DOMContentLoaded", () => {

        function toggleNavbar(element) {
            // Get the target from the "data-target" attribute
            const target = document.getElementById(element.dataset.target);
            // Toggle the "is-active" class on both the "navbar-burger" and the "navbar-menu"
            element.classList.toggle("is-active");
            target.classList.toggle("is-active");
        }

        // if this functions returns it indicates javascript is enabled on the browser
        function isJavascriptEnabled() {
            return true;
        }

        // https://github.com/Modernizr/Modernizr/blob/master/feature-detects/cookies.js
        function hasCookiesDisabled() {
            // Quick test if browser has cookieEnabled host property
            if (navigator.cookieEnabled) return false;
            // Create cookie
            document.cookie = "cookietest=1";
            const isCookieSet = document.cookie.indexOf("cookietest=") !== -1;
            // Delete cookie
            document.cookie = "cookietest=1; expires=Thu, 01-Jan-1970 00:00:01 GMT";

            return !isCookieSet;
        }

        const mainBurger = document.getElementById("main-burger");
        mainBurger.addEventListener("click", () => toggleNavbar(mainBurger));

        if (isJavascriptEnabled()) {
            document.getElementById("app").classList.remove("is-invisible");
        }

        if (hasCookiesDisabled()) {
            console.error("Cookies are disabled.");
            document.getElementById("cookies-warning").classList.remove("is-hidden");
            document.getElementById("app").classList.add("is-invisible");
        }
    });
</script>
</body>
</html>
