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
                        <td style="font-weight: bold;">Full Name:</td>
                        <td>
                            @if($therapist->name_prefix)
                            {{ $therapist->name_prefix }}
                            @endif
                            {{ $therapist->name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Title:</td>
                        <td>{{ $therapist->title ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Location:</td>
                        <td>
                            @if($therapist->city){{ $therapist->city }}@endif
                            @if($therapist->state && is_array($therapist->state) && count($therapist->state) > 0 && $therapist->city), @endif
                            @if($therapist->state && is_array($therapist->state) && count($therapist->state) > 0){{ implode(', ', $therapist->state) }}@endif
                            @if($therapist->country && ($therapist->city || ($therapist->state && is_array($therapist->state) && count($therapist->state) > 0))), @endif
                            @if($therapist->country){{ $therapist->country }}@endif
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Gender:</td>
                        <td>
                            @if($therapist->gender)
                            <span class="badge @if($therapist->gender == 'Male') badge-primary @elseif($therapist->gender == 'Female') badge-danger @else badge-secondary @endif">
                                {{ $therapist->gender }}
                            </span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Experience:</td>
                        <td>
                            @if($therapist->experience_duration)
                            {{ $therapist->experience_duration }} years
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Phone:</td>
                        <td>{{ $therapist->phone_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Email:</td>
                        <td>
                            @if($therapist->email)
                            <a href="mailto:{{ $therapist->email }}">{{ $therapist->email }}</a>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Website:</td>
                        <td>
                            @if($therapist->link_to_website)
                            <a href="{{ $therapist->link_to_website }}" target="_blank">{{ $therapist->link_to_website }}</a>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Online Offered:</td>
                        <td>
                            @if($therapist->online_offered && is_array($therapist->online_offered) && count($therapist->online_offered) > 0)
                            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                @foreach($therapist->online_offered as $service)
                                <span class="badge badge-success">{{ $service }}</span>
                                @endforeach
                            </div>
                            @else
                            <span class="text-muted">No online services</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Source:</td>
                        <td><span class="badge badge-warning">{{ $therapist->source ?? 'Unknown' }}</span></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Created:</td>
                        <td>{{ $therapist->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Languages -->
        @if($therapist->languages && is_array($therapist->languages) && count($therapist->languages) > 0)
        <div class="card" style="margin-top: 15px;">
            <div class="card-header">
                <h4>Languages</h4>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    @foreach($therapist->languages as $language)
                    <span class="badge badge-dark">{{ $language }}</span>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Services Offered -->
        @if($therapist->services_offered)
        <div class="card" style="margin-top: 15px;">
            <div class="card-header">
                <h4>Services Offered</h4>
            </div>
            <div class="card-body">
                <span class="badge badge-info">{{ $therapist->services_offered }}</span>
            </div>
        </div>
        @endif

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

        <!-- Clinical Approaches -->
        @if($therapist->clinnical_approaches && is_array($therapist->clinnical_approaches) && count($therapist->clinnical_approaches) > 0)
        <div class="card" style="margin-top: 15px;">
            <div class="card-header">
                <h4>Clinical Approaches</h4>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    @foreach($therapist->clinnical_approaches as $approach)
                    <span class="badge badge-warning">{{ $approach }}</span>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Payment Methods -->
        @if($therapist->payment_method && is_array($therapist->payment_method) && count($therapist->payment_method) > 0)
        <div class="card" style="margin-top: 15px;">
            <div class="card-header">
                <h4>Payment Methods</h4>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    @foreach($therapist->payment_method as $method)
                    <span class="badge badge-info">{{ $method }}</span>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Biography/Description -->
        @if($therapist->about_1)
        <div class="card" style="margin-top: 15px;">
            <div class="card-header">
                <h4>About</h4>
            </div>
            <div class="card-body">
                <p style="line-height: 1.6; margin: 0;">{{ $therapist->about_1 }}</p>
            </div>
        </div>
        @endif

        <!-- Education & Credentials -->
        @if($therapist->education || $therapist->license)
        <div class="card" style="margin-top: 15px;">
            <div class="card-header">
                <h4>Education & Credentials</h4>
            </div>
            <div class="card-body">
                @if($therapist->education)
                <div style="margin-bottom: 15px;">
                    <h6 style="font-weight: bold; margin-bottom: 8px;">Education:</h6>
                    <p style="margin: 0;">{{ $therapist->education }}</p>
                </div>
                @endif
                @if($therapist->license)
                <div>
                    <h6 style="font-weight: bold; margin-bottom: 8px;">License:</h6>
                    <p style="margin: 0;">{{ $therapist->license }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection