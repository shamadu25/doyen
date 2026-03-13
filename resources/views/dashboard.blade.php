@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<style>
    .dashboard-header {
        margin-bottom: 30px;
    }
    .dashboard-header h1 {
        font-size: 2.5rem;
        color: #333;
        margin-bottom: 10px;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }
    .stat-card {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .stat-icon {
        font-size: 3rem;
        margin-bottom: 15px;
    }
    .stat-title {
        color: #666;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 10px;
    }
    .stat-value {
        font-size: 2.5rem;
        font-weight: bold;
        color: #333;
    }
    .quick-actions {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .quick-actions h2 {
        margin-bottom: 20px;
        color: #333;
    }
    .action-buttons {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }
    .action-btn {
        padding: 15px 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-decoration: none;
        border-radius: 10px;
        text-align: center;
        font-weight: 600;
        transition: transform 0.3s;
        display: block;
    }
    .action-btn:hover {
        transform: translateY(-3px);
    }
</style>

<div class="dashboard-header">
    <h1>Welcome back, {{ auth()->user()->name }}! 👋</h1>
    <p style="color: #666; font-size: 1.1rem;">Here's what's happening in your garage today.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-title">Total Customers</div>
        <div class="stat-value">{{ \App\Models\Customer::count() }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">🚗</div>
        <div class="stat-title">Total Vehicles</div>
        <div class="stat-value">{{ \App\Models\Vehicle::count() }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-title">Appointments Today</div>
        <div class="stat-value">{{ \App\Models\Appointment::whereDate('scheduled_date', today())->count() }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">🔧</div>
        <div class="stat-title">Active Job Cards</div>
        <div class="stat-value">{{ \App\Models\JobCard::where('status', 'in_progress')->count() }}</div>
    </div>
</div>

<div class="quick-actions">
    <h2>Quick Actions</h2>
    <div class="action-buttons">
        <a href="{{ route('customers.create') }}" class="action-btn">➕ New Customer</a>
        <a href="{{ route('vehicles.create') }}" class="action-btn">🚗 Add Vehicle</a>
        <a href="{{ route('appointments.create') }}" class="action-btn">📅 Book Appointment</a>
        <a href="{{ route('job-cards.create') }}" class="action-btn">🔧 Create Job Card</a>
        <a href="{{ route('invoices.create') }}" class="action-btn">📄 New Invoice</a>
        <a href="{{ route('quotes.create') }}" class="action-btn">💰 Create Quote</a>
    </div>
</div>
@endsection
