<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Detail</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f6f8; margin:0; padding:0; }
        .container { max-width: 900px; margin: 50px auto; padding: 30px; background:#fff; border-radius:10px; box-shadow:0 2px 15px rgba(0,0,0,0.05); }
        h1 { text-align:center; color:#333; margin-bottom: 25px; }
        .field { margin-bottom:15px; }
        .field label { font-weight:bold; color:#555; display:block; margin-bottom:5px; }
        .field span { color:#333; word-break: break-word; }
        .actions { margin-top:30px; text-align:center; }
        .actions a, .actions form { display:inline-block; margin:0 10px; }
        .btn { padding:8px 18px; border-radius:5px; border:none; cursor:pointer; font-size:14px; color:#fff; text-decoration:none; }
        .btn-back { background:#6c757d; }
        .btn-suspend { background:#ffc107; }
        .btn-delete { background:#dc3545; }
        .section-title { font-size:18px; font-weight:bold; margin-top:20px; color:#444; border-bottom:1px solid #ddd; padding-bottom:5px; }
    </style>
</head>
<body>
    @include('layouts.sidebar')
    <div class="container">
        <h1>{{ $user->username }}'s Details</h1>

        <div class="section-title">Basic Info</div>
        <div class="field"><label>Username:</label><span>{{ $user->username }}</span></div>
        <div class="field"><label>Email:</label><span>{{ $user->email }}</span></div>
        <div class="field"><label>Role:</label><span>{{ $user->role ?? 'user' }}</span></div>
        <div class="field"><label>Status:</label><span>{{ $user->status ?? 'active' }}</span></div>
        <div class="field"><label>Verified:</label><span>{{ $user->is_verified ? 'Yes' : 'No' }}</span></div>

        <div class="section-title">Contact & Location</div>
        <div class="field"><label>Phone:</label><span>{{ $user->phone ?? '-' }}</span></div>
        <div class="field"><label>Location:</label><span>{{ $user->place ?? '-' }}</span></div>
        <div class="field"><label>Office ID:</label><span>{{ $user->office_id ?? '-' }}</span></div>

        <div class="actions">
            <a href="{{ route('admin.users') }}" class="btn btn-back"><i class="fas fa-arrow-left"></i> Back</a>

        <form action="{{ route('admin.users.suspend', $user->id) }}" method="POST" style="display:inline;">
    @csrf
    <button type="submit" class="btn btn-suspend">
        {{ $user->is_suspended ? 'Activate' : 'Suspend' }}
    </button>
</form>


            <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-delete">Delete</button>
            </form>
        </div>
    </div>
</body>
</html>
