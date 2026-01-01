<!doctype html>
<html>
<head>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin - Promo Offers</title>
  @vite(['resources/js/admin.js'])
</head>
<body>
  <div id="app" class="container">
    <h1>Promo Offers</h1>

    <div class="mb-3">
      <a href="{{ url('/admin/kpis') }}">Back to KPIs</a>
      &nbsp;|&nbsp;
      <a href="{{ route('admin.promo.index') }}">Promo</a>
    </div>

    @if($offers->isEmpty())
      <p>No offers yet. Run the demo seeder to generate mock promo offers.</p>
    @else
      <table class="table" style="width:100%; border-collapse:collapse;">
        <thead>
          <tr>
            <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">ID</th>
            <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Hotel</th>
            <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Mode</th>
            <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Discount %</th>
            <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Margin Before %</th>
            <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Margin After %</th>
            <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Status</th>
            <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Window</th>
            <th style="border-bottom:1px solid #ddd; padding:8px;"></th>
          </tr>
        </thead>
        <tbody>
          @foreach($offers as $offer)
            <tr>
              <td style="padding:8px; border-bottom:1px solid #f3f3f3;">{{ $offer->id }}</td>
              <td style="padding:8px; border-bottom:1px solid #f3f3f3;">#{{ $offer->hotel_id }}</td>
              <td style="padding:8px; border-bottom:1px solid #f3f3f3;">{{ $offer->mode_code }}</td>
              <td style="padding:8px; border-bottom:1px solid #f3f3f3;">{{ number_format($offer->discount_percent, 2) }}</td>
              <td style="padding:8px; border-bottom:1px solid #f3f3f3;">{{ number_format($offer->margin_before_percent, 2) }}</td>
              <td style="padding:8px; border-bottom:1px solid #f3f3f3;">{{ number_format($offer->margin_after_percent, 2) }}</td>
              <td style="padding:8px; border-bottom:1px solid #f3f3f3;">{{ $offer->is_active ? 'Active' : 'Inactive' }}</td>
              <td style="padding:8px; border-bottom:1px solid #f3f3f3; color:#666;">
                {{ optional($offer->starts_at)->format('Y-m-d H:i') }} → {{ optional($offer->ends_at)->format('Y-m-d H:i') }}
              </td>
              <td style="padding:8px; border-bottom:1px solid #f3f3f3; text-align:right;">
                <a href="{{ route('admin.promo.offers.show', $offer) }}">View</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>

      <div style="margin-top:12px;">
        {{ $offers->links() }}
      </div>
    @endif
  </div>
</body>
</html>
@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h4 mb-0">Promo Offers</h1>
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.dashboard') }}">Back to Dashboard</a>
    </div>

    <div class="card">
        <div class="card-body">
            @if($offers->isEmpty())
                <p class="text-muted mb-0">No offers yet. Run the demo seeder to generate mock promo offers.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Hotel</th>
                                <th>Mode</th>
                                <th>Discount %</th>
                                <th>Margin Before %</th>
                                <th>Margin After %</th>
                                <th>Status</th>
                                <th>Window</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($offers as $offer)
                                <tr>
                                    <td>{{ $offer->id }}</td>
                                    <td>#{{ $offer->hotel_id }}</td>
                                    <td>{{ $offer->mode_code }}</td>
                                    <td>{{ number_format($offer->discount_percent, 2) }}</td>
                                    <td>{{ number_format($offer->margin_before_percent, 2) }}</td>
                                    <td>{{ number_format($offer->margin_after_percent, 2) }}</td>
                                    <td>
                                        @if($offer->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">
                                        {{ optional($offer->starts_at)->format('Y-m-d H:i') }} → {{ optional($offer->ends_at)->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-primary" href="{{ route('admin.promo.offers.show', $offer) }}">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $offers->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
