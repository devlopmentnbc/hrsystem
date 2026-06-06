
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>HR Management Login</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- Google Font -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>

        body{ font-family: 'Poppins', sans-serif; background: linear-gradient(135deg,#2563eb,#4f46e5,#7c3aed); min-height:100vh; overflow-x:hidden; padding:20px; } .login-card{ border:none; border-radius:28px; overflow:hidden; box-shadow:0 15px 40px rgba(0,0,0,.15); max-width:1000px; margin:auto; } .left-panel{ background: linear-gradient(135deg,#2563eb,#4f46e5,#7c3aed); color:white; position:relative; padding:45px; } .left-panel::before{ content:''; position:absolute; width:220px; height:220px; background:rgba(255,255,255,.08); border-radius:50%; top:-80px; right:-80px; } .left-panel::after{ content:''; position:absolute; width:180px; height:180px; background:rgba(255,255,255,.08); border-radius:50%; bottom:-60px; left:-60px; } .feature-box{ background:rgba(255,255,255,.1); border-radius:18px; padding:12px; backdrop-filter:blur(10px); } .login-right{ padding:45px; background:white; } .form-control{ height:52px; border-radius:14px; border:1px solid #dbe2ea; padding-left:45px; font-size:15px; } .form-control:focus{ box-shadow:none; border-color:#4f46e5; } .input-group-text{ position:absolute; z-index:10; height:52px; border:none; background:transparent; color:#6b7280; } .btn-login{ height:52px; border-radius:14px; background:linear-gradient(135deg,#2563eb,#7c3aed); border:none; font-weight:600; font-size:16px; } .btn-login:hover{ opacity:.95; } @media(max-width:991px){ body{ padding:15px; } .left-panel{ display:none; } .login-right{ padding:35px 25px; } }

    </style>

</head>

<body>

<div class="container py-5">

    <div class="row justify-content-center align-items-center min-vh-100">

        <div class="col-xl-10">

            <div class="card login-card">

                <div class="row g-0">

                    <!-- LEFT -->
                    <div class="col-lg-6 left-panel d-flex flex-column justify-content-between">

                        <div>

                            <div class="d-flex align-items-center mb-5">

                                <div class="bg-white bg-opacity-25 rounded-4 p-3">

                                    <i class="bi bi-people-fill fs-2"></i>

                                </div>

                                <div class="ms-3">

                                    <h2 class="fw-bold mb-0">
                                        HR System
                                    </h2>

                                    <small>
                                        Attendance & Payroll
                                    </small>

                                </div>

                            </div>

                            <h1 class="fw-bold display-4">
                                Welcome
                                <br>
                                Back!
                            </h1>

                            <p class="mt-4 fs-5 text-white-50">

                                Manage attendance, overtime,
                                payroll and employee operations
                                with one smart platform.

                            </p>

                        </div>

                        <div class="row g-3 mt-4">

                            <div class="col-4">

                                <div class="feature-box text-center">

                                    <i class="bi bi-fingerprint fs-2"></i>

                                    <div class="mt-2 small">
                                        Attendance
                                    </div>

                                </div>

                            </div>

                            <div class="col-4">

                                <div class="feature-box text-center">

                                    <i class="bi bi-cash-stack fs-2"></i>

                                    <div class="mt-2 small">
                                        Payroll
                                    </div>

                                </div>

                            </div>

                            <div class="col-4">

                                <div class="feature-box text-center">

                                    <i class="bi bi-bar-chart-line fs-2"></i>

                                    <div class="mt-2 small">
                                        Reports
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- RIGHT -->
                    <div class="col-lg-6 login-right d-flex align-items-center">

                        <div class="w-100">

                            <h2 class="fw-bold mb-2">
                                Sign In
                            </h2>

                            <p class="text-muted mb-5">
                                Login to continue to your dashboard
                            </p>

                            <!-- Errors -->
                            @if ($errors->any())

                                <div class="alert alert-danger rounded-4">

                                    <ul class="mb-0">

                                        @foreach ($errors->all() as $error)

                                            <li>{{ $error }}</li>

                                        @endforeach

                                    </ul>

                                </div>

                            @endif

                            <form method="POST" action="{{ route('login.submit') }}">

                                @csrf

                                <!-- Email -->
                                <div class="mb-4 position-relative">

                                    <span class="input-group-text">
                                        <i class="bi bi-envelope"></i>
                                    </span>

                                    <input
                                        type="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        class="form-control"
                                        placeholder="Email Address"
                                        required
                                    >

                                </div>

                                <!-- Password -->
                                <div class="mb-4 position-relative">

                                    <span class="input-group-text">
                                        <i class="bi bi-lock"></i>
                                    </span>

                                    <input
                                        type="password"
                                        name="password"
                                        class="form-control"
                                        placeholder="Password"
                                        required
                                    >

                                </div>

                                <!-- Remember -->
                                <div class="d-flex justify-content-between align-items-center mb-4">

                                    <div class="form-check">

                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="remember"
                                        >

                                        <label class="form-check-label">
                                            Remember Me
                                        </label>

                                    </div>

                                    <a
                                        href=""
                                        class="text-decoration-none"
                                    >
                                        Forgot Password?
                                    </a>

                                </div>

                                <!-- Button -->
                                <button
                                    type="submit"
                                    class="btn btn-primary btn-login w-100"
                                >
                                    Sign In
                                </button>

                            </form>

                            <div class="text-center mt-5 text-muted small">

                                © {{ date('Y') }} HR Management System

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>