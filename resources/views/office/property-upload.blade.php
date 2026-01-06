@extends('layouts.office-layout')

@section('title', 'Add Property - Dream Mulk Office')

@section('content')
<div class="container">
    <div class="page-header">
        <h1 class="page-title"><i class="fas fa-plus"></i> Add New Property</h1>
        <a href="{{ route('office.properties') }}" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Properties
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-error">
            <ul>
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
                        <input type="text" id="city" name="city" value="{{ old('city', $office->city) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="district">District</label>
                        <input type="text" id="district" name="district" value="{{ old('district', $office->district) }}">
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
                <button type="submit" class="submit-btn">
                    <i class="fas fa-save"></i> Add Property
                </button>
                <a href="{{ route('office.properties') }}" class="cancel-btn">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
    .form-card { background: white; padding: 32px; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .form-section { margin-bottom: 32px; padding-bottom: 32px; border-bottom: 1px solid #e5e7eb; }
    .form-section:last-of-type { border-bottom: none; }
    .form-section h3 { font-size: 18px; margin-bottom: 20px; color: #111827; }
    .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 15px; }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #667eea; }
    .form-group small { display: block; margin-top: 4px; color: #6b7280; font-size: 13px; }
    .form-actions { display: flex; gap: 12px; margin-top: 32px; }
    .submit-btn { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 14px 32px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; }
    .cancel-btn { background: #e5e7eb; color: #374151; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: 600; }
</style>
@endsection
