@extends('layouts.app')

@section('title', 'Edit Therapist')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Edit Therapist</h2>
    <div>
        <a href="{{ route('therapists.show', $therapist) }}" class="btn btn-info">View</a>
        <a href="{{ route('therapists.index') }}" class="btn btn-secondary">Back to List</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('therapists.update', $therapist) }}">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                            id="name" name="name" value="{{ old('name', $therapist->name) }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                            id="title" name="title" value="{{ old('title', $therapist->title) }}">
                        @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="city" class="form-label">City</label>
                        <input type="text" class="form-control @error('city') is-invalid @enderror"
                            id="city" name="city" value="{{ old('city', $therapist->city) }}">
                        @error('city')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="experience_duration" class="form-label">Experience (years)</label>
                        <input type="text" class="form-control @error('experience_duration') is-invalid @enderror"
                            id="experience_duration" name="experience_duration"
                            value="{{ old('experience_duration', $therapist->experience_duration) }}">
                        @error('experience_duration')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="avatar" class="form-label">Avatar URL</label>
                <input type="url" class="form-control @error('avatar') is-invalid @enderror"
                    id="avatar" name="avatar" value="{{ old('avatar', $therapist->avatar) }}"
                    placeholder="https://example.com/avatar.jpg">
                @error('avatar')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @if($therapist->avatar)
                <div class="mt-2">
                    <img src="{{ $therapist->avatar }}" alt="Current Avatar"
                        class="rounded" width="100" height="100"
                        onerror="this.style.display='none'">
                </div>
                @endif
            </div>

            <div class="mb-3">
                <label for="specialty" class="form-label">Specialties</label>
                @php
                $currentSpecialties = '';
                if ($therapist->specialty) {
                $specialties = is_array($therapist->specialty)
                ? $therapist->specialty
                : json_decode($therapist->specialty, true) ?? [$therapist->specialty];
                $currentSpecialties = implode(', ', $specialties);
                }
                @endphp
                <input type="text" class="form-control @error('specialty') is-invalid @enderror"
                    id="specialty" name="specialty"
                    value="{{ old('specialty', $currentSpecialties) }}"
                    placeholder="Separate with commas (e.g., Anxiety, Depression, PTSD)">
                @error('specialty')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="general_expertise" class="form-label">General Expertise</label>
                @php
                $currentExpertise = '';
                if ($therapist->general_expertise) {
                $expertise = is_array($therapist->general_expertise)
                ? $therapist->general_expertise
                : json_decode($therapist->general_expertise, true) ?? [];
                $currentExpertise = implode(', ', $expertise);
                }
                @endphp
                <input type="text" class="form-control @error('general_expertise') is-invalid @enderror"
                    id="general_expertise" name="general_expertise"
                    value="{{ old('general_expertise', $currentExpertise) }}"
                    placeholder="Separate with commas">
                @error('general_expertise')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="text-end">
                <a href="{{ route('therapists.show', $therapist) }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Therapist</button>
            </div>
        </form>
    </div>
</div>
@endsection

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('therapists.update', $therapist) }}">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text"
                            class="form-control @error('name') is-invalid @enderror"
                            id="name"
                            name="name"
                            value="{{ old('name', $therapist->name) }}"
                            required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text"
                            class="form-control @error('title') is-invalid @enderror"
                            id="title"
                            name="title"
                            value="{{ old('title', $therapist->title) }}">
                        @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="city" class="form-label">City</label>
                        <input type="text"
                            class="form-control @error('city') is-invalid @enderror"
                            id="city"
                            name="city"
                            value="{{ old('city', $therapist->city) }}">
                        @error('city')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="experience_duration" class="form-label">Experience Duration (years)</label>
                        <input type="text"
                            class="form-control @error('experience_duration') is-invalid @enderror"
                            id="experience_duration"
                            name="experience_duration"
                            value="{{ old('experience_duration', $therapist->experience_duration) }}">
                        @error('experience_duration')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="avatar" class="form-label">Avatar URL</label>
                <input type="url"
                    class="form-control @error('avatar') is-invalid @enderror"
                    id="avatar"
                    name="avatar"
                    value="{{ old('avatar', $therapist->avatar) }}"
                    placeholder="https://example.com/avatar.jpg">
                @error('avatar')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @if($therapist->avatar)
                <div class="mt-2">
                    <img src="{{ $therapist->avatar }}"
                        alt="Current Avatar"
                        class="rounded"
                        width="100"
                        height="100"
                        onerror="this.style.display='none'">
                </div>
                @endif
            </div>

            <div class="mb-3">
                <label for="specialty" class="form-label">Specialties</label>
                @php
                $currentSpecialties = '';
                if ($therapist->specialty) {
                $specialties = is_array($therapist->specialty)
                ? $therapist->specialty
                : json_decode($therapist->specialty, true) ?? [$therapist->specialty];
                $currentSpecialties = implode(', ', $specialties);
                }
                @endphp
                <input type="text"
                    class="form-control @error('specialty') is-invalid @enderror"
                    id="specialty"
                    name="specialty"
                    value="{{ old('specialty', $currentSpecialties) }}"
                    placeholder="Separate multiple specialties with commas (e.g., Anxiety, Depression, PTSD)">
                @error('specialty')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Enter specialties separated by commas</div>
            </div>

            <div class="mb-3">
                <label for="general_expertise" class="form-label">General Expertise</label>
                @php
                $currentExpertise = '';
                if ($therapist->general_expertise) {
                $expertise = is_array($therapist->general_expertise)
                ? $therapist->general_expertise
                : json_decode($therapist->general_expertise, true) ?? [];
                $currentExpertise = implode(', ', $expertise);
                }
                @endphp
                <input type="text"
                    class="form-control @error('general_expertise') is-invalid @enderror"
                    id="general_expertise"
                    name="general_expertise"
                    value="{{ old('general_expertise', $currentExpertise) }}"
                    placeholder="Separate multiple areas with commas">
                @error('general_expertise')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Enter areas of expertise separated by commas</div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('therapists.show', $therapist) }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Therapist
                </button>
            </div>
        </form>
    </div>
</div>
@endsection