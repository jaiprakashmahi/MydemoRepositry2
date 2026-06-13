<div>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">

    <div class="container">

        <a class="navbar-brand fw-bold">
            Job Portal
        </a>

        <div class="ms-auto">

            @guest

            <a href="{{ route('login') }}"
               class="btn btn-outline-primary me-2">
               Login
            </a>

            <a href="{{ route('register') }}"
               class="btn btn-primary">
               Register
            </a>

            @endguest

            @auth

            <a href="{{ route('dashboard') }}"
               class="btn btn-success">
               Dashboard
            </a>

            @endauth

        </div>

    </div>

</nav>

<section class="bg-primary text-white py-5">

    <div class="container text-center">

        <h1 class="display-4 fw-bold">
            Find Your Dream Job
        </h1>

        <p class="lead">
            Search Jobs From Top Companies
        </p>

        <div class="row justify-content-center mt-4">

            <div class="col-md-4">
                <input class="form-control"
                       placeholder="Job Title">
            </div>

            <div class="col-md-3">
                <input class="form-control"
                       placeholder="Location">
            </div>

            <div class="col-md-2">
                <button class="btn btn-warning w-100">
                    Search
                </button>
            </div>

        </div>

    </div>

</section>

<section class="py-5">

    <div class="container">

        <div class="row text-center">

            <div class="col-md-4">
                <h2>5000+</h2>
                <p>Jobs</p>
            </div>

            <div class="col-md-4">
                <h2>1000+</h2>
                <p>Companies</p>
            </div>

            <div class="col-md-4">
                <h2>15000+</h2>
                <p>Candidates</p>
            </div>

        </div>

    </div>

</section>

<section class="bg-light py-5">

    <div class="container">

        <h2 class="text-center mb-5">
            Featured Jobs
        </h2>

        <div class="row">

            @for($i=1;$i<=6;$i++)

            <div class="col-md-4 mb-4">

                <div class="card shadow">

                    <div class="card-body">

                        <h5>Laravel Developer</h5>

                        <p>ABC Technologies</p>

                        <p>Patna, Bihar</p>

                        <button class="btn btn-primary">
                            Apply Now
                        </button>

                    </div>

                </div>

            </div>

            @endfor

        </div>

    </div>

</section>

</div>