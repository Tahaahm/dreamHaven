@extends('layouts.office-layout')

@section('title', 'Add Property - Dream Mulk')
@section('search-placeholder', 'Search...')

@section('styles')
<style>
    .form-container { max-width: 900px; }
    .form-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 32px; }
    .form-section { margin-bottom: 32px; padding-bottom: 32px; border-bottom: 1px solid var(--border-color); }
    .form-section:last-of-type { border-bottom: none; }
    .form-section h3 { font-size: 18px; font-weight: 700; color: var(--text-primary); margin-bottom: 20px; }
    .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; font-size: 14px; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px 16px; border: 2px solid var(--border-color); border-radius: 8px; font-size: 15px; background: var(--bg-main); color: var(--text-primary); }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #6366f1; }
    .form-group small { display: block; margin-top: 4px; color: var(--text-muted); font-size: 13px; }
    .form-actions { display: flex; gap: 12px; margin-top: 32px; }
    .btn-primary { background: #6366f1; color: white; padding: 14px 32px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
    .btn-primary:hover { background: #5558e3; transform: translateY(-1px); }
    .btn-secondary { background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-primary); padding: 14px 32px; border-radius: 8px; font-weight: 600; text-decoration: none; }
    .alert { padding: 16px; border-radius: 8px; margin-bottom: 20px; }
    .alert-error { background: rgba(239,68,68,0.12); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); }
</style>
@endsection

@section('content')
<div class="form-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
        <h1 style="font-size: 32px; font-weight: 700; color: var(--text-primary);">Add New Property</h1>
        <a href="{{ route('office.properties') }}" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    @if($errors->any())
        <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="form-card">
        <form action="{{ route('office.property.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-section">
                <h3>Basic Information</h3>

                <div class="form-group">
                    <label for="name">Property Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price (USD) *</label>
                        <input type="number" id="price" name="price" value="{{ old('price') }}" min="0" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label for="listing_type">Listing Type *</label>
                        <select id="listing_type" name="listing_type" required>
                            <option value="">Select Type</option>
                            <option value="sale" {{ old('listing_type') == 'sale' ? 'selected' : '' }}>For Sale</option>
                            <option value="rent" {{ old('listing_type') == 'rent' ? 'selected' : '' }}>For Rent</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="form-section">
                <h3>Property Details</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="property_type">Property Type *</label>
                        <select id="property_type" name="property_type" required>
                            <option value="">Select Type</option>
                            <option value="apartment">Apartment</option>
                            <option value="house">House</option>
                            <option value="villa">Villa</option>
                            <option value="commercial">Commercial</option>
                            <option value="land">Land</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="rooms">Bedrooms</label>
                        <input type="number" id="rooms" name="rooms" value="{{ old('rooms') }}" min="0">
                    </div>

                    <div class="form-group">
                        <label for="bathrooms">Bathrooms</label>
                        <input type="number" id="bathrooms" name="bathrooms" value="{{ old('bathrooms') }}" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label for="area">Area (m²)</label>
                    <input type="number" id="area" name="area" value="{{ old('area') }}" min="0" step="0.01">
                </div>
            </div>

            <div class="form-section">
                <h3>Location</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City *</label>
                        <input type="text" id="city" name="city" value="{{ old('city', $office->city ?? '') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="district">District</label>
                        <input type="text" id="district" name="district" value="{{ old('district', $office->district ?? '') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="address_details">Address Details</label>
                    <textarea id="address_details" name="address_details" rows="3">{{ old('address_details') }}</textarea>
                </div>
            </div>

            <div class="form-section">
                <h3>Property Images</h3>

                <div class="form-group">
                    <label for="images">Upload Images (Max 10)</label>
                    <input type="file" id="images" name="images[]" multiple accept="image/*">
                    <small>Recommended: 1200x800px, Max 2MB each</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Add Property
                </button>
                <a href="{{ route('office.properties') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
