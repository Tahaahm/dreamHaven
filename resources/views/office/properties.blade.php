@extends('layouts.office-layout')

@section('title', 'Properties - Dream Mulk')
@section('search-placeholder', 'Search properties...')

@section('top-actions')
<a href="{{ route('office.property.upload') }}" class="add-btn">
    <i class="fas fa-plus"></i> Add Property
</a>
@endsection

@section('styles')
<style>
    .page-title { font-size: 32px; font-weight: 700; color: var(--text-primary); margin-bottom: 32px; }
    .properties-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; }
    .property-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; overflow: hidden; transition: all 0.3s; }
    .property-card:hover { transform: translateY(-5px); box-shadow: 0 12px 40px var(--shadow); border-color: rgba(99,102,241,0.4); }

    .property-img { position: relative; width: 100%; height: 220px; overflow: hidden; }
    .property-img img { width: 100%; height: 100%; object-fit: cover; }
    .property-badge { position: absolute; top: 12px; left: 12px; background: #6366f1; color: white; padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 700; }
    .property-status { position: absolute; top: 12px; right: 12px; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; text-transform: capitalize; }
    .property-status.available { background: rgba(34,197,94,0.9); color: white; }
    .property-status.sold { background: rgba(239,68,68,0.9); color: white; }
    .property-status.rented { background: rgba(249,115,22,0.9); color: white; }

    .property-actions { position: absolute; bottom: 12px; right: 12px; display: flex; gap: 8px; }
    .action-btn { width: 36px; height: 36px; background: rgba(0,0,0,0.6); backdrop-filter: blur(10px); border: none; border-radius: 8px; color: white; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; }
    .action-btn:hover { background: rgba(99,102,241,0.9); transform: scale(1.1); }

    .property-info { padding: 20px; }
    .property-price { font-size: 24px; font-weight: 700; color: #6366f1; margin-bottom: 8px; }
    .property-name { font-size: 16px; font-weight: 600; color: var(--text-primary); margin-bottom: 8px; line-height: 1.4; }
    .property-location { font-size: 13px; color: var(--text-muted); margin-bottom: 16px; display: flex; align-items: center; gap: 6px; }

    .property-specs { display: flex; gap: 16px; padding-top: 16px; border-top: 1px solid var(--border-color); }
    .spec-item { display: flex; align-items: center; gap: 6px; font-size: 14px; color: var(--text-secondary); }
    .spec-item i { font-size: 16px; color: var(--text-muted); width: 18px; text-align: center; }

    .empty-state { text-align: center; padding: 80px 20px; color: var(--text-muted); }
    .empty-state i { font-size: 64px; margin-bottom: 20px; opacity: 0.3; }
    .empty-state h3 { color: var(--text-secondary); margin-bottom: 8px; font-size: 20px; }
    .empty-state .add-btn-large { display: inline-block; margin-top: 20px; padding: 14px 28px; background: #6366f1; color: white; border-radius: 10px; text-decoration: none; font-weight: 600; transition: all 0.3s; }
    .empty-state .add-btn-large:hover { background: #5558e3; transform: translateY(-2px); }

    .pagination { display: flex; justify-content: center; gap: 8px; margin-top: 32px; }
    .page-link { padding: 10px 16px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); text-decoration: none; transition: all 0.2s; }
    .page-link:hover { border-color: #6366f1; color: #6366f1; }
    .page-link.active { background: #6366f1; color: white; border-color: #6366f1; }
    .page-link.disabled { opacity: 0.5; cursor: not-allowed; }
</style>
@endsection

@section('content')
<h1 class="page-title">Properties</h1>

@if(session('success'))
    <div style="padding: 16px; background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.2); border-radius: 10px; color: #22c55e; margin-bottom: 24px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

@if($properties->count() > 0)
    <div class="properties-grid">
        @foreach($properties as $property)
            @php
                $images = is_array($property->images) ? $property->images : json_decode($property->images, true);
                $firstImage = is_array($images) && count($images) > 0 ? $images[0] : 'https://via.placeholder.com/320x220/6366f1/ffffff?text=No+Image';

                $name = is_array($property->name) ? ($property->name['en'] ?? 'N/A') : (json_decode($property->name, true)['en'] ?? $property->name ?? 'N/A');

                $price = is_array($property->price) ? $property->price : json_decode($property->price, true);
                $priceUsd = $price['usd'] ?? 0;

                $rooms = is_array($property->rooms) ? $property->rooms : json_decode($property->rooms, true);
                $bedrooms = $rooms['bedroom']['count'] ?? 0;
                $bathrooms = $rooms['bathroom']['count'] ?? 0;

                $address = is_array($property->address_details) ? $property->address_details : json_decode($property->address_details, true);
                $city = $address['city']['en'] ?? '';
                $district = $address['district']['en'] ?? '';
            @endphp

            <div class="property-card">
                <div class="property-img">
                    <img src="{{ $firstImage }}" alt="{{ $name }}">
                    <div class="property-badge">{{ ucfirst($property->listing_type) }}</div>
                    <div class="property-status {{ $property->status }}">{{ ucfirst($property->status) }}</div>

                    <div class="property-actions">
                        <a href="{{ route('office.property.edit', $property->id) }}" class="action-btn" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('office.property.delete', $property->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this property?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="property-info">
                    <div class="property-price">${{ number_format($priceUsd) }}</div>
                    <div class="property-name">{{ $name }}</div>
                    <div class="property-location">
                        <i class="fas fa-map-marker-alt"></i>
                        {{ $city }}{{ $district ? ', ' . $district : '' }}
                    </div>

                    <div class="property-specs">
                        <div class="spec-item">
                            <i class="fas fa-bed"></i>
                            <span>{{ $bedrooms }}</span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-bath"></i>
                            <span>{{ $bathrooms }}</span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-ruler-combined"></i>
                            <span>{{ $property->area ?? 0 }} m²</span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-eye"></i>
                            <span>{{ $property->views ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    @if($properties->hasPages())
        <div class="pagination">
            @if ($properties->onFirstPage())
                <span class="page-link disabled">Previous</span>
            @else
                <a href="{{ $properties->previousPageUrl() }}" class="page-link">Previous</a>
            @endif

            @foreach ($properties->getUrlRange(1, $properties->lastPage()) as $page => $url)
                <a href="{{ $url }}" class="page-link {{ $page == $properties->currentPage() ? 'active' : '' }}">
                    {{ $page }}
                </a>
            @endforeach

            @if ($properties->hasMorePages())
                <a href="{{ $properties->nextPageUrl() }}" class="page-link">Next</a>
            @else
                <span class="page-link disabled">Next</span>
            @endif
        </div>
    @endif
@else
    <div class="empty-state">
        <i class="fas fa-building"></i>
        <h3>No Properties Yet</h3>
        <p>Start building your property portfolio</p>
        <a href="{{ route('office.property.upload') }}" class="add-btn-large">
            <i class="fas fa-plus"></i> Add Your First Property
        </a>
    </div>
@endif
@endsection
