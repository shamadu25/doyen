@extends('emails.layout')

@section('title', 'Reset Password - Doyen Auto Services')

@section('content')
    <div class="greeting">
        Hello,
    </div>

    <p>
        You are receiving this email because we received a password reset request for your account at Doyen Auto Services.
    </p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $url }}" class="button">Reset Password</a>
    </div>

    <p style="font-size: 12px; color: #64748b;">
        This password reset link will expire in {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} minutes.
    </p>

    <div class="alert alert-warning">
        <strong>⚠️ Security Notice:</strong><br>
        If you did not request a password reset, no further action is required. Your account remains secure.
    </div>

    <div class="divider"></div>

    <p style="font-size: 12px; color: #94a3b8;">
        <strong>Having trouble clicking the button?</strong><br>
        Copy and paste the URL below into your web browser:<br>
        <a href="{{ $url }}" style="color: #3b82f6; word-break: break-all;">{{ $url }}</a>
    </p>
@endsection
