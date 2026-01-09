@extends('layouts.office-layout')

@section('title', 'Add Agent - Dream Mulk')
@section('search-placeholder', 'Search agents...')

@section('top-actions')
    <a href="{{ route('office.agents') }}" class="back-btn" style="background: #f8f9fb; color: #6b7280; padding: 11px 22px; border-radius: 8px; border: 1px solid #e8eaed; font-weight: 600; display: flex; align-items: center; gap: 9px; font-size: 14px; text-decoration: none; transition: all 0.2s;">
        <i class="fas fa-arrow-left"></i> Back
    </a>
@endsection

@section('styles')
<style>
    .page-title { font-size: 32px; font-weight: 700; color: var(--text-primary); margin-bottom: 32px; }
    .form-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 32px; margin-bottom: 24px; }
    .form-title { font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 24px; }

    .alert { padding: 16px; border-radius: 8px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; }
    .alert-success { background: rgba(34,197,94,0.1); color: #22c55e; border: 1px solid rgba(34,197,94,0.2); }
    .alert-error { background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid rgba(239,68,68,0.2); }

    .search-box { background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 10px; padding: 14px 20px; font-size: 15px; color: var(--text-primary); width: 100%; transition: all 0.3s; }
    .search-box:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }

    .helper-text { font-size: 13px; color: var(--text-muted); margin-top: 8px; display: flex; align-items: center; gap: 6px; }

    .privacy-notice { background: rgba(99,102,241,0.1); border: 1px solid rgba(99,102,241,0.2); border-radius: 10px; padding: 16px; margin-top: 12px; display: flex; align-items: start; gap: 12px; }
    .privacy-notice i { color: #6366f1; font-size: 20px; margin-top: 2px; }
    .privacy-notice-text { flex: 1; }
    .privacy-notice-text strong { color: var(--text-primary); display: block; margin-bottom: 4px; font-size: 15px; }
    .privacy-notice-text p { color: var(--text-secondary); font-size: 13px; line-height: 1.5; margin: 0; }

    .agents-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; }

    .agent-card { background: var(--bg-main); border: 2px solid var(--border-color); border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 16px; transition: all 0.3s; }
    .agent-card:hover { border-color: #6366f1; transform: translateY(-3px); box-shadow: 0 8px 24px var(--shadow); }

    .agent-avatar-large { width: 70px; height: 70px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 24px; flex-shrink: 0; }

    .agent-info { flex: 1; min-width: 0; }
    .agent-name { font-size: 18px; font-weight: 600; color: var(--text-primary); margin-bottom: 6px; }
    .agent-email { font-size: 14px; color: var(--text-secondary); margin-bottom: 3px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .agent-phone { font-size: 13px; color: var(--text-muted); }

    .btn-add { background: #6366f1; color: white; padding: 10px 20px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 6px; }
    .btn-add:hover { background: #5558e3; transform: scale(1.05); box-shadow: 0 4px 12px rgba(99,102,241,0.3); }

    .btn-added { background: #22c55e; color: white; padding: 10px 20px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: default; display: flex; align-items: center; gap: 6px; }

    .initial-state { text-align: center; padding: 60px 20px; color: var(--text-muted); background: var(--bg-main); border: 2px dashed var(--border-color); border-radius: 12px; }
    .initial-state i { font-size: 48px; margin-bottom: 16px; opacity: 0.3; color: #6366f1; }
    .initial-state h3 { font-size: 18px; margin-bottom: 8px; color: var(--text-primary); font-weight: 600; }
    .initial-state p { font-size: 14px; color: var(--text-secondary); margin: 0; }

    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
    .empty-state i { font-size: 48px; margin-bottom: 16px; opacity: 0.4; }
    .empty-state h3 { font-size: 18px; margin-bottom: 8px; color: var(--text-secondary); font-weight: 600; }
    .empty-state p { font-size: 14px; color: var(--text-muted); margin: 0; }

    .loading { text-align: center; padding: 40px; color: var(--text-muted); }
    .loading i { font-size: 32px; margin-bottom: 12px; }
    .loading p { font-size: 14px; margin: 0; }

    .back-btn:hover { background: #eff3ff !important; color: #6366f1 !important; border-color: #6366f1 !important; }

    .search-count { font-size: 14px; color: var(--text-secondary); margin-bottom: 16px; font-weight: 500; }
</style>
@endsection

@section('content')
<h1 class="page-title">Add Agents to Your Office</h1>

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <div>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    </div>
@endif

<div class="form-card">
    <h2 class="form-title"><i class="fas fa-search"></i> Search Available Agents</h2>
    <input type="text" class="search-box" id="agent-search" placeholder="Search by name, email, phone, or license number..." oninput="searchAgents()">
    <div class="helper-text">
        <i class="fas fa-info-circle"></i>
        <span>Enter at least 3 characters to search for available agents</span>
    </div>
    <div class="privacy-notice">
        <i class="fas fa-shield-alt"></i>
        <div class="privacy-notice-text">
            <strong>Privacy Protection</strong>
            <p>For privacy reasons, agent information is hidden by default. Please search for specific agents by name, email, phone, or license number to view their profiles.</p>
        </div>
    </div>
</div>

<div class="form-card">
    <h2 class="form-title"><i class="fas fa-users"></i> Search Results</h2>
    <div id="search-count" class="search-count" style="display: none;"></div>
    <div id="agents-list">
        <div class="loading" id="loading-state" style="display: none;">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Searching agents...</p>
        </div>

        <div id="agents-grid">
            <div class="initial-state">
                <i class="fas fa-user-shield"></i>
                <h3>Start Your Search</h3>
                <p>Use the search box above to find agents by their name, email, phone, or license number</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let searchTimeout;

function searchAgents() {
    clearTimeout(searchTimeout);

    const searchInput = document.getElementById('agent-search');
    const query = searchInput.value.trim();
    const loadingState = document.getElementById('loading-state');
    const agentsGrid = document.getElementById('agents-grid');
    const searchCount = document.getElementById('search-count');

    console.log('=== SEARCH STARTED ===');
    console.log('Query:', query);
    console.log('Query Length:', query.length);

    // Reset if less than 3 characters
    if (query.length < 3) {
        console.log('Query too short, resetting');
        searchCount.style.display = 'none';
        agentsGrid.innerHTML = `
            <div class="initial-state">
                <i class="fas fa-user-shield"></i>
                <h3>Start Your Search</h3>
                <p>Use the search box above to find agents by their name, email, phone, or license number</p>
            </div>
        `;
        return;
    }

    // Debounce the search
    searchTimeout = setTimeout(() => {
        console.log('Executing search after debounce');

        loadingState.style.display = 'block';
        agentsGrid.style.display = 'none';
        searchCount.style.display = 'none';

        // Build the URL
        const baseUrl = '{{ route("office.agents.search") }}';
        const url = `${baseUrl}?search=${encodeURIComponent(query)}`;

        console.log('Fetch URL:', url);
        console.log('CSRF Token:', '{{ csrf_token() }}');

        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('=== RESPONSE RECEIVED ===');
            console.log('Status:', response.status);
            console.log('Status Text:', response.statusText);
            console.log('Headers:', [...response.headers.entries()]);

            // Clone response to read it twice
            return response.clone().text().then(text => {
                console.log('Raw Response:', text);

                // Try to parse as JSON
                try {
                    const json = JSON.parse(text);
                    console.log('Parsed JSON:', json);

                    if (!response.ok) {
                        throw new Error(json.message || 'Network response was not ok');
                    }

                    return json;
                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                }
            });
        })
        .then(data => {
            console.log('=== PROCESSING DATA ===');
            console.log('Success:', data.success);
            console.log('Agents Count:', data.agents ? data.agents.length : 0);
            console.log('Agents Data:', data.agents);
            console.log('Debug Info:', data.debug);

            loadingState.style.display = 'none';
            agentsGrid.style.display = 'block';

            if (data.success && data.agents && data.agents.length > 0) {
                console.log('Displaying', data.agents.length, 'agents');

                // Show count
                searchCount.style.display = 'block';
                searchCount.innerHTML = `<i class="fas fa-check-circle"></i> Found ${data.agents.length} agent${data.agents.length > 1 ? 's' : ''}`;

                // Display agents
                const agentsHtml = data.agents.map(agent => {
                    console.log('Rendering agent:', agent);
                    return `
                        <div class="agent-card" data-agent-id="${agent.id}">
                            <div class="agent-avatar-large">
                                ${agent.agent_name ? agent.agent_name.charAt(0).toUpperCase() : 'A'}
                            </div>
                            <div class="agent-info">
                                <div class="agent-name">${escapeHtml(agent.agent_name || 'Unknown Agent')}</div>
                                <div class="agent-email">${escapeHtml(agent.primary_email || 'No email')}</div>
                                <div class="agent-phone">${escapeHtml(agent.primary_phone || 'No phone')}</div>
                            </div>
                            <form action="{{ route('office.agents.store') }}" method="POST" onsubmit="return handleAddAgent(event, '${agent.id}')">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="agent_id" value="${agent.id}">
                                <button type="submit" class="btn-add">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </form>
                        </div>
                    `;
                }).join('');

                agentsGrid.innerHTML = '<div class="agents-grid">' + agentsHtml + '</div>';
                console.log('Agents rendered successfully');
            } else {
                console.log('No agents found or unsuccessful response');
                searchCount.style.display = 'none';
                agentsGrid.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <h3>No Agents Found</h3>
                        <p>No agents match your search criteria. Try different keywords or check spelling.</p>
                        ${data.debug ? `<p style="font-size: 12px; color: #999;">Debug: Total agents: ${data.debug.total_agents}, Available: ${data.debug.available_agents}</p>` : ''}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('=== ERROR OCCURRED ===');
            console.error('Error:', error);
            console.error('Error Message:', error.message);
            console.error('Error Stack:', error.stack);

            loadingState.style.display = 'none';
            agentsGrid.style.display = 'block';
            searchCount.style.display = 'none';
            agentsGrid.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Search Error</h3>
                    <p>${escapeHtml(error.message || 'Failed to search agents. Please check your connection and try again.')}</p>
                    <p style="font-size: 12px; color: #ef4444; margin-top: 10px;">Check browser console for details</p>
                </div>
            `;
        });
    }, 400);
}


function handleAddAgent(event, agentId) {
    const confirmed = confirm('Are you sure you want to add this agent to your office?');
    if (confirmed) {
        const card = document.querySelector(`[data-agent-id="${agentId}"]`);
        if (card) {
            const btn = card.querySelector('button[type="submit"]');
            if (btn) {
                btn.className = 'btn-added';
                btn.innerHTML = '<i class="fas fa-check"></i> Added';
                btn.disabled = true;
            }
        }
    }
    return confirmed;
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}
</script>
@endsection
