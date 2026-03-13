@extends('layouts.app')

@section('title', 'Service: ' . $service->name)

@push('styles')
<style>
    .show-card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden; max-width: 800px; }
    .show-header { padding: 24px 28px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: flex-start; }
    .show-header h2 { font-size: 1.6rem; color: #333; }
    .show-body { padding: 28px; }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .info-item { display: flex; flex-direction: column; gap: 4px; }
    .info-item .ilabel { font-size: 0.8rem; color: #888; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    .info-item .ival { font-size: 0.95rem; color: #333; font-weight: 500; }
    .section-divider { border: none; border-top: 1px solid #f0f0f0; margin: 20px 0; }
    .badge { display: inline-flex; align-items: center; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
    .badge-success  { background: #d4edda; color: #155724; }
    .badge-danger   { background: #f8d7da; color: #721c24; }
    .badge-info     { background: #d1ecf1; color: #0c5460; }
    .badge-warning  { background: #fff3cd; color: #856404; }
    .badge-orange   { background: #ffe6cc; color: #7a3e00; }
    .show-actions { padding: 20px 28px; border-top: 1px solid #f0f0f0; display: flex; gap: 10px; flex-wrap: wrap; }
</style>
@endpush

@section('content')
<div style="margin-bottom:16px;">
    <a href="{{ route('services.index') }}" style="color:#667eea;text-decoration:none;">← Back to Services</a>
</div>

<div class="show-card">
    <div class="show-header">
        <div>
            <h2>{{ $service->icon ? $service->icon . ' ' : '' }}{{ $service->name }}</h2>
            <span style="background:#e8ecff;color:#3d56c9;padding:3px 10px;border-radius:12px;font-size:0.8rem;font-weight:600;">
                {{ ucwords($service->category ?? '—') }}
            </span>
            @if($service->code)
            <span style="margin-left:8px;color:#999;font-size:0.82rem;">{{ $service->code }}</span>
            @endif
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            @if($service->is_active) <span class="badge badge-success">Active</span>
            @else <span class="badge badge-danger">Inactive</span> @endif
            @if($service->is_approved) <span class="badge badge-info">Approved</span>
            @else <span class="badge badge-warning">Pending</span> @endif
            @if($service->show_on_website && $service->is_approved && $service->is_active)
            <span class="badge badge-orange">🌐 Live on Website</span>
            @else <span class="badge" style="background:#eee;color:#666;">Hidden from Website</span>
            @endif
        </div>
    </div>
    <div class="show-body">
        <div class="info-grid">
            <div class="info-item">
                <span class="ilabel">Price</span>
                <span class="ival" style="font-size:1.3rem;color:#28a745;font-weight:700;">£{{ number_format($service->default_price, 2) }}</span>
            </div>
            <div class="info-item">
                <span class="ilabel">VAT Rate</span>
                <span class="ival">{{ $service->vat_rate }}%</span>
            </div>
            <div class="info-item">
                <span class="ilabel">Cost Price</span>
                <span class="ival">£{{ number_format($service->cost_price ?? 0, 2) }}</span>
            </div>
            <div class="info-item">
                <span class="ilabel">Duration</span>
                <span class="ival">{{ $service->estimated_duration_minutes ? $service->estimated_duration_minutes . ' minutes' : '—' }}</span>
            </div>
            <div class="info-item">
                <span class="ilabel">Requires Booking</span>
                <span class="ival">{{ $service->requires_booking ? 'Yes' : 'No' }}</span>
            </div>
            <div class="info-item">
                <span class="ilabel">Sort Order</span>
                <span class="ival">{{ $service->sort_order ?? 0 }}</span>
            </div>
        </div>

        @if($service->description)
        <hr class="section-divider">
        <div class="info-item">
            <span class="ilabel">Internal Description</span>
            <p style="color:#555;font-size:0.9rem;margin-top:6px;line-height:1.6;">{{ $service->description }}</p>
        </div>
        @endif

        @if($service->website_description)
        <hr class="section-divider">
        <div class="info-item">
            <span class="ilabel">Website Description</span>
            <p style="color:#555;font-size:0.9rem;margin-top:6px;line-height:1.6;">{{ $service->website_description }}</p>
        </div>
        @endif
    </div>

    <div class="show-actions">
        <a href="{{ route('services.edit', $service) }}" class="btn btn-primary">✏️ Edit Service</a>

        <form method="POST" action="{{ route('services.toggle-approve', $service) }}" style="display:inline;">
            @csrf
            @if($service->is_approved)
                <button type="submit" class="btn" style="background:#ffc107;color:#333;">Unapprove</button>
            @else
                <button type="submit" class="btn" style="background:#17a2b8;color:white;">✅ Approve</button>
            @endif
        </form>

        <form method="POST" action="{{ route('services.toggle-website', $service) }}" style="display:inline;">
            @csrf
            @if($service->show_on_website)
                <button type="submit" class="btn" style="background:#6c757d;color:white;">Hide from Website</button>
            @else
                <button type="submit" class="btn" style="background:#fd7e14;color:white;">🌐 Show on Website</button>
            @endif
        </form>

        <form method="POST" action="{{ route('services.toggle-active', $service) }}" style="display:inline;">
            @csrf
            @if($service->is_active)
                <button type="submit" class="btn btn-danger">Disable</button>
            @else
                <button type="submit" class="btn" style="background:#28a745;color:white;">Enable</button>
            @endif
        </form>

        <form method="POST" action="{{ route('services.destroy', $service) }}" style="display:inline;"
              onsubmit="return confirm('Delete this service?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger">🗑 Delete</button>
        </form>
    </div>
</div>
@endsection
