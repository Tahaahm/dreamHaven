<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Users List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f6f8; margin:0; padding:0; }
        .container { max-width: 1200px; margin: 50px auto; padding: 20px; }
        h1 { text-align: center; margin-bottom: 20px; color:#333; }

        /* Search Bar */
        .search-box { text-align: center; margin-bottom: 30px; }
        .search-box input { width: 50%; padding: 10px 15px; border-radius: 8px; border: 1px solid #ccc; font-size: 16px; }

        .user-card { background: #fff; border-radius: 10px; padding: 20px; margin-bottom: 15px;
            display:flex; justify-content: space-between; align-items:center; box-shadow:0 2px 10px rgba(0,0,0,0.05); transition:0.3s;}
        .user-card:hover { box-shadow:0 4px 15px rgba(0,0,0,0.1); }
        .user-info { display:flex; flex-direction: column; }
        .user-info span { color:#555; }
        .actions a, .actions form { display:inline-block; margin-left:10px; }
        .actions button, .actions a { padding:5px 12px; border:none; border-radius:5px; cursor:pointer; font-size:14px; }
        .btn-view { background:#007bff; color:#fff; text-decoration:none; }
        .btn-suspend { background:#ffc107; color:#fff; }
        .btn-delete { background:#dc3545; color:#fff; }

        /* Highlight no results */
        .no-results { text-align:center; color:#999; font-size:16px; margin-top:20px; }
        /* Pagination Styling */

        


.pagination {
    display: inline-flex;
    list-style: none;
    padding: 0;
    margin: 0;
}

.pagination li {
    margin: 0 3px;
}

.pagination li a,
.pagination li span {
    padding: 5px 10px;
    border: 1px solid #ccc;
    text-decoration: none;
    color: #333;
    font-size: 14px;
    border-radius: 3px;
}

.pagination li a:hover {
    background-color: #eee;
}

.pagination li.active span {
    font-weight: bold;
    background-color: #ddd;
}

.pagination li.disabled span {
    color: #aaa;
}






    </style>
</head>
<body>
@include('layouts.sidebar')
<div class="container">
    <h1>All Users & Agents</h1>

    <!-- Filter Dropdown -->
    <div style="text-align:center; margin-bottom: 20px;">
        <form method="GET" id="filterForm">
            <select name="filter" onchange="document.getElementById('filterForm').submit();">
                <option value="" {{ $filter == null ? 'selected' : '' }}>All</option>
                <option value="User" {{ $filter == 'User' ? 'selected' : '' }}>Users</option>
                <option value="Agent" {{ $filter == 'Agent' ? 'selected' : '' }}>Agents</option>
            </select>
        </form>
    </div>


<!-- Search Bar -->
<div class="search-box" style="text-align:center; margin-bottom: 20px;">
    <input type="text" id="searchInput" placeholder="Search by name, email, or ID..." onkeyup="filterEntities()">
</div>


    <div id="entitiesList">
        @foreach($entities as $entity)
            <div class="user-card">
                <div class="user-info">
                    <strong class="username">{{ $entity->name }}</strong>
                    <span class="email">{{ $entity->email }}</span>
                    <span class="id">ID: {{ $entity->id }}</span>
                    <span>Type: {{ $entity->type }}</span>
                    <span>Status: {{ $entity->is_suspended ? 'Suspended' : 'Active' }}</span>
                </div>
                <div class="actions">
                    {{-- View --}}
                    @if($entity->type === 'User')
                        <a href="{{ route('admin.users.show', $entity->id) }}" class="btn-view"><i class="fas fa-eye"></i> View</a>
                    @else
                        <a href="{{ route('admin.agents.show', $entity->id) }}" class="btn-view"><i class="fas fa-eye"></i> View</a>
                    @endif

                    {{-- Suspend / Activate --}}
                    <form action="{{ route('admin.entity.suspend', ['type'=>strtolower($entity->type), 'id'=>$entity->id]) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-suspend">
                            {{ $entity->is_suspended ? 'Activate' : 'Suspend' }}
                        </button>
                    </form>

                    {{-- Delete --}}
                    <form action="{{ route('admin.entity.delete', ['type'=>strtolower($entity->type), 'id'=>$entity->id]) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-delete">Delete</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
<!-- Basic Pagination -->
<div class="pagination-wrapper" style="text-align:center; margin:20px 0;">
    {{ $entities->links('pagination::simple-default') }}
</div>




</div>
<script>
function filterEntities() {
    let input = document.getElementById('searchInput').value.toLowerCase();
    let entities = document.querySelectorAll('#entitiesList .user-card');
    let anyVisible = false;

    entities.forEach(function(entity) {
        let name = entity.querySelector('.username').textContent.toLowerCase();
        let email = entity.querySelector('.email').textContent.toLowerCase();
        let id = entity.querySelector('.id').textContent.toLowerCase();

        if(name.includes(input) || email.includes(input) || id.includes(input)) {
            entity.style.display = 'flex';
            anyVisible = true;
        } else {
            entity.style.display = 'none';
        }
    });

    // Show "no results" if nothing matches
    const noResults = document.getElementById('noResults');
    if(noResults) {
        noResults.style.display = anyVisible ? 'none' : 'block';
    }
}
</script>

<div id="noResults" class="no-results" style="display:none;">No users or agents found.</div>
</body>


</html>
