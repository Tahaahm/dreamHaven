<body>
    @include('layouts.sidebar')

    <div class="container">

        <h1 class="page-title">Agent Details</h1>

        {{-- Agent Card --}}
        <div class="agent-card">

            {{-- Profile & Basic Info --}}
            <div class="agent-header">
                <img src="{{ $agent->profile_image ? asset('property_images/' . $agent->profile_image) : asset('property_images/IMG_0697.JPG') }}" 
                     alt="Agent Profile" class="agent-image">
                <div class="agent-basic-info">
                    <h2>{{ $agent->agent_name }}</h2>
                    <p class="agent-type">{{ $agent->type ?? 'Type not set' }}</p>
                    <span class="agent-status {{ $agent->status === 'active' ? 'active' : 'disabled' }}">
                        {{ ucfirst($agent->status ?? 'Active') }}
                    </span>
                </div>
            </div>

            {{-- Details Grid --}}
            <div class="agent-details">
                <div><strong>Email:</strong> {{ $agent->primary_email ?? '-' }}</div>
                <div><strong>Phone:</strong> {{ $agent->primary_phone ?? '-' }}</div>
                <div><strong>Verified:</strong> {{ $agent->is_verified ? 'Yes' : 'No' }}</div>
                <div><strong>Overall Rating:</strong> {{ number_format($agent->overall_rating, 2) }}</div>
                <div><strong>City:</strong> {{ $agent->city ?? '-' }}</div>
                <div><strong>Years Experience:</strong> {{ $agent->years_experience ?? 0 }}</div>
                <div><strong>License Number:</strong> {{ $agent->license_number ?? '-' }}</div>
                <div><strong>Company:</strong> {{ $agent->company_name ?? '-' }}</div>
            </div>

            {{-- Bio --}}
            @if($agent->agent_bio)
            <div class="agent-bio">
                <h3>About the Agent</h3>
                <p>{{ $agent->agent_bio }}</p>
            </div>
            @endif

            {{-- Actions --}}
            <div class="actions">
                <form action="{{ route('admin.entity.suspend', $agent->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="{{ $agent->status === 'active' ? 'btn-disable' : 'btn-activate' }}">
                        {{ $agent->status === 'active' ? 'Disable' : 'Activate' }}
                    </button>
                </form>

                <form action="{{ route('admin.entity.delete', $agent->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this agent?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-delete">Delete</button>
                </form>

                <a href="{{ route('admin.users') }}" class="btn-back">Back to All Users & Agents</a>
            </div>

        </div>

    </div>

    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f2f8;
            color: #333;
        }

        .container {
            max-width: 900px;
            margin: 50px auto;
            padding: 0 15px;
        }

        .page-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 40px;
            color: #303b97;
        }

        .agent-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            padding: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .agent-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
        }

        .agent-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #303b97;
            margin-bottom: 15px;
        }

        .agent-basic-info h2 {
            margin: 0;
            font-size: 1.8rem;
            color: #303b97;
            text-align: center;
        }

        .agent-type {
            color: #555;
            margin: 5px 0;
            font-style: italic;
        }

        .agent-status {
            font-weight: bold;
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 0.9rem;
        }

        .agent-status.active {
            color: #4CAF50;
            background: #e8f5e9;
        }

        .agent-status.disabled {
            color: #f44336;
            background: #fdecea;
        }

        .agent-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px 40px;
            width: 100%;
            margin-bottom: 30px;
        }

        .agent-bio {
            width: 100%;
            background: #f8f9fc;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .agent-bio h3 {
            margin-top: 0;
            color: #303b97;
        }

        .agent-bio p {
            margin: 10px 0 0 0;
            color: #555;
        }

        .actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .actions form, .actions a {
            display: inline-block;
        }

        button, .btn-back {
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: 0.3s;
        }

        .btn-disable {
            background-color: #f44336;
            color: #fff;
        }

        .btn-activate {
            background-color: #4CAF50;
            color: #fff;
        }

        .btn-delete {
            background-color: #303b97;
            color: #fff;
        }

        .btn-back {
            background-color: #ccc;
            color: #333;
            text-decoration: none;
            text-align: center;
        }

        button:hover, .btn-back:hover {
            opacity: 0.85;
        }

        @media(max-width: 600px) {
            .agent-details {
                grid-template-columns: 1fr;
            }

            .agent-card {
                padding: 20px;
            }
        }
    </style>
</body>
