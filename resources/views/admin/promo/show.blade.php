<!doctype html>
<html>
<head>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin - Promo Offer #{{ $offer->id }}</title>
  @vite(['resources/js/admin.js'])
</head>
<body>
  <div id="app" class="container">
    <h1>Promo Offer #{{ $offer->id }}</h1>

    <div class="mb-3">
      <a href="{{ route('admin.promo.index') }}">Back to offers</a>
      &nbsp;|&nbsp;
      <a href="{{ url('/admin/kpis') }}">KPIs</a>
    </div>

    <h2>Summary</h2>
    <ul>
      <li><strong>Hotel:</strong> #{{ $offer->hotel_id }}</li>
      <li><strong>Mode:</strong> {{ $offer->mode_code }}</li>
      <li><strong>Discount:</strong> {{ number_format($offer->discount_percent, 2) }}%</li>
      <li><strong>Margin before:</strong> {{ number_format($offer->margin_before_percent, 2) }}%</li>
      <li><strong>Margin after:</strong> {{ number_format($offer->margin_after_percent, 2) }}%</li>
      <li><strong>Status:</strong> {{ $offer->is_active ? 'Active' : 'Inactive' }}</li>
      <li><strong>Window:</strong> {{ optional($offer->starts_at)->format('Y-m-d H:i') }} → {{ optional($offer->ends_at)->format('Y-m-d H:i') }}</li>
    </ul>

    <h2>Events</h2>
    @php
      $impressions = $offer->events->where('event_type', 'impression')->count();
      $clicks = $offer->events->where('event_type', 'click')->count();
      $ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0;
    @endphp

    <p>
      <strong>Impressions:</strong> {{ $impressions }}
      &nbsp;|&nbsp;
      <strong>Clicks:</strong> {{ $clicks }}
      &nbsp;|&nbsp;
      <strong>CTR:</strong> {{ $ctr }}%
    </p>

    @if($offer->events->isEmpty())
      <p>No events recorded.</p>
    @else
      <table class="table" style="width:100%; border-collapse:collapse;">
        <thead>
          <tr>
            <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">ID</th>
            <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Type</th>
            <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Session</th>
            <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Time</th>
          </tr>
        </thead>
        <tbody>
          @foreach($offer->events->sortByDesc('id')->take(100) as $event)
            <tr>
              <td style="padding:8px; border-bottom:1px solid #f3f3f3;">{{ $event->id }}</td>
              <td style="padding:8px; border-bottom:1px solid #f3f3f3;">{{ $event->event_type }}</td>
              <td style="padding:8px; border-bottom:1px solid #f3f3f3; color:#666;">{{ $event->session_id }}</td>
              <td style="padding:8px; border-bottom:1px solid #f3f3f3; color:#666;">{{ optional($event->created_at)->format('Y-m-d H:i') }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
</body>
</html>
@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h4 mb-0">Offer #{{ $offer->id }}</h1>
            <div class="text-muted small">Hotel #{{ $offer->hotel_id }} • Mode: {{ $offer->mode_code }}</div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.promo.index') }}">Back to Offers</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="h6">Summary</h2>
                    <dl class="row mb-0">
                        <dt class="col-5">Discount</dt>
                        <dd class="col-7">{{ number_format($offer->discount_percent, 2) }}%</dd>

                        <dt class="col-5">Margin before</dt>
                        <dd class="col-7">{{ number_format($offer->margin_before_percent, 2) }}%</dd>

                        <dt class="col-5">Margin after</dt>
                        <dd class="col-7">{{ number_format($offer->margin_after_percent, 2) }}%</dd>

                        <dt class="col-5">Active</dt>
                        <dd class="col-7">{{ $offer->is_active ? 'Yes' : 'No' }}</dd>

                        <dt class="col-5">Starts</dt>
                        <dd class="col-7">{{ optional($offer->starts_at)->toDayDateTimeString() }}</dd>

                        <dt class="col-5">Ends</dt>
                        <dd class="col-7">{{ optional($offer->ends_at)->toDayDateTimeString() }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="h6">Events</h2>

                    @php
                        $impressions = $offer->events->where('event_type', 'impression')->count();
                        $clicks = $offer->events->where('event_type', 'click')->count();
                        $ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0;
                    @endphp

                    <div class="row text-center mb-3">
                        <div class="col">
                            <div class="text-muted small">Impressions</div>
                            <div class="h5 mb-0">{{ $impressions }}</div>
                        </div>
                        <div class="col">
                            <div class="text-muted small">Clicks</div>
                            <div class="h5 mb-0">{{ $clicks }}</div>
                        </div>
                        <div class="col">
                            <div class="text-muted small">CTR</div>
                            <div class="h5 mb-0">{{ $ctr }}%</div>
                        </div>
                    </div>

                    @if($offer->events->isEmpty())
                        <p class="text-muted mb-0">No events recorded.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Type</th>
                                        <th>Session</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($offer->events->sortByDesc('id')->take(50) as $event)
                                        <tr>
                                            <td>{{ $event->id }}</td>
                                            <td>{{ $event->event_type }}</td>
                                            <td class="text-muted small">{{ $event->session_id }}</td>
                                            <td class="text-muted small">{{ optional($event->created_at)->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
