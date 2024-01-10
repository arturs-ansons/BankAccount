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

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
