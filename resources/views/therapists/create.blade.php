@extends('layouts.app')

@section('title', 'Add New Therapist')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Add New Therapist</h2>
    <a href="{{ route('therapists.index') }}" class="btn btn-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('therapists.store') }}">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                            id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                            id="title" name="title" value="{{ old('title') }}">
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
                            id="city" name="city" value="{{ old('city') }}">
                        @error('city')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="experience_duration" class="form-label">Experience (years)</label>
                        <input type="text" class="form-control @error('experience_duration') is-invalid @enderror"
                            id="experience_duration" name="experience_duration" value="{{ old('experience_duration') }}">
                        @error('experience_duration')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="avatar" class="form-label">Avatar URL</label>
                <input type="url" class="form-control @error('avatar') is-invalid @enderror"
                    id="avatar" name="avatar" value="{{ old('avatar') }}"
                    placeholder="https://example.com/avatar.jpg">
                @error('avatar')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="specialty" class="form-label">Specialties</label>
                <input type="text" class="form-control @error('specialty') is-invalid @enderror"
                    id="specialty" name="specialty" value="{{ old('specialty') }}"
                    placeholder="Separate with commas (e.g., Anxiety, Depression, PTSD)">
                @error('specialty')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="general_expertise" class="form-label">General Expertise</label>
                <input type="text" class="form-control @error('general_expertise') is-invalid @enderror"
                    id="general_expertise" name="general_expertise" value="{{ old('general_expertise') }}"
                    placeholder="Separate with commas">
                @error('general_expertise')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="text-end">
                <a href="{{ route('therapists.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Therapist</button>
            </div>
        </form>
    </div>
</div>
@endsection

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('therapists.store') }}">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text"
                            class="form-control @error('name') is-invalid @enderror"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
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
                            value="{{ old('title') }}">
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
                            value="{{ old('city') }}">
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
                            value="{{ old('experience_duration') }}">
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
                    value="{{ old('avatar') }}"
                    placeholder="https://example.com/avatar.jpg">
                @error('avatar')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="specialty" class="form-label">Specialties</label>
                <input type="text"
                    class="form-control @error('specialty') is-invalid @enderror"
                    id="specialty"
                    name="specialty"
                    value="{{ old('specialty') }}"
                    placeholder="Separate multiple specialties with commas (e.g., Anxiety, Depression, PTSD)">
                @error('specialty')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Enter specialties separated by commas</div>
            </div>

            <div class="mb-3">
                <label for="general_expertise" class="form-label">General Expertise</label>
                <input type="text"
                    class="form-control @error('general_expertise') is-invalid @enderror"
                    id="general_expertise"
                    name="general_expertise"
                    value="{{ old('general_expertise') }}"
                    placeholder="Separate multiple areas with commas">
                @error('general_expertise')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Enter areas of expertise separated by commas</div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('therapists.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Therapist
                </button>
            </div>
        </form>
    </div>
</div>
@endsection