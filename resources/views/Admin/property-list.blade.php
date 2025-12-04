@extends('layouts.app')

@section('content')

<style>
body {
    background: #f0f2f5 !important;
}


.admin-wrapper {
    max-width: 1250px;
    margin: 40px auto;
    padding: 20px;
}

.admin-header {
    margin-bottom: 25px;
}

.admin-header h2 {
    font-weight: 800;
    margin-bottom: 5px;
    color: #111827;
}

.search-bar input {
    width: 280px;
    padding: 12px 15px;
    border-radius: 14px;
    border: 1px solid #d1d5db;
    background: white;
    transition: .2s;
}
.search-bar input:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 12px rgba(37, 99, 235, .3);
}

.property-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.property-card {
    background: white;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    transition: .25s;
    display: flex;
    flex-direction: column;
    position: relative;
}

.property-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.12);
}

.property-img {
    position: relative;
}

.property-img img {
    width: 100%;
    height: 185px;
    object-fit: cover;
    cursor: pointer;
    border-radius: 18px 18px 0 0;
    transition: 0.2s;
}

.property-img img:hover {
    transform: scale(1.03);
}

.photo-count-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: rgba(0,0,0,0.6);
    color: white;
    padding: 5px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
}

.property-info {
    padding: 18px;
    flex-grow: 1;
}

.property-info h4 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: #111827;
}

.property-info small {
    color: #6b7280;
}

.card-menu {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 20;
}

.menu-btn {
    background: rgba(255,255,255,0.9);
    border: none;
    font-size: 22px;
    padding: 4px 9px;
    border-radius: 8px;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.menu-dropdown {
    position: absolute;
    right: 0;
    margin-top: 8px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 6px 15px rgba(0,0,0,.2);
    padding: 10px;
    display: none;
}

.menu-dropdown button {
    background: none;
    border: none;
    padding: 8px 14px;
    width: 100%;
    text-align: left;
    cursor: pointer;
    font-size: 15px;
    color: #dc2626;
}

.menu-dropdown button:hover {
    background: #fee2e2;
}

.pagination-wrapper {
    text-align: center;
    margin: 20px 0;
}

.pagination-wrapper nav a,
.pagination-wrapper nav span {
    border: 1px solid #ccc !important;
    padding: 6px 14px;
    border-radius: 6px;
    margin: 0 4px;
    text-decoration: none;
    color: #333;
}

.pagination-wrapper nav a:hover {
    background: #e6e6e6;
}

.delete-btn {
    background: #ef4444; /* Red */
    color: white;
    border: none;
    border-radius: 6px;
    padding: 6px 12px;
    cursor: pointer;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 6px; /* space between icon and text */
    transition: background 0.2s, transform 0.2s;
    width: 100%;
}

.delete-btn i {
    font-size: 16px;
}

.delete-btn:hover {
    background: #b91c1c; /* Darker red on hover */
    transform: scale(1.05);
}


</style>

@include('layouts.sidebar')

<div class="admin-wrapper">

    <!-- HEADER -->
    <div class="admin-header">
        <h2>Manage Properties</h2>
        <p style="color:#6b7280;">View, search, and manage all property posts.</p>
    </div>

    <!-- SEARCH BAR -->
    <form class="search-bar" method="GET" action="{{ route('admin.properties') }}">
        <input type="text" name="search" placeholder="Search by title or ID..." value="{{ $search ?? '' }}">
        <button class="btn btn-primary" style="margin-left:10px;">Search</button>
    </form>

    <!-- PROPERTY GRID -->
    <div class="property-grid">
   @foreach($properties as $property)
    @php
        // Ensure we have an array
        $images = is_array($property->images) ? $property->images : json_decode($property->images, true);
        $thumb = isset($images[0]) ? $images[0] : asset('property_images/default.jpg');
    @endphp

    <div class="property-card">

        <!-- MENU BUTTON -->
        <div class="card-menu">
            <button class="menu-btn" onclick="toggleMenu('{{ $property->id }}')">⋮</button>
  <div class="menu-dropdown" id="menu-{{ $property->id }}">
    <button class="delete-btn" onclick="deleteProperty('{{ $property->id }}')" title="Delete Property">
        <i class="fas fa-trash-alt"></i> Delete
    </button>
</div>

        </div>

        <!-- IMAGE -->
        <div class="property-img">
            <a href="{{ route('property.PropertyDetail', ['property_id' => $property->id]) }}">
                <img src="{{ $thumb }}" alt="Property thumbnail">
            </a>
        </div>

        <!-- INFO -->
        <div class="property-info">
            <h4>{{ $property->name['en'] ?? 'Untitled' }}</h4>
            <small>ID: {{ $property->id }}</small><br>
            <small>{{ $property->created_at->format('Y-m-d') }}</small>
        </div>

    </div>
@endforeach

    </div>

    <!-- PAGINATION -->
    <div class="pagination-wrapper">
        {{ $properties->links('pagination::simple-default') }}
    </div>

</div>

<script>
function toggleMenu(id) {
    document.querySelectorAll('.menu-dropdown').forEach(el => el.style.display = 'none');
    const menu = document.getElementById('menu-' + id);
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

window.onclick = function(e) {
    if (!e.target.matches('.menu-btn')) {
        document.querySelectorAll('.menu-dropdown').forEach(el => el.style.display = 'none');
    }
}

function deleteProperty(id) {
    if (!confirm("Are you sure you want to delete this property?")) return;

    fetch(`/admin/properties/${id}`, {
        method: "DELETE",
        headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" }
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        location.reload();
    });
}
</script>

@endsection
