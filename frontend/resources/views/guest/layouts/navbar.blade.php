<nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
    <div class="container">
        <a class="navbar-brand" href="index.html">Even<span>talk.</span></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav"
            aria-expanded="false" aria-label="Toggle navigation">
            <span class="oi oi-menu"></span> Menu
        </button>

        <div class="collapse navbar-collapse" id="ftco-nav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active"><a href="{{route('guest.home') }}" class="nav-link">Home</a></li>
                <li class="nav-item mr-5"><a href="{{route('guest.events') }}" class="nav-link">Events</a></li>
                <li class="nav-item su mr-md-2"><a href="{{route('register') }}" class=" nav-link">Signup</a></li>
     
                <li class="nav-item cta mr-md-2"><a href="{{ route('login') }}" class="nav-link">Login</a></li>

            </ul>
        </div>
    </div>
</nav>
