@extends('layouts.office-layout')

@section('title', 'Properties - Dream Mulk')
@section('search-placeholder', 'Search properties...')

@section('top-actions')
    <a href="{{ route('office.property.upload') }}" class="btn-action-primary">
        <i class="fas fa-plus"></i> <span>Add Property</span>
    </a>
@endsection

@section('styles')
<style>
    /* --- CSS Reset & Variables --- */
    :root {
        --primary-color: #4F46E5; /* Hardcoded fallback */
        --text-dark: #111827;
        --text-gray: #6b7280;
        --bg-white: #ffffff;
        --border-light: #e5e7eb;
    }

    /* --- Force Action Button Style --- */
    .btn-action-primary {
        background-color: #4F46E5 !important; /* Hardcoded Indigo 600 */
        color: #ffffff !important;
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
        border: none;
        box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        line-height: 1.5;
    }

    .btn-action-primary:hover {
        background-color: #4338ca !important; /* Darker Indigo */
        color: #ffffff !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 10px -2px rgba(79, 70, 229, 0.4);
    }

    .btn-action-primary i {
        color: #ffffff !important; /* Force icon white */
    }

    /* --- Page Header --- */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 800;
        color: #111827;
        margin: 0;
    }

    /* --- Grid System --- */
    .properties-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 24px;
        padding-bottom: 48px;
    }

    /* --- Property Card --- */
    .property-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .property-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        border-color: #c7d2fe;
    }

    /* Image Area */
    .card-image-wrapper {
        position: relative;
        height: 220px;
        width: 100%;
        overflow: hidden;
        background: #f3f4f6;
    }
    .card-image-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .property-card:hover .card-image-wrapper img {
        transform: scale(1.05);
    }

    /* Badges */
    .badge-group {
        position: absolute;
        top: 12px;
        left: 12px;
        right: 12px;
        display: flex;
        justify-content: space-between;
        pointer-events: none;
    }
    .badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        backdrop-filter: blur(4px);
    }
    .badge-type { background: rgba(255,255,255,0.95); color: #4F46E5; }

    .badge-status { color: white; }
    .status-available { background: rgba(16, 185, 129, 0.9); }
    .status-sold { background: rgba(239, 68, 68, 0.9); }
    .status-rented { background: rgba(245, 158, 11, 0.9); }

    /* Content Area */
    .card-content { padding: 20px; flex: 1; display: flex; flex-direction: column; }

    .price {
        font-size: 24px;
        font-weight: 800;
        color: #4F46E5;
        margin-bottom: 4px;
        line-height: 1.2;
    }

    .property-title {
        font-size: 16px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 8px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .property-location {
        color: #6b7280;
        font-size: 14px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Specs Row */
    .specs-row {
        display: flex;
        justify-content: space-between;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
        margin-top: auto;
    }
    .spec-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        font-size: 14px;
        font-weight: 600;
        color: #111827;
    }
    .spec-item i { color: #9ca3af; margin-bottom: 4px; }
    .spec-item span {
        font-size: 11px;
        color: #6b7280;
        font-weight: 500;
        text-transform: uppercase;
    }

    /* Actions Footer */
    .card-actions {
        padding: 12px 20px;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 12px;
    }
    .action-btn {
        flex: 1;
        padding: 8px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        text-decoration: none;
    }
    .btn-edit { background: white; border: 1px solid #e5e7eb; color: #374151; }
    .btn-edit:hover { border-color: #4F46E5; color: #4F46E5; }

    .btn-delete { background: white; border: 1px solid #e5e7eb; color: #ef4444; }
    .btn-delete:hover { background: #fee2e2; border-color: #fee2e2; }

    /* --- Empty State --- */
    .empty-state {
        text-align: center;
        padding: 80px 20px;
        background: #ffffff;
        border-radius: 20px;
        border: 2px dashed #e5e7eb;
        margin-top: 20px;
    }
    .empty-icon { font-size: 64px; color: #d1d5db; margin-bottom: 24px; }
    .empty-state h3 { font-size: 20px; font-weight: 700; color: #111827; margin-bottom: 8px; }
    .empty-state p { color: #6b7280; margin-bottom: 32px; }

    /* --- Pagination --- */
    .pagination-wrapper { display: flex; justify-content: center; margin-top: 40px; }
    .pagination { display: flex; gap: 8px; }
    .page-link {
        width: 40px; height: 40px;
        display: flex; align-items: center; justify-content: center;
        background: white; border: 1px solid #e5e7eb;
        border-radius: 8px; color: #6b7280;
        text-decoration: none; transition: all 0.2s;
    }
    .page-link:hover { border-color: #4F46E5; color: #4F46E5; }
    .page-link.active { background: #4F46E5; color: white; border-color: #4F46E5; }
    .page-link.disabled { opacity: 0.5; pointer-events: none; }
</style>
@endsection

@section('content')

<div class="page-header">
    <h1 class="page-title">Properties</h1>
    <span style="color: #6b7280; font-weight: 500;">
        {{ $properties->total() }} Properties
    </span>
</div>

@if(session('success'))
    <div style="padding: 16px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; color: #047857; margin-bottom: 32px; display: flex; align-items: center; gap: 12px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

@if($properties->count() > 0)
    <div class="properties-grid">
        @foreach($properties as $property)
            @php
                $images = is_array($property->images) ? $property->images : json_decode($property->images, true);
                $firstImage = !empty($images) && isset($images[0]) ? $images[0] : 'https://placehold.co/600x400/e2e8f0/64748b?text=No+Image';

                $name = is_array($property->name) ? ($property->name['en'] ?? 'N/A') : (json_decode($property->name, true)['en'] ?? $property->name ?? 'N/A');

                $price = is_array($property->price) ? $property->price : json_decode($property->price, true);
                $rooms = is_array($property->rooms) ? $property->rooms : json_decode($property->rooms, true);
                $addr = is_array($property->address_details) ? $property->address_details : json_decode($property->address_details, true);

                $statusClass = match(strtolower($property->status)) {
                    'sold' => 'status-sold',
                    'rented' => 'status-rented',
                    default => 'status-available',
                };
            @endphp

            <div class="property-card">
                <div class="card-image-wrapper">
                    <img src="{{ $firstImage }}" alt="{{ $name }}">
                    <div class="badge-group">
                        <span class="badge badge-type">{{ ucfirst($property->listing_type) }}</span>
                        <span class="badge badge-status {{ $statusClass }}">{{ ucfirst($property->status) }}</span>
                    </div>
                </div>

                <div class="card-content">
                    <div class="price">${{ number_format($price['usd'] ?? 0) }}</div>
                    <div class="property-title" title="{{ $name }}">{{ $name }}</div>
                    <div class="property-location">
                        <i class="fas fa-map-marker-alt" style="color:#4F46E5"></i>
                        {{ ($addr['city']['en'] ?? '') . ($addr['district']['en'] ? ', ' . $addr['district']['en'] : '') }}
                    </div>

                    <div class="specs-row">
                        <div class="spec-item">
                            <i class="fas fa-bed"></i>
                            {{ $rooms['bedroom']['count'] ?? 0 }}
                            <span>Beds</span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-bath"></i>
                            {{ $rooms['bathroom']['count'] ?? 0 }}
                            <span>Baths</span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-ruler-combined"></i>
                            {{ $property->area ?? 0 }}
                            <span>m²</span>
                        </div>
                    </div>
                </div>

                <div class="card-actions">
                    <a href="{{ route('office.property.edit', $property->id) }}" class="action-btn btn-edit">
                        <i class="fas fa-pen"></i> Edit
                    </a>

                    <form action="{{ route('office.property.delete', $property->id) }}" method="POST" style="flex:1; display:flex;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="action-btn btn-delete" onclick="return confirm('Are you sure you want to remove this property?')">
                            <i class="fas fa-trash-alt"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    @if($properties->hasPages())
        <div class="pagination-wrapper">
            <div class="pagination">
                @if ($properties->onFirstPage())
                    <span class="page-link disabled"><i class="fas fa-chevron-left"></i></span>
                @else
                    <a href="{{ $properties->previousPageUrl() }}" class="page-link"><i class="fas fa-chevron-left"></i></a>
                @endif

                @foreach ($properties->getUrlRange(max(1, $properties->currentPage() - 2), min($properties->lastPage(), $properties->currentPage() + 2)) as $page => $url)
                    <a href="{{ $url }}" class="page-link {{ $page == $properties->currentPage() ? 'active' : '' }}">
                        {{ $page }}
                    </a>
                @endforeach

                @if ($properties->hasMorePages())
                    <a href="{{ $properties->nextPageUrl() }}" class="page-link"><i class="fas fa-chevron-right"></i></a>
                @else
                    <span class="page-link disabled"><i class="fas fa-chevron-right"></i></span>
                @endif
            </div>
        </div>
    @endif

@else
    <div class="empty-state">
        <i class="fas fa-city empty-icon"></i>
        <h3>No Properties Found</h3>
        <p>It looks like you haven't added any properties to your portfolio yet.</p>

        <a href="{{ route('office.property.upload') }}"
           class="btn-action-primary"
           style="background-color: #4F46E5 !important; color: white !important; display: inline-flex;">
            <i class="fas fa-plus-circle" style="margin-right: 8px;"></i> Create New Listing
        </a>
    </div>
@endif

@endsection
