<x-app-layout>
    <x-slot name="title">KPIs</x-slot>

    <style>
        .kpi-card {
            background: #C9D0E7;
            border-radius: 20px;
            padding: 20px 24px;
            box-shadow: 0 10px 40px rgba(15, 23, 42, 0.05);
            margin-bottom: 20px;
        }
        .kpi-title {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #111111;
            margin-bottom: 4px;
        }
        .kpi-value {
            font-size: 22px;
            font-weight: 600;
            color: #111827;
        }
        .kpi-sub {
            font-size: 12px;
            color: #6b7280;
        }
        .badge-pill {
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 500;
        }
        .badge-pill-success {
            background: #ecfdf5; color:#15803d; border:1px solid #bbf7d0;
        }
        .badge-pill-warning {
            background: #fffbeb; color:#92400e; border:1px solid #fed7aa;
        }
    </style>
    <div class="mb-3 text-xs text-gray-500">
        Home <span class="mx-1">›</span> KPIs
    </div>
<div class="inv-card">
    <div class="container-fluid py-4">
        {{-- Header + period filter --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            
            <div>
                <div class="inv-header-title">KPIs</div>
                <div class="inv-page-title">Key Performance Indicators</div>
            </div>
            <form method="get" class="d-flex gap-2">
                <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                    <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                </select>
            </form>
        </div>

        {{-- Top row: total bookings, revenue, API health placeholder --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="kpi-card">
                    <div class="kpi-title">Total Bookings</div>
                    <div class="kpi-value">{{ number_format($totalBookings) }}</div>
                    <div class="kpi-sub">
                        {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="kpi-card">
                    <div class="kpi-title">Total Revenue (After Markups)</div>
                    <div class="kpi-value">
                        {{ number_format($revenue, 2) }}
                    </div>
                    <div class="kpi-sub">
                        {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="kpi-card" style="border-left:4px solid #22c55e;">
                    <div class="kpi-title">API Health</div>
                    <div class="kpi-value text-success">ACTIVE</div>
                    <div class="kpi-sub">Last checked: {{ now()->format('d M Y H:i') }}</div>
                </div>
            </div>
        </div>

        {{-- Second row: mobile vs desktop, new vs returning, active users, average nights --}}
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="kpi-card">
                    <div class="kpi-title">Mobile vs Desktop Traffic</div>
                    <div class="kpi-sub">
                        Mobile: {{ $mobilePct }}% &nbsp;&nbsp; Desktop: {{ $webPct }}%
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-card">
                    <div class="kpi-title">New vs Returning Users</div>
                    <div class="kpi-sub">
                        {{ $newPct }}% New &nbsp;&nbsp; {{ $retPct }}% Returning
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-card">
                    <div class="kpi-title">Active Users</div>
                    <div class="kpi-value">{{ number_format($activeUsers) }}</div>
                    <div class="kpi-sub">{{ $period === 'today' ? 'Today' : 'In selected period' }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-card">
                    <div class="kpi-title">Average Nights Booked</div>
                    <div class="kpi-value">{{ number_format($avgNights, 1) }}</div>
                    <div class="kpi-sub">Across all bookings in this period</div>
                </div>
            </div>
        </div>

        {{-- Third row: average booking value, refund queue, conversion, search volume --}}
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="kpi-card">
                    <div class="kpi-title">Average Booking Value</div>
                    <div class="kpi-value">{{ number_format($avgBookingValue, 2) }}</div>
                    <div class="kpi-sub">Revenue / Bookings</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-card">
                    <div class="kpi-title">Refund Queue / Pending Refunds</div>
                    <div class="kpi-value">{{ $refundPending }}</div>
                    <div class="kpi-sub">Bookings awaiting refund</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-card">
                    <div class="kpi-title">Conversion Rate</div>
                    <div class="kpi-value">{{ $conversionRate }}%</div>
                    <div class="kpi-sub">Bookings / Searches</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-card">
                    <div class="kpi-title">Search Volume</div>
                    <div class="kpi-value">{{ number_format($searchVolume) }}</div>
                    <div class="kpi-sub">Total number of searches</div>
                </div>
            </div>
        </div>

        {{-- Fourth row: booking success rate & cancellation rate --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="kpi-card">
                    <div class="kpi-title">Booking Success Rate</div>
                    <div class="kpi-value">{{ $bookingSuccessRate }}%</div>
                    <div class="kpi-sub">% of booking attempts that succeeded</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="kpi-card">
                    <div class="kpi-title">Cancellation Rate</div>
                    <div class="kpi-value">{{ $cancellationRate }}%</div>
                    <div class="kpi-sub">% of bookings that were cancelled</div>
                </div>
            </div>
        </div>

        {{-- Bottom: Top destinations table --}}
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <div class="kpi-title mb-1">Top Destinations Booked</div>
                    <div class="kpi-sub">Countries with the most bookings in this period</div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr class="text-muted small">
                            <th>Country</th>
                            <th>Total Booked</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topDestinations as $row)
                            <tr>
                                <td>{{ $row->country ?: 'Unknown' }}</td>
                                <td>{{ $row->total_booked }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted small py-3">
                                    No bookings in this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Promo Engine panel --}}
        <div class="kpi-card mt-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <div class="kpi-title mb-1">Promo Engine</div>
                    <div class="kpi-sub">Overview and quick actions (no admin API token needed).</div>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.promo.index') }}">Open Promo</a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success py-2">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger py-2">{{ session('error') }}</div>
            @endif

            <div class="row">
                <div class="col-md-3">
                    <div class="kpi-title">Enabled</div>
                    <div class="kpi-value">{{ $promoSettings->is_enabled ? 'Yes' : 'No' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="kpi-title">Active Offers</div>
                    <div class="kpi-value">{{ number_format($promoActiveOffers) }}</div>
                    <div class="kpi-sub">Total offers: {{ number_format($promoOffersTotal) }}</div>
                </div>
                <div class="col-md-3">
                    <div class="kpi-title">Impressions</div>
                    <div class="kpi-value">{{ number_format($promoImpressions) }}</div>
                </div>
                <div class="col-md-3">
                    <div class="kpi-title">Clicks / CTR</div>
                    <div class="kpi-value">{{ number_format($promoClicks) }} / {{ $promoCtr }}%</div>
                </div>
            </div>

            <hr />

            <form method="post" action="{{ route('admin.promo-engine.recompute') }}" class="row g-2 align-items-end" id="promoRecomputeForm">
                @csrf
                <div class="col-md-4">
                    <label class="form-label small text-muted">Hotel</label>

                    {{-- Visible searchable input --}}
                    <input
                        id="promoHotelSearch"
                        type="text"
                        class="form-control form-control-sm"
                        list="promoHotelsList"
                        placeholder="Search hotel by name (or pick from list)"
                        autocomplete="off"
                        required
                    >

                    {{-- Actual submitted hotel_id --}}
                    <input id="promoHotelId" name="hotel_id" type="hidden" required>

                    <datalist id="promoHotelsList">
                        @foreach($promoHotels as $h)
                            <option value="{{ $h->name }} (#{{ $h->id }})" data-id="{{ $h->id }}">
                                {{ $h->name }} ({{ $h->city }} {{ $h->country }})
                            </option>
                        @endforeach
                    </datalist>

                    <div class="kpi-sub mt-1">Pick a hotel; the ID will be extracted automatically.</div>
                </div>
                <div class="col-md-4">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" value="1" id="forceRecompute" name="force">
                        <label class="form-check-label" for="forceRecompute">Force recompute (deactivate current active offer)</label>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-sm btn-primary" type="submit">Recompute Offer</button>
                </div>
            </form>

            <script>
                (function () {
                    const input = document.getElementById('promoHotelSearch');
                    const hidden = document.getElementById('promoHotelId');
                    const form = document.getElementById('promoRecomputeForm');

                    function extractId(value) {
                        // Expected format: "Hotel Name (#123)"
                        const m = String(value || '').match(/\(#(\d+)\)\s*$/);
                        return m ? parseInt(m[1], 10) : null;
                    }

                    input?.addEventListener('input', function () {
                        const id = extractId(input.value);
                        hidden.value = id ? String(id) : '';
                    });

                    form?.addEventListener('submit', function (e) {
                        const id = extractId(input.value);
                        if (!id) {
                            e.preventDefault();
                            alert('Please select a hotel from the list so we can detect the ID.');
                        }
                        hidden.value = id ? String(id) : '';
                    });
                })();
            </script>
        </div>
    </div>
</div>    
</x-app-layout>
