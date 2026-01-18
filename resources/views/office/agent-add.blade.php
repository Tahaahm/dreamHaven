@extends('layouts.office-layout')

@section('title', 'Add Agent - Dream Mulk')

@section('styles')
<style>
    :root {
        --primary: #6366f1;
        --primary-hover: #4f46e5;
        --bg-card: #ffffff;
        --text-main: #111827;
        --text-sub: #6b7280;
        --border: #e5e7eb;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    /* Page Container */
    .add-agent-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Header Section */
    .header-section {
        text-align: center;
        margin-bottom: 40px;
    }

    .page-title {
        font-size: 32px;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 12px;
        letter-spacing: -0.025em;
    }

    .page-subtitle {
        font-size: 16px;
        color: var(--text-sub);
        max-width: 600px;
        margin: 0 auto;
    }

    /* Search Bar Wrapper */
    .search-wrapper {
        background: var(--bg-card);
        padding: 8px;
        border-radius: 16px;
        box-shadow: var(--shadow-lg);
        max-width: 700px;
        margin: 0 auto 40px;
        border: 1px solid var(--border);
        display: flex;
        align-items: center;
        transition: transform 0.2s;
    }

    .search-wrapper:focus-within {
        transform: translateY(-2px);
        border-color: var(--primary);
        box-shadow: 0 20px 25px -5px rgba(99, 102, 241, 0.15);
    }

    .search-icon {
        width: 60px;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-sub);
        font-size: 20px;
    }

    .search-box {
        flex: 1;
        border: none;
        font-size: 18px;
        color: var(--text-main);
        padding: 16px 0;
        background: transparent;
        outline: none;
    }

    .search-box::placeholder { color: #9ca3af; }

    /* Results Grid */
    .agents-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 24px;
        padding-bottom: 40px;
    }

    /* Agent Card Design */
    .agent-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 20px;
        overflow: hidden;
        transition: all 0.3s ease;
        position: relative;
        display: flex;
        flex-direction: column;
        animation: fadeUp 0.4s ease forwards;
        opacity: 0;
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .agent-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
        border-color: var(--primary);
    }

    .card-header {
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 16px;
        border-bottom: 1px solid #f3f4f6;
        background: linear-gradient(to bottom, #f9fafb, #ffffff);
    }

    .avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #ffffff;
        box-shadow: var(--shadow-sm);
    }

    .avatar-placeholder {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), #818cf8);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: 700;
        border: 3px solid #ffffff;
        box-shadow: var(--shadow-sm);
        text-transform: uppercase;
    }

    .info { flex: 1; overflow: hidden; }

    .name {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .role {
        font-size: 12px;
        font-weight: 600;
        color: var(--primary);
        background: #e0e7ff;
        padding: 2px 10px;
        border-radius: 12px;
        display: inline-block;
    }

    .card-body {
        padding: 20px 24px;
        flex: 1;
    }

    .detail-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
        color: var(--text-sub);
        font-size: 14px;
    }

    .detail-row i { width: 16px; text-align: center; color: #9ca3af; }

    .card-footer {
        padding: 16px 24px;
        background: #f9fafb;
        border-top: 1px solid var(--border);
    }

    .btn-add {
        width: 100%;
        background: var(--text-main);
        color: white;
        padding: 12px;
        border-radius: 12px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-add:hover {
        background: var(--primary);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        transform: translateY(-1px);
    }

    /* States */
    .loading-state {
        text-align: center;
        padding: 40px;
        color: var(--text-sub);
    }

    .spinner {
        font-size: 40px;
        color: var(--primary);
        animation: spin 1s linear infinite;
        margin-bottom: 16px;
    }

    @keyframes spin { 100% { transform: rotate(360deg); } }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: var(--bg-card);
        border-radius: 20px;
        border: 2px dashed var(--border);
        color: var(--text-sub);
    }
    .empty-icon { font-size: 48px; color: #d1d5db; margin-bottom: 16px; }

</style>
@endsection

@section('content')
<div class="add-agent-container">

    <div class="header-section">
        <h1 class="page-title">Expand Your Team</h1>
        <p class="page-subtitle">Search for existing agents by name, email, or phone number to invite them to your office.</p>
    </div>

    <div class="search-wrapper">
        <div class="search-icon">
            <i class="fas fa-search"></i>
        </div>
        <input type="text"
               class="search-box"
               id="agent-search"
               placeholder="Search by name, email, or phone..."
               autocomplete="off">
    </div>

    <div id="results-container">
        <div id="loading" class="loading-state" style="display: none;">
            <i class="fas fa-circle-notch spinner"></i>
            <p>Searching database...</p>
        </div>

        <div id="results" class="agents-grid">
            {{-- Initial State / Empty --}}
            <div class="empty-state" style="grid-column: 1 / -1;">
                <i class="fas fa-users empty-icon"></i>
                <h3>Start Typing to Search</h3>
                <p>Enter at least 3 characters to find agents.</p>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let searchTimeout;
    const searchInput = document.getElementById('agent-search');
    const loading = document.getElementById('loading');
    const results = document.getElementById('results');

    // Helper: Create HTML for Agent Card
    function createAgentCard(agent) {
        let avatarHtml = '';
        if (agent.profile_image) {
            avatarHtml = `<img src="${agent.profile_image}" class="avatar" alt="${agent.agent_name}">`;
        } else {
            const initial = agent.agent_name ? agent.agent_name.charAt(0).toUpperCase() : '?';
            avatarHtml = `<div class="avatar-placeholder">${initial}</div>`;
        }

        return `
            <div class="agent-card">
                <div class="card-header">
                    ${avatarHtml}
                    <div class="info">
                        <div class="name">${agent.agent_name}</div>
                        <div class="role">Agent #${agent.id}</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="detail-row">
                        <i class="fas fa-envelope"></i>
                        <span>${agent.primary_email || 'No email'}</span>
                    </div>
                    <div class="detail-row">
                        <i class="fas fa-phone"></i>
                        <span>${agent.primary_phone || 'No phone'}</span>
                    </div>
                </div>
                <div class="card-footer">
                    <form action="{{ route('office.agents.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="agent_id" value="${agent.id}">
                        <button type="submit" class="btn-add">
                            <i class="fas fa-user-plus"></i> Add to Team
                        </button>
                    </form>
                </div>
            </div>
        `;
    }

    if (!searchInput) return;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 1) { // Allow searching by ID (length 1)
            results.innerHTML = `
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <i class="fas fa-search empty-icon"></i>
                    <h3>Type to Search</h3>
                    <p>Enter Name, Email, or ID to search.</p>
                </div>`;
            return;
        }

        searchTimeout = setTimeout(function() {
            loading.style.display = 'block';
            results.innerHTML = '';

            // ✅ FIX: Changed '?query=' to '?search='
            const url = "{{ route('office.agents.search') }}?search=" + encodeURIComponent(query);

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                loading.style.display = 'none';

                // Handle different response structures
                const agents = data.agents ? data.agents : (Array.isArray(data) ? data : []);

                if (agents.length > 0) {
                    let html = '';
                    agents.forEach(agent => {
                        html += createAgentCard(agent);
                    });
                    results.innerHTML = html;
                } else {
                    results.innerHTML = `
                        <div class="empty-state" style="grid-column: 1 / -1;">
                            <i class="far fa-frown empty-icon"></i>
                            <h3>No Agents Found</h3>
                            <p>We couldn't find anyone matching "${query}".</p>
                        </div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                loading.style.display = 'none';
                results.innerHTML = `
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <i class="fas fa-exclamation-triangle empty-icon" style="color: #ef4444;"></i>
                        <h3>Something went wrong</h3>
                        <p>Please try again later.</p>
                    </div>`;
            });
        }, 500);
    });
});
</script>
@endsection
