@extends('layouts.app')

@section('title', 'Therapist Details')

@section('content')
<div class="header-content mb-3">
    <div>
        <h1 class="mb-1">Therapist Details</h1>
    </div>
    <div class="button-group">
        <a href="{{ route('therapists.index') }}" class="btn btn-secondary">Back to List</a>
    </div>
</div>

<div style="display: flex; gap: 20px; margin-bottom: 20px;">
    <div style="flex: 0 0 300px;">
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 20px;">
                @if($therapist->avatar)
                <img src="{{ $therapist->avatar }}" alt="Avatar"
                    style="width: 150px; height: 150px; border-radius: 50%; margin-bottom: 15px; object-fit: cover;"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="avatar-placeholder" style="display: none; width: 150px; height: 150px; margin: 0 auto 15px; font-size: 48px;">
                    {{ substr($therapist->name ?? 'N', 0, 1) }}
                </div>
                @else
                <div class="avatar-placeholder" style="width: 150px; height: 150px; margin: 0 auto 15px; font-size: 48px;">
                    {{ substr($therapist->name ?? 'N', 0, 1) }}
                </div>
                @endif
                <h3 style="margin-bottom: 8px; color: #333;">{{ $therapist->name ?? '' }}</h3>
                <p style="color: #666; margin: 0;">{{ $therapist->title ?? '' }}</p>
            </div>
        </div>
    </div>

    <div style="flex: 1;">
        <div class="card">
            <div class="card-header">
                <h4>Basic Information</h4>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <td style="font-weight: bold; width: 150px;">ID:</td>
                        <td>{{ $therapist->id }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; width: 150px;">Name:</td>
                        <td>{{ $therapist->name ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Title:</td>
                        <td>{{ $therapist->title ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">City:</td>
                        <td>{{ $therapist->city ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Experience:</td>
                        <td>
                            @if($therapist->experience_duration)
                            {{ $therapist->experience_duration }} years
                            @else

                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Source:</td>
                        <td>{{ $therapist->source ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Created:</td>
                        <td>{{ $therapist->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Specialty -->
        @if($therapist->specialty && is_array($therapist->specialty) && count($therapist->specialty) > 0)
        <div class="card" style="margin-top: 15px;">
            <div class="card-header">
                <h4>Specialties</h4>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    @foreach($therapist->specialty as $specialty)
                    <span class="badge badge-primary">{{ $specialty }}</span>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- General Expertise -->
        @if($therapist->general_expertise && is_array($therapist->general_expertise) && count($therapist->general_expertise) > 0)
        <div class="card" style="margin-top: 15px;">
            <div class="card-header">
                <h4>General Expertise</h4>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    @foreach($therapist->general_expertise as $item)
                    <span class="badge badge-success">{{ $item }}</span>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection