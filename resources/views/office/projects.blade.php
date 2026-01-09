@extends('layouts.office-layout')

@section('title', 'Projects - Dream Mulk')
@section('search-placeholder', 'Search projects...')

@section('top-actions')
<a href="{{ route('office.project.add') }}" class="add-btn"><i class="fas fa-plus"></i> Add Project</a>
@endsection

@section('styles')
<style>
    .projects-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; }
    .project-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; overflow: hidden; transition: all 0.3s; }
    .project-card:hover { transform: translateY(-5px); box-shadow: 0 12px 40px var(--shadow); border-color: rgba(99,102,241,0.4); }
    .project-img { width: 100%; height: 200px; object-fit: cover; }
    .project-content { padding: 24px; }
    .project-title { font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; }
    .project-desc { font-size: 14px; color: var(--text-secondary); margin-bottom: 16px; line-height: 1.6; }
    .project-meta { display: flex; gap: 20px; padding-top: 16px; border-top: 1px solid var(--border-color); }
    .meta-item { font-size: 13px; color: var(--text-muted); }
    .meta-item strong { color: var(--text-primary); font-weight: 600; }
    .project-actions { padding: 20px 24px; background: var(--bg-hover); border-top: 1px solid var(--border-color); display: flex; gap: 8px; }
    .btn-action { flex: 1; padding: 10px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); font-weight: 600; cursor: pointer; transition: all 0.2s; text-align: center; text-decoration: none; display: block; }
    .btn-action:hover { border-color: #6366f1; color: #6366f1; background: rgba(99,102,241,0.08); }
    .empty { text-align: center; padding: 80px 20px; color: var(--text-muted); }
    .empty i { font-size: 64px; margin-bottom: 20px; opacity: 0.4; }
</style>
@endsection

@section('content')
<h1 style="font-size: 32px; font-weight: 700; color: var(--text-primary); margin-bottom: 32px;">Projects</h1>

@if(isset($projects) && $projects->count() > 0)
    <div class="projects-grid">
        @foreach($projects as $project)
        <div class="project-card">
            @php
                $images = is_array($project->images) ? $project->images : json_decode($project->images, true);
                $firstImage = is_array($images) && count($images) > 0 ? $images[0] : 'https://via.placeholder.com/400x200';
            @endphp
            <img src="{{ $firstImage }}" alt="Project" class="project-img">
            <div class="project-content">
                <h3 class="project-title">{{ is_array($project->name) ? ($project->name['en'] ?? 'N/A') : $project->name }}</h3>
                <p class="project-desc">{{ Str::limit(is_array($project->description) ? ($project->description['en'] ?? '') : $project->description, 100) }}</p>
                <div class="project-meta">
                    <div class="meta-item">
                        <strong>{{ $project->properties_count ?? 0 }}</strong> Properties
                    </div>
                    <div class="meta-item">
                        <strong>{{ $project->units ?? 'N/A' }}</strong> Units
                    </div>
                    <div class="meta-item">
                        <strong>{{ ucfirst($project->status ?? 'active') }}</strong>
                    </div>
                </div>
            </div>
            <div class="project-actions">
                <a href="{{ route('office.project.edit', $project->id) }}" class="btn-action"><i class="fas fa-edit"></i> Edit</a>
                <form action="{{ route('office.project.delete', $project->id) }}" method="POST" style="flex: 1;" onsubmit="return confirm('Delete this project?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-action" style="width: 100%;"><i class="fas fa-trash"></i> Delete</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
@else
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 28px;">
        <div class="empty">
            <i class="fas fa-folder"></i>
            <h3>No Projects Yet</h3>
            <p>Start by creating your first project</p>
            <a href="{{ route('office.project.add') }}" class="add-btn" style="margin-top: 20px; display: inline-flex;">
                <i class="fas fa-plus"></i> Create Project
            </a>
        </div>
    </div>
@endif
@endsection
