@extends('layouts.app')

@section('content')
<div class="header-content mb-3">
    <div>
        <h1 class="mb-1">All Therapists</h1>
        <div class="header-stats">Total: {{ $therapists->total() ?? 0 }} therapists found</div>
    </div>
    <div class="button-group">
        <a href="{{ route('therapists.export') }}{{ request('search') ? '?search='.request('search') : '' }}" class="btn btn-success">Export CSV</a>
    </div>
</div>

<!-- Search Section -->
<div class="search-section">
    <form method="GET" action="{{ route('therapists.index') }}" class="search-form">
        <input type="text" name="search" class="search-input"
            placeholder="Search by name, title, city, specialty, expertise, source..."
            value="{{ request('search') }}">
        <button type="submit" class="btn btn-primary">Search</button>
        @if(request('search'))
        <a href="{{ route('therapists.index') }}" class="btn btn-secondary">Clear</a>
        @endif
    </form>
    @if(request('search'))
    <div class="search-info">
        Search results for: <strong>"{{ request('search') }}"</strong>
    </div>
    @endif
</div>

@if($therapists->count() > 0)
<!-- Table Section -->
<div class="table-section">
    <div class="table-header">
        <div class="table-title">Therapists List</div>
        <div class="table-info">
            Showing {{ $therapists->firstItem() }} to {{ $therapists->lastItem() }}
            of {{ $therapists->total() }} results
        </div>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th style="width: 60px;">Avatar</th>
                    <th style="width: 150px;">Name</th>
                    <th style="width: 180px;">Title</th>
                    <th style="width: 100px;">City</th>
                    <th style="width: 80px;">Experience</th>
                    <th style="width: 180px;">Specialty</th>
                    <th style="width: 160px;">Expertise</th>
                    <th style="width: 70px;">Source</th>
                    <th style="width: 80px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($therapists as $therapist)
                <tr>
                    <td>
                        <span style="font-weight: bold; color: #666;">{{ $therapist->id }}</span>
                    </td>
                    <td>
                        @if($therapist->avatar)
                        <img src="{{ $therapist->avatar }}" alt="Avatar" class="avatar"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="avatar-placeholder" style="display: none;">
                            {{ substr($therapist->name ?? 'N', 0, 1) }}
                        </div>
                        @else
                        <div class="avatar-placeholder">
                            {{ substr($therapist->name ?? 'N', 0, 1) }}
                        </div>
                        @endif
                    </td>
                    <td>
                        <div class="fw-bold">{{ $therapist->name ?? '' }}</div>
                        @if($therapist->avatar_list)
                        <div style="font-size: 11px; color: #6c757d; margin-top: 2px;">
                            {{ Str::limit(basename($therapist->avatar_list), 15) }}
                        </div>
                        @endif
                    </td>
                    <td>
                        <div style="word-wrap: break-word;">{{ $therapist->title ?? '' }}</div>
                    </td>
                    <td>
                        @if($therapist->city)
                        <span class="badge badge-light">{{ $therapist->city }}</span>
                        @else
                        <span class="text-muted"></span>
                        @endif
                    </td>
                    <td>
                        @if($therapist->experience_duration)
                        <span class="badge badge-primary">{{ $therapist->experience_duration }} years</span>
                        @else
                        <span class="text-muted"></span>
                        @endif
                    </td>
                    <td>
                        @if($therapist->specialty && is_array($therapist->specialty))
                        <div class="d-flex flex-wrap gap-1">
                            @foreach(array_slice($therapist->specialty, 0, 2) as $spec)
                            <span class="badge badge-info">{{ Str::limit($spec, 12) }}</span>
                            @endforeach
                            @if(count($therapist->specialty) > 2)
                            <span class="badge badge-secondary" title="{{ implode(', ', array_slice($therapist->specialty, 2)) }}">
                                +{{ count($therapist->specialty) - 2 }}
                            </span>
                            @endif
                        </div>
                        @else
                        <span class="text-muted"></span>
                        @endif
                    </td>
                    <td>
                        @if($therapist->general_expertise && is_array($therapist->general_expertise) && count($therapist->general_expertise) > 0)
                        <div class="d-flex flex-wrap gap-1">
                            @foreach(array_slice($therapist->general_expertise, 0, 2) as $expertise)
                            <span class="badge badge-success">{{ Str::limit($expertise, 10) }}</span>
                            @endforeach
                            @if(count($therapist->general_expertise) > 2)
                            <span class="badge badge-secondary" title="{{ implode(', ', array_slice($therapist->general_expertise, 2)) }}">
                                +{{ count($therapist->general_expertise) - 2 }}
                            </span>
                            @endif
                        </div>
                        @else
                        <span class="text-muted"></span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-warning">{{ $therapist->source ?? 'Unknown' }}</span>
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('therapists.show', $therapist) }}" class="btn btn-info btn-sm" title="View">View</a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($therapists->hasPages())
    <div class="pagination">
        <div class="pagination-info">
            Showing {{ $therapists->firstItem() }} to {{ $therapists->lastItem() }}
            of {{ $therapists->total() }} results
        </div>
        <div class="pagination-nav">
            {{-- Previous Page Link --}}
            @if($therapists->onFirstPage())
            <button class="btn btn-secondary btn-sm" disabled>← Previous</button>
            @else
            <a href="{{ $therapists->appends(request()->query())->previousPageUrl() }}" class="btn btn-primary btn-sm">← Previous</a>
            @endif

            {{-- Page Numbers --}}
            @php
            $currentPage = $therapists->currentPage();
            $lastPage = $therapists->lastPage();
            $showPages = 5; // Show 5 page numbers at a time

            $startPage = max(1, $currentPage - 2);
            $endPage = min($lastPage, $currentPage + 2);

            // Adjust if we're near the beginning or end
            if ($endPage - $startPage < $showPages - 1) {
                if ($startPage==1) {
                $endPage=min($lastPage, $startPage + $showPages - 1);
                } else {
                $startPage=max(1, $endPage - $showPages + 1);
                }
                }
                @endphp

                {{-- First page if not in range --}}
                @if($startPage> 1)
                <a href="{{ $therapists->appends(request()->query())->url(1) }}" class="btn btn-light btn-sm">1</a>
                @if($startPage > 2)
                <span class="btn btn-light btn-sm" disabled>...</span>
                @endif
                @endif

                {{-- Page Numbers --}}
                @for($page = $startPage; $page <= $endPage; $page++)
                    @if($page==$currentPage)
                    <span class="btn btn-primary btn-sm" style="background: #0066cc; color: white;">{{ $page }}</span>
                    @else
                    <a href="{{ $therapists->appends(request()->query())->url($page) }}" class="btn btn-light btn-sm">{{ $page }}</a>
                    @endif
                    @endfor

                    {{-- Last page if not in range --}}
                    @if($endPage < $lastPage)
                        @if($endPage < $lastPage - 1)
                        <span class="btn btn-light btn-sm" disabled>...</span>
                        @endif
                        <a href="{{ $therapists->appends(request()->query())->url($lastPage) }}" class="btn btn-light btn-sm">{{ $lastPage }}</a>
                        @endif

                        {{-- Next Page Link --}}
                        @if($therapists->hasMorePages())
                        <a href="{{ $therapists->appends(request()->query())->nextPageUrl() }}" class="btn btn-primary btn-sm">Next →</a>
                        @else
                        <button class="btn btn-secondary btn-sm" disabled>Next →</button>
                        @endif
        </div>
    </div>
    @endif
</div>

@else
<!-- Empty State -->
<div class="empty-state">
    <div class="empty-icon">👥</div>
    <h3>No therapists found</h3>
    @if(request('search'))
    <p>No results found for "<strong>{{ request('search') }}</strong>"</p>
    <div style="margin-top: 20px;">
        <a href="{{ route('therapists.index') }}" class="btn btn-primary">View All Therapists</a>
    </div>
    @else
    <p>Get started by viewing therapist data</p>
    <div style="margin-top: 20px;">
        <a href="{{ route('therapists.index') }}?export=csv" class="btn btn-primary">Export Data</a>
    </div>
    @endif
</div>
@endif
@endsection