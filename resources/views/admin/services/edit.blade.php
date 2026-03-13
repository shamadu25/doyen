@extends('layouts.app')

@section('title', 'Edit Service: ' . $service->name)

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
        max-width: 900px;
    }
    .form-card-header {
        padding: 20px 28px;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .form-card-header h2 { font-size: 1.3rem; color: #333; }
    .status-badges { display: flex; gap: 8px; }
    .form-body { padding: 28px; }
    .form-section { margin-bottom: 28px; }
    .form-section h3 {
        font-size: 1rem; color: #667eea;
        border-bottom: 2px solid #e8ecff; padding-bottom: 8px;
        margin-bottom: 18px; font-weight: 700;
    }
    .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 18px; }
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .form-group label { font-weight: 600; font-size: 0.88rem; color: #444; }
    .form-group input[type=text],
    .form-group input[type=number],
    .form-group select,
    .form-group textarea {
        padding: 10px 13px; border: 1px solid #ddd;
        border-radius: 8px; font-size: 0.9rem; font-family: inherit;
        transition: border-color 0.2s;
    }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
        outline: none; border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102,126,234,0.15);
    }
    .form-group textarea { resize: vertical; min-height: 90px; }
    .form-group .hint { font-size: 0.78rem; color: #888; }
    .toggle-row { display: flex; gap: 24px; flex-wrap: wrap; }
    .toggle-group {
        display: flex; align-items: center; gap: 10px;
        background: #f8f9fa; border-radius: 8px; padding: 12px 16px; flex: 1; min-width: 160px;
    }
    .toggle-group label { margin: 0; font-weight: 600; font-size: 0.88rem; }
    .toggle-group .hint { font-size: 0.76rem; color: #888; }
    .toggle-group input[type=checkbox] { width: 18px; height: 18px; cursor: pointer; }
    .form-actions {
        padding: 20px 28px; border-top: 1px solid #f0f0f0;
        display: flex; gap: 12px; justify-content: flex-end;
    }
    .error-msg { color: #dc3545; font-size: 0.8rem; }
    .badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    .badge-success  { background: #d4edda; color: #155724; }
    .badge-danger   { background: #f8d7da; color: #721c24; }
    .badge-info     { background: #d1ecf1; color: #0c5460; }
    .badge-warning  { background: #fff3cd; color: #856404; }
    .badge-orange   { background: #ffe6cc; color: #7a3e00; }
</style>
@endpush

@section('content')
<div style="margin-bottom:16px;">
    <a href="{{ route('services.index') }}" style="color:#667eea;text-decoration:none;">← Back to Services</a>
</div>

<div class="form-card">
    <div class="form-card-header">
        <h2>✏️ Edit: {{ $service->name }}</h2>
        <div class="status-badges">
            @if($service->is_active) <span class="badge badge-success">Active</span>
            @else <span class="badge badge-danger">Inactive</span> @endif
            @if($service->is_approved) <span class="badge badge-info">Approved</span>
            @else <span class="badge badge-warning">Pending</span> @endif
            @if($service->show_on_website && $service->is_approved && $service->is_active)
            <span class="badge badge-orange">🌐 Live</span> @endif
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger" style="margin:16px 28px 0;">
        <strong>Please fix the following errors:</strong>
        <ul style="margin-top:8px;padding-left:20px;">
            @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('services.update', $service) }}">
        @csrf @method('PUT')
        <div class="form-body">

            {{-- Basic Info --}}
            <div class="form-section">
                <h3>Basic Information</h3>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="name">Service Name <span style="color:red">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $service->name) }}" required>
                        @error('name') <span class="error-msg">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="code">Service Code</label>
                        <input type="text" name="code" id="code" value="{{ old('code', $service->code) }}">
                        @error('code') <span class="error-msg">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="form-grid-2" style="margin-top:16px;">
                    <div class="form-group">
                        <label for="category">Category <span style="color:red">*</span></label>
                        <input type="text" name="category" id="category" list="category-list"
                               value="{{ old('category', $service->category) }}" required>
                        <datalist id="category-list">
                            @foreach($categories as $cat) <option value="{{ $cat }}"> @endforeach
                            <option value="MOT"><option value="Service"><option value="Repair">
                            <option value="Diagnostics"><option value="Bodywork">
                            <option value="Electrical"><option value="Tyres"><option value="Commercial">
                        </datalist>
                        @error('category') <span class="error-msg">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="icon">Icon / Emoji</label>
                        <input type="text" name="icon" id="icon" value="{{ old('icon', $service->icon) }}" placeholder="e.g. 🔧">
                    </div>
                </div>
                <div class="form-group" style="margin-top:16px;">
                    <label for="description">Internal Description</label>
                    <textarea name="description" id="description">{{ old('description', $service->description) }}</textarea>
                </div>
                <div class="form-group" style="margin-top:16px;">
                    <label for="website_description">Website Description</label>
                    <textarea name="website_description" id="website_description">{{ old('website_description', $service->website_description) }}</textarea>
                    <span class="hint">Shown publicly on the website when service is enabled.</span>
                </div>
            </div>

            {{-- Pricing --}}
            <div class="form-section">
                <h3>Pricing & Duration</h3>
                <div class="form-grid-3">
                    <div class="form-group">
                        <label for="default_price">Price (£) <span style="color:red">*</span></label>
                        <input type="number" name="default_price" id="default_price"
                               value="{{ old('default_price', $service->default_price) }}" min="0" step="0.01" required>
                        @error('default_price') <span class="error-msg">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="cost_price">Cost Price (£)</label>
                        <input type="number" name="cost_price" id="cost_price"
                               value="{{ old('cost_price', $service->cost_price) }}" min="0" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="vat_rate">VAT Rate (%)</label>
                        <input type="number" name="vat_rate" id="vat_rate"
                               value="{{ old('vat_rate', $service->vat_rate) }}" min="0" max="100" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="estimated_duration_minutes">Duration (mins)</label>
                        <input type="number" name="estimated_duration_minutes" id="estimated_duration_minutes"
                               value="{{ old('estimated_duration_minutes', $service->estimated_duration_minutes) }}" min="0" step="15">
                    </div>
                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" name="sort_order" id="sort_order"
                               value="{{ old('sort_order', $service->sort_order ?? 0) }}" min="0">
                        <span class="hint">Lower = shown first on website.</span>
                    </div>
                </div>
            </div>

            {{-- Status & Visibility --}}
            <div class="form-section">
                <h3>Status & Website Visibility</h3>
                <div class="toggle-row">
                    <div class="toggle-group">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                               {{ old('is_active', $service->is_active) ? 'checked' : '' }}>
                        <div>
                            <label for="is_active">Active</label>
                            <div class="hint">Available to use in the system</div>
                        </div>
                    </div>
                    <div class="toggle-group">
                        <input type="checkbox" name="requires_booking" id="requires_booking" value="1"
                               {{ old('requires_booking', $service->requires_booking) ? 'checked' : '' }}>
                        <div>
                            <label for="requires_booking">Requires Booking</label>
                            <div class="hint">Customers must book in advance</div>
                        </div>
                    </div>
                    <div class="toggle-group" style="border: 2px solid {{ $service->is_approved ? '#17a2b8' : '#ffc107' }};">
                        <input type="checkbox" name="is_approved" id="is_approved" value="1"
                               {{ old('is_approved', $service->is_approved) ? 'checked' : '' }}>
                        <div>
                            <label for="is_approved">✅ Approved</label>
                            <div class="hint">Required before showing on website</div>
                        </div>
                    </div>
                    <div class="toggle-group" style="border: 2px solid {{ ($service->show_on_website && $service->is_approved && $service->is_active) ? '#fd7e14' : '#ddd' }};">
                        <input type="checkbox" name="show_on_website" id="show_on_website" value="1"
                               {{ old('show_on_website', $service->show_on_website) ? 'checked' : '' }}>
                        <div>
                            <label for="show_on_website">🌐 Show on Website</label>
                            <div class="hint">Must be approved & active to go live</div>
                        </div>
                    </div>
                </div>
                <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:12px 16px;margin-top:16px;font-size:0.85rem;">
                    ⚠️ A service will only appear on the website when <strong>Active</strong>, <strong>Approved</strong> and <strong>Show on Website</strong> are all enabled.
                </div>
            </div>

        </div>
        <div class="form-actions">
            <a href="{{ route('services.index') }}" class="btn" style="background:#eee;color:#333;">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>
@endsection
