@auth

    <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-center" id="navbarNav" style="margin-bottom: 20px">
                    <ul class="navbar-nav">
                        <li class="nav-item {{ Request::is('dashboard') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="nav-item {{ Request::is('transfer') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('transfer') }}">Transfer Money</a>
                        </li>
                        <li class="nav-item {{ Request::is('add-currency') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('add-currency') }}">Create Currency Account</a>
                        </li>
                        <li class="nav-item {{ Request::is('buy-crypto') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('buy-crypto') }}">Buy Crypto</a>
                        </li>


                        <li class="nav-item {{ Request::is('sell-crypto') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('sell-crypto') }}">Sell Crypto</a>
                        </li>
                        <li class="nav-item {{ Request::is('transactions') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('transactions') }}">Transactions History</a>
                        </li>


                    </ul>
                </div>
            </div>
        </nav>

@endauth

