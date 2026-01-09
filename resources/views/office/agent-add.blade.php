@extends('layouts.office-layout')

@section('title', 'Add Agent - Dream Mulk')

@section('styles')
<style>
    .page-title { font-size: 32px; font-weight: 700; color: var(--text-primary); margin-bottom: 32px; }
    .form-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 32px; margin-bottom: 24px; }
    .search-box { background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 10px; padding: 14px 20px; font-size: 15px; color: var(--text-primary); width: 100%; }
    .search-box:focus { outline: none; border-color: #6366f1; }
    .helper-text { font-size: 13px; color: var(--text-muted); margin-top: 8px; }
    .agents-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; margin-top: 20px; }
    .agent-card { background: var(--bg-main); border: 2px solid var(--border-color); border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 16px; }
    .agent-avatar { width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 20px; }
    .agent-info { flex: 1; }
    .agent-name { font-size: 16px; font-weight: 600; color: var(--text-primary); }
    .agent-email { font-size: 13px; color: var(--text-secondary); }
    .btn-add { background: #6366f1; color: white; padding: 10px 20px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
    .btn-add:hover { background: #5558e3; }
    .loading { text-align: center; padding: 40px; color: var(--text-muted); }
    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
</style>
@endsection

@section('content')
<h1 class="page-title">Add Agents to Your Office</h1>

<div class="form-card">
    <input type="text" class="search-box" id="agent-search" placeholder="Search by name, email, or phone...">
    <div class="helper-text">Enter at least 3 characters to search</div>
</div>

<div class="form-card">
    <div id="loading" class="loading" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i> Searching...
    </div>
    <div id="results"></div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM READY - Starting agent search');

    let searchTimeout;
    const searchInput = document.getElementById('agent-search');
    const loading = document.getElementById('loading');
    const results = document.getElementById('results');

    console.log('Elements:', {
        searchInput: searchInput,
        loading: loading,
        results: results
    });

    if (!searchInput) {
        console.error('Search input not found!');
        return;
    }

    searchInput.addEventListener('input', function() {
        console.log('Input event triggered');
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        console.log('Query:', query);

        if (query.length < 3) {
            results.innerHTML = '<div class="empty-state">Type at least 3 characters to search</div>';
            return;
        }

        searchTimeout = setTimeout(function() {
            console.log('Starting search for:', query);
            loading.style.display = 'block';
            results.innerHTML = '';

            const url = '/office/agents/search?search=' + encodeURIComponent(query);
            console.log('Fetching:', url);

            fetch(url, {
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
    }
})
.then(function(response) {
    console.log('✅ Response Status:', response.status);
    console.log('✅ Response OK:', response.ok);
    console.log('✅ Response Headers:', response.headers);

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }
    return response.json();
})
.then(function(data) {
    console.log('✅ Full Response Data:', data);
    loading.style.display = 'none';

    if (data.success && data.agents && data.agents.length > 0) {
        console.log('✅ Found', data.agents.length, 'agents');
        // ... rest of code
    } else {
        console.log('⚠️ No agents found or data.success is false');
        console.log('⚠️ data.success:', data.success);
        console.log('⚠️ data.agents:', data.agents);
        results.innerHTML = '<div class="empty-state"><i class="fas fa-search"></i><br>No agents found</div>';
    }
})
.catch(function(error) {
    console.error('❌ Fetch error:', error);
    console.error('❌ Error stack:', error.stack);
    loading.style.display = 'none';
    results.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><br>Search failed: ' + error.message + '</div>';
});
        }, 500);
    });

    console.log('Agent search initialized');
});
</script>
@endsection
