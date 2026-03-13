<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Garage Management System')</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            color: #333;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }
        .navbar-menu {
            display: flex;
            gap: 2rem;
            list-style: none;
        }
        .navbar-menu a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .navbar-menu a:hover {
            background: rgba(255,255,255,0.2);
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
    </style>
    @stack('styles')
</head>
<body>
    @auth
    <nav class="navbar">
        <div class="navbar-content">
            <a href="{{ route('dashboard') }}" class="navbar-brand">🚗 Garage Management</a>
            <ul class="navbar-menu">
                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li><a href="{{ route('customers.index') }}">Customers</a></li>
                <li><a href="{{ route('vehicles.index') }}">Vehicles</a></li>
                <li><a href="{{ route('appointments.index') }}">Appointments</a></li>
                <li><a href="{{ route('job-cards.index') }}">Job Cards</a></li>
                <li><a href="{{ route('invoices.index') }}">Invoices</a></li>
                <li><a href="{{ route('services.index') }}">Services</a></li>
                <li>
                    <a href="{{ route('whatsapp.support.index') }}" style="display:flex;align-items:center;gap:5px;position:relative;">
                        &#128172; WhatsApp
                        @php $unreadWa = \App\Models\WhatsAppConversation::where('unread_count','>',0)->sum('unread_count'); @endphp
                        @if($unreadWa > 0)
                            <span style="background:#dc3545;color:#fff;font-size:.65rem;font-weight:700;padding:2px 6px;border-radius:10px;margin-left:2px;">{{ $unreadWa }}</span>
                        @endif
                    </a>
                </li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-danger" style="padding: 0.5rem 1rem;">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>
    @endauth

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
