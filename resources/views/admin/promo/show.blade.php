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
