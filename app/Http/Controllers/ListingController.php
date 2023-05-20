<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ListingController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function __construct() {
        $this->authorizeResource(Listing::class, 'listing');
    }

    public function index(Request $request)
    {
        // $filters = $request->only([
        //     'priceFrom', 'priceTo', 'beds', 'baths', 'areaFrom', 'areaTo'
        // ]);

        $userId = Auth::id();
        return inertia(
            'Listing/Index',
            [
                // 'filters' => $filters,
                'listings' => Listing::mostRecent()->where('by_user_id', $userId)->with('car.owner')/*->filter($filters)*/
                ->paginate(12)->withQueryString()
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //$this->authorize('create', Listing::class);
        return inertia('Listing/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->user()->listings()->create(
            $request->validate([
                'car_id' => 'required|integer',
                'inspection_date' => [
                    'required',
                    'date',
                    'after_or_equal:' . date('Y-m-d', strtotime('-1 year')),
                ],
                'expiration_date' => 'required|date_format:Y-m-d|after_or_equal:'.date('Y-m-d', strtotime('+1 year', strtotime($request->inspection_date))),
            ], [
                'car_id.required' => 'Bạn chưa chọn xe được đăng kiểm',
                'inspection_date.required' => 'Bạn chưa chọn ngày thực hiện đăng kiểm',
                'inspection_date.date' => 'Ngày đăng kiểm phải đúng định dạng',
                'inspection_date.after_or_equal' => 'The inspection date must not be before today.',
                'expiration_date.required' => 'Bạn chưa chọn ngày hết hạn đăng kiểm',
                'expiration_date.date' => 'The expiration date must be a valid date.',
                'expiration_date.after_or_equal' => 'Ngày hết hạn đăng kiểm phải là ít nhất 1 năm kể từ ngày thực hiện đăng kiểm',
            ])
        );

        return redirect()->route('listing.index')->with('success', 'Đăng kiểm thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Listing $listing)
    {
        // if (Auth::user()->cannot('view', $listing)) {
        //     abort(403);
        // };
        //$this->authorize('view', $listing);

        $listing->load(['car.owner', 'car.series.brand', 'car.series.country', 'car.province']);

        return inertia(
            'Listing/Show',
            [
                'listing' => $listing
            ]
        );
    }

    public function edit(Listing $listing)
    {
        return inertia(
            'Listing/Edit',
            [
                'listing' => $listing
            ]
        );
    }

    public function update(Request $request, Listing $listing)
    {
        $listing->update(
            // $request->validate([
            //     'beds' => 'required|integer|min:0|max:20',
            //     'baths' => 'required|integer|min:0|max:20',
            //     'area' => 'required|integer|min:20|max:2000',
            //     'city' => 'required',
            //     'code' => 'required',
            //     'street' => 'required',
            //     'street_nr' => 'required|min:1|max:1000',
            //     'price' => 'required|integer|min:1|max:20000000',
            // ])
        );

        return redirect()->route('listing.index')->with('success', 'Listing was changed!');
    }

}
