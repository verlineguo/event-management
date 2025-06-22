<nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
    <div class="container">
        <a class="navbar-brand" href="index.html">Even<span>talk.</span></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav"
            aria-expanded="false" aria-label="Toggle navigation">
            <span class="oi oi-menu"></span> Menu
        </button>

        <div class="collapse navbar-collapse" id="ftco-nav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active"><a href="{{ route('member.home') }}" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="{{ route('member.events.index') }}" class="nav-link">Events</a></li>
                <li class="nav-item"><a href="{{{ route('member.myRegistrations.index') }}}" class="nav-link">History</a></li>
                <li class="nav-item"><a href="{{ route('member.profile') }}" class="nav-link">Profile</a></li>

     
                
                <li class="nav-item cta mr-md-2">
                        <a href="#"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                            class="nav-link">
                            Logout
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>

                    </li>

            </ul>
        </div>
    </div>
</nav>
