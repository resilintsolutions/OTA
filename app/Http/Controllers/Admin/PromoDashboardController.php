<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoOffer;
use Illuminate\Http\Request;

final class PromoDashboardController extends Controller
{
    public function index(Request $request)
    {
        $offers = PromoOffer::query()
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.promo.index', [
            'offers' => $offers,
        ]);
    }

    public function show(PromoOffer $offer)
    {
        $offer->load('events');

        return view('admin.promo.show', [
            'offer' => $offer,
        ]);
    }
}
