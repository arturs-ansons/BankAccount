@include('includes.head')
<body>
   @include('includes.header')
<br>

    <main class="page-main">
        @include('includes.menu')
        <br>
        @yield('content')
        @yield('dashboard')
        @yield('add-currency')
        @yield('buy-crypto')
        @yield('transactions')
        </main>
    @include('includes.footer')
</body>
</html>

