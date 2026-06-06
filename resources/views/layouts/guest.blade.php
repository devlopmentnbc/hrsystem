<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'HR Management') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">

    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap"
        rel="stylesheet"
    />

    <!-- Remix Icons -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.min.css"
    />

    <!-- Bootstrap 5 CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- Remix Icons -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.min.css"
    />

    <!-- Google Font -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >



    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="font-sans antialiased bg-[#f4f7fb]">

<div class="min-h-screen flex items-center justify-center p-4 lg:p-10">

    <div class="w-full max-w-6xl bg-white rounded-[40px] shadow-2xl overflow-hidden grid lg:grid-cols-2">

        <!-- LEFT SIDE -->
        <div class="hidden lg:flex relative bg-gradient-to-br from-[#2563eb] via-[#4f46e5] to-[#9333ea] p-14 text-white flex-col justify-between overflow-hidden">

            <!-- Shapes -->
            <div class="absolute top-0 right-0 w-80 h-80 bg-white/10 rounded-full"></div>

            <div class="absolute bottom-0 left-0 w-72 h-72 bg-white/10 rounded-full"></div>

            <div class="relative z-10">

                <!-- Logo -->
                <div class="flex items-center mb-14">

                    <div class="w-16 h-16 rounded-3xl bg-white/20 backdrop-blur-md flex items-center justify-center">

                        <i class="ri-team-fill text-4xl"></i>

                    </div>

                    <div class="ml-4">

                        <h1 class="text-3xl font-bold">
                            HR Management
                        </h1>

                        <p class="text-blue-100">
                            Attendance & Payroll Platform
                        </p>

                    </div>

                </div>

                <!-- Main Text -->
                <h2 class="text-6xl font-extrabold leading-tight">
                    Welcome
                    <br>
                    Back!
                </h2>

                <p class="mt-8 text-xl leading-relaxed text-blue-100 max-w-lg">

                    Manage attendance, payroll, overtime,
                    employees and HR operations with a modern
                    enterprise management platform.

                </p>

                <!-- Features -->
                <div class="space-y-6 mt-14">

                    <div class="flex items-center">

                        <div class="w-14 h-14 rounded-2xl bg-cyan-400 flex items-center justify-center shadow-xl">

                            <i class="ri-fingerprint-line text-2xl text-white"></i>

                        </div>

                        <div class="ml-4">

                            <h3 class="text-xl font-semibold">
                                Smart Attendance
                            </h3>

                            <p class="text-blue-100">
                                Real-time employee tracking
                            </p>

                        </div>

                    </div>

                    <div class="flex items-center">

                        <div class="w-14 h-14 rounded-2xl bg-emerald-400 flex items-center justify-center shadow-xl">

                            <i class="ri-money-dollar-circle-line text-2xl text-white"></i>

                        </div>

                        <div class="ml-4">

                            <h3 class="text-xl font-semibold">
                                Payroll Automation
                            </h3>

                            <p class="text-blue-100">
                                Salary & OT calculations
                            </p>

                        </div>

                    </div>

                    <div class="flex items-center">

                        <div class="w-14 h-14 rounded-2xl bg-orange-400 flex items-center justify-center shadow-xl">

                            <i class="ri-bar-chart-grouped-line text-2xl text-white"></i>

                        </div>

                        <div class="ml-4">

                            <h3 class="text-xl font-semibold">
                                Analytics Reports
                            </h3>

                            <p class="text-blue-100">
                                Advanced HR reporting system
                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <!-- Bottom -->
            <div class="relative z-10 flex items-center justify-between mt-10 text-blue-100 text-sm">

                <div class="flex items-center gap-2">

                    <i class="ri-shield-check-line"></i>

                    Secure

                </div>

                <div class="flex items-center gap-2">

                    <i class="ri-time-line"></i>

                    24/7 Access

                </div>

                <div class="flex items-center gap-2">

                    <i class="ri-cloud-line"></i>

                    Cloud Based

                </div>

            </div>

        </div>

        <!-- RIGHT SIDE -->
        <div class="flex items-center justify-center p-6 sm:p-10 lg:p-16 bg-white">

            <div class="w-full max-w-md">

                <!-- Mobile Logo -->
                <div class="lg:hidden text-center mb-10">

                    <div class="w-20 h-20 rounded-3xl bg-gradient-to-r from-blue-600 to-purple-600 flex items-center justify-center mx-auto shadow-xl">

                        <i class="ri-team-fill text-4xl text-white"></i>

                    </div>

                    <h1 class="mt-4 text-3xl font-bold text-gray-800">
                        HR Management
                    </h1>

                </div>

                {{ $slot }}

            </div>

        </div>

    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>