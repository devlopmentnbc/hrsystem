<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        HR Management System
    </title>

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

        body{
            font-family:'Poppins',sans-serif;
            background:#f4f7fb;
            overflow-x:hidden;
        }

        .main-content{
            margin-left:270px;
            padding:25px;
        }

        .topbar{
            background:white;
            border-radius:22px;
            padding:18px 25px;
            box-shadow:0 5px 20px rgba(0,0,0,.05);
            margin-bottom:25px;
        }

        .content-card{
            background:white;
            border-radius:24px;
            padding:25px;
            box-shadow:0 5px 20px rgba(0,0,0,.05);
            border:none;
        }

        .pagination-wrap{
            margin-top:20px;
        }

        .app-pagination{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:16px;
            flex-wrap:wrap;
            padding:14px 16px;
            border:1px solid #e9eef7;
            border-radius:18px;
            background:#f8fbff;
        }

        .app-pagination .pagination{
            margin-bottom:0;
            flex-wrap:wrap;
            gap:6px;
        }

        .app-pagination .page-item{
            margin:0;
        }

        .app-pagination .page-link{
            border:none;
            min-width:42px;
            height:42px;
            display:flex;
            align-items:center;
            justify-content:center;
            border-radius:999px;
            padding:.55rem .85rem;
            color:#334155;
            background:#fff;
            box-shadow:0 2px 8px rgba(15,23,42,.05);
            font-weight:600;
        }

        .app-pagination .page-item.active .page-link{
            background:linear-gradient(135deg, #2563eb, #4f46e5);
            color:#fff;
        }

        .app-pagination .page-item.disabled .page-link{
            background:#eef2f7;
            color:#94a3b8;
            box-shadow:none;
        }

        .pagination-meta{
            white-space:nowrap;
        }

        .schedule-calendar-grid{
            display:grid;
            grid-template-columns:repeat(auto-fit, minmax(90px, 1fr));
            gap:10px;
        }

        .schedule-day-card{
            border:1px solid #e5e7eb;
            border-radius:16px;
            padding:12px 10px;
            background:#fff;
            min-height:88px;
            display:flex;
            flex-direction:column;
            justify-content:space-between;
            transition:all .2s ease;
        }

        .schedule-day-card.free{
            background:#f0fdf4;
            border-color:#86efac;
        }

        .schedule-day-card.booked{
            background:#fff7ed;
            border-color:#fdba74;
        }

        .schedule-day-card.overlap{
            background:#fef2f2;
            border-color:#f87171;
            box-shadow:0 4px 12px rgba(239,68,68,.08);
        }

        .schedule-day-card.active-booking{
            border-width:2px;
        }

        .booking-pill-list{
            display:flex;
            flex-wrap:wrap;
            gap:6px;
        }

        @media(max-width:991px){

            .main-content{
                margin-left:0;
                padding:15px;
            }

        }

        @media(max-width:575px){

            .content-card{
                padding:18px;
                border-radius:18px;
            }

            .app-pagination{
                padding:14px;
                border-radius:16px;
                align-items:stretch;
            }

            .pagination-meta{
                width:100%;
                text-align:center;
                white-space:normal;
            }

            .app-pagination .pagination-pages{
                display:none !important;
            }

            .pagination-mobile-actions .btn,
            .pagination-mobile-actions span{
                display:flex;
                align-items:center;
                justify-content:center;
                min-height:42px;
            }
        }

    </style>

</head>

<body>

    <!-- Sidebar -->
    @include('layouts.sidebar')

    <!-- Main -->
    <div class="main-content">

        <!-- Topbar -->
        <div class="topbar d-flex justify-content-between align-items-center">

            <div>

                <h4 class="fw-bold mb-1">
                    @yield('title')
                </h4>

                <small class="text-muted">
                    Welcome back, {{ auth()->user()->name }}
                </small>

            </div>

            <div class="d-flex align-items-center gap-3">

                <button class="btn btn-light rounded-circle">

                    <i class="bi bi-bell"></i>

                </button>

                <form method="POST" action="{{ route('logout') }}">

                    @csrf

                    <button class="btn btn-danger rounded-pill px-4">

                        Logout

                    </button>

                </form>

            </div>

        </div>

        <!-- Content -->
        @yield('content')

    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')

</body>

</html>