<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Portal</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        body{
            font-family:'Segoe UI',sans-serif;
            background:#f8fafc;
        }

        .navbar{
            background:#ffffff;
            box-shadow:0 2px 10px rgba(0,0,0,.08);
        }

        .hero{
            background:linear-gradient(135deg,#0d6efd,#0b5ed7);
            color:white;
            padding:100px 0;
        }

        .hero h1{
            font-size:55px;
            font-weight:700;
        }

        .hero p{
            font-size:20px;
        }

        .btn-custom{
            padding:12px 30px;
            border-radius:30px;
        }

        .stats{
            margin-top:-60px;
        }

        .stat-card{
            background:white;
            padding:25px;
            border-radius:15px;
            text-align:center;
            box-shadow:0 4px 15px rgba(0,0,0,.08);
        }

        .section-title{
            text-align:center;
            margin-bottom:50px;
            font-weight:700;
        }

        .job-card{
            background:white;
            border-radius:15px;
            padding:25px;
            transition:.3s;
            box-shadow:0 2px 10px rgba(0,0,0,.08);
        }

        .job-card:hover{
            transform:translateY(-5px);
        }

        .footer{
            background:#212529;
            color:white;
            padding:25px;
            text-align:center;
            margin-top:50px;
        }

    </style>
</head>

<body>

<!-- Navbar -->

<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">

        <a class="navbar-brand fw-bold text-primary" href="#">
            JOB PORTAL
        </a>

        <div>

            @if (Route::has('login'))

                @auth

                    <a href="{{ url('/dashboard') }}" class="btn btn-success">
                        Dashboard
                    </a>

                @else

                    <a href="{{ route('login') }}"
                       class="btn btn-outline-primary me-2">
                        Login
                    </a>

                    @if (Route::has('register'))

                        <a href="{{ route('register') }}"
                           class="btn btn-primary">
                            Register
                        </a>

                    @endif

                @endauth

            @endif

        </div>
    </div>
</nav>

<!-- Hero Section -->

<section class="hero">
    <div class="container text-center">

        <h1>Find Your Dream Job Today</h1>

        <p class="mt-3">
            Search thousands of jobs from top companies and apply instantly.
        </p>

        <div class="mt-4">

            <a href="{{ route('register') }}"
               class="btn btn-light btn-lg btn-custom me-3">
                Create Account
            </a>

            <a href="{{ route('login') }}"
               class="btn btn-outline-light btn-lg btn-custom">
                Login
            </a>

        </div>

    </div>
</section>

<!-- Statistics -->

<section class="container stats">

    <div class="row g-4">

        <div class="col-md-3">
            <div class="stat-card">
                <h2 class="text-primary">500+</h2>
                <p>Jobs Available</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <h2 class="text-success">100+</h2>
                <p>Companies</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <h2 class="text-danger">2000+</h2>
                <p>Job Seekers</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <h2 class="text-warning">95%</h2>
                <p>Success Rate</p>
            </div>
        </div>

    </div>

</section>

<!-- Categories -->

<section class="container py-5">

    <h2 class="section-title">
        Popular Job Categories
    </h2>

    <div class="row g-4">

        <div class="col-md-4">
            <div class="job-card">
                <h4>Software Developer</h4>
                <p>Laravel, PHP, React, NodeJS</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="job-card">
                <h4>Human Resource</h4>
                <p>Recruitment & Payroll</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="job-card">
                <h4>Digital Marketing</h4>
                <p>SEO, Ads & Social Media</p>
            </div>
        </div>

    </div>

</section>

<!-- Featured Jobs -->

<section class="container py-5">

    <h2 class="section-title">
        Featured Jobs
    </h2>

    <div class="row g-4">

        <div class="col-md-4">

            <div class="job-card">

                <h5>Laravel Developer</h5>

                <p>
                    Experience: 2+ Years
                </p>

                <p>
                    Location: Patna
                </p>

                <a href="{{ route('register') }}"
                   class="btn btn-primary">
                    Apply Now
                </a>

            </div>

        </div>

        <div class="col-md-4">

            <div class="job-card">

                <h5>HR Executive</h5>

                <p>
                    Experience: 1+ Years
                </p>

                <p>
                    Location: Delhi
                </p>

                <a href="{{ route('register') }}"
                   class="btn btn-primary">
                    Apply Now
                </a>

            </div>

        </div>

        <div class="col-md-4">

            <div class="job-card">

                <h5>Accounts Officer</h5>

                <p>
                    Experience: 3+ Years
                </p>

                <p>
                    Location: Mumbai
                </p>

                <a href="{{ route('register') }}"
                   class="btn btn-primary">
                    Apply Now
                </a>

            </div>

        </div>

    </div>

</section>

<!-- Footer -->

<footer class="footer">

    <h5>Job Portal Management System</h5>

    <p>
        © {{ date('Y') }} All Rights Reserved.
    </p>

</footer>

</body>
</html>