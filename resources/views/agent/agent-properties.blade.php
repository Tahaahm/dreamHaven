@extends('layouts.agent-layout')

@section('title', 'My Properties - Dream Mulk')

@section('styles')
<style>
    .page-title {
        font-size: 32px;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 32px;
    }

    .alert {
        padding: 16px;
        border-radius: 10px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .alert-success {
        background: rgba(34,197,94,0.1);
        color: #22c55e;
        border: 1px solid rgba(34,197,94,0.2);
    }

    .properties-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 24px;
    }

    .property-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        transition: all 0.3s;
    }

    .property-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px rgba(48,59,151,0.15);
    }

    .property-image {
        width: 100%;
        height: 220px;
        background: linear-gradient(135deg, #303b97, #1e2875);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 56px;
        position: relative;
    }

    .property-status {
        position: absolute;
        top: 12px;
        right: 12px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        background: white;
        color: #22c55e;
    }

    .property-status.sold {
        color: #ef4444;
    }

    .property-content {
        padding: 20px;
    }

    .property-title {
        font-size: 18px;
        font-weight: 600;
        color: #1a202c;
        margin-bottom: 8px;
    }

    .property-location {
        font-size: 14px;
        color: #64748b;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .property-price {
        font-size: 22px;
        font-weight: 700;
        color: #303b97;
        margin-bottom: 12px;
    }

    .property-features {
        display: flex;
        gap: 16px;
        padding: 12px 0;
        border-top: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 16px;
        font-size: 13px;
        color: #64748b;
    }

    .feature {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .property-actions {
        display: flex;
        gap: 8px;
    }

    .btn-edit {
        flex: 1;
        padding: 10px;
        background: #303b97;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 14px;
        transition: all 0.3s;
    }

    .btn-edit:hover {
        background: #1e2875;
    }

    .btn-delete {
        padding: 10px 16px;
        background: transparent;
        color: #ef4444;
        border: 1px solid #ef4444;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-delete:hover {
        background: #ef4444;
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 20px 20px;
        background: white;
        border: 2px dashed #e2e8f0;
        border-radius: 14px;
    }

    .empty-state i {
        font-size: 80px;
        color: #cbd5e1;
        opacity: 0.3;
        margin-bottom: 24px;
    }

    .empty-state h3 {
        font-size: 24px;
        color: #64748b;
        margin-bottom: 12px;
    }

    .empty-state p {
        color: #94a3b8;
        margin-bottom: 24px;
        font-size: 16px;
    }

    .add-btn {
        padding: 12px 24px;
        background: #303b97;
        color: white;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }

    .add-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(48,59,151,0.4);
    }
</style>
@endsection

@section('content')
<h1 class="page-title">My Properties</h1>

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if($properties && $properties->count() > 0)
    <div class="properties-grid">
      @foreach($properties as $property)
<div class="property-card">
    @if($property->images && count($property->images) > 0)
        <img src="{{ $property->images[0] }}" alt="{{ $property->name['en'] ?? 'Property' }}" class="property-image" style="object-fit: cover;">
    @else
        <div class="property-image">
            <i class="fas fa-home"></i>
        </div>
    @endif

    <div class="property-status {{ $property->status == 'sold' ? 'sold' : '' }}">
        {{ ucfirst($property->status ?? 'available') }}
    </div>

    <div class="property-content">
        <div class="property-title">{{ $property->name['en'] ?? 'Untitled Property' }}</div>

        <div class="property-location">
            <i class="fas fa-map-marker-alt"></i>
            {{ $property->address_details['city']['en'] ?? 'Unknown' }}, {{ $property->address_details['district']['en'] ?? '' }}
        </div>

        <div class="property-price">
            {{ number_format($property->price['iqd'] ?? 0) }} IQD
        </div>

        <div class="property-features">
            <div class="feature">
                <i class="fas fa-bed"></i> {{ $property->rooms['bedroom']['count'] ?? 0 }}
            </div>
            <div class="feature">
                <i class="fas fa-bath"></i> {{ $property->rooms['bathroom']['count'] ?? 0 }}
            </div>
            <div class="feature">
                <i class="fas fa-ruler-combined"></i> {{ $property->area ?? 0 }}m²
            </div>
        </div>

        <div class="property-actions">
            <a href="{{ route('agent.property.edit', $property->id) }}" class="btn-edit">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form action="{{ route('agent.property.delete', $property->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this property?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-delete">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </div>
</div>
@endforeach
    </div>

    <div style="margin-top: 32px;">
        {{ $properties->links() }}
    </div>
@else
    <div class="empty-state">
        <i class="fas fa-home"></i>
        <h3>No Properties Yet</h3>
        <p>Start building your portfolio by adding your first property</p>
        <a href="{{ route('agent.property.add') }}" class="add-btn">
            <i class="fas fa-plus"></i> Add Your First Property
        </a>
    </div>
@endif
@endsection
