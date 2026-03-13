@extends('layouts.app')

@section('title', 'Login')

@section('content')
<style>
    .login-container {
        max-width: 450px;
        margin: 80px auto;
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    }
    .login-header {
        text-align: center;
        margin-bottom: 30px;
    }
    .login-header h2 {
        color: #333;
        font-size: 2rem;
        margin-bottom: 10px;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #555;
        font-weight: 600;
    }
    .form-control {
        width: 100%;
        padding: 12px;
        border: 2px solid #e1e8ed;
        border-radius: 8px;
        font-size: 1rem;
        transition: border 0.3s;
    }
    .form-control:focus {
        outline: none;
        border-color: #667eea;
    }
    .form-check {
        margin-bottom: 20px;
    }
    .form-check input {
        margin-right: 8px;
    }
    .btn-login {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.3s;
    }
    .btn-login:hover {
        transform: translateY(-2px);
    }
    .register-link {
        text-align: center;
        margin-top: 20px;
        color: #666;
    }
    .register-link a {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
    }
    .error-text {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 5px;
    }
</style>

<div class="login-container">
    <div class="login-header">
        <h2>🚗 Login</h2>
        <p style="color: #666;">Welcome back to Garage Management</p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
            @error('email')
                <span class="error-text">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
            @error('password')
                <span class="error-text">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-check">
            <label>
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                Remember Me
            </label>
        </div>

        <button type="submit" class="btn-login">Login</button>

        <div class="register-link">
            Don't have an account? <a href="{{ route('register') }}">Register here</a>
        </div>
    </form>
</div>
@endsection
