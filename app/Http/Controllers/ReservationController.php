<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * 予約一覧ページ
     */
    public function index()
    {
        if (!Auth::user()->subscribed('premium_plan')) {
            return redirect()->route('subscription.create');
        }

        $reservations = Reservation::where('user_id', Auth::id())
            ->orderBy('reserved_datetime', 'desc')
            ->paginate(15);

        return view('reservations.index', compact('reservations'));
    }

    /**
     * 予約ページ
     */
    public function create(Restaurant $restaurant)
    {
        if (!Auth::user()->subscribed('premium_plan')) {
            return redirect()->route('subscription.create');
        }

        return view('reservations.create', compact('restaurant'));
    }

    /** 
     * 予約機能
     */
    public function store(Request $request)
    {
        if (!Auth::user()->subscribed('premium_plan')) {
            return redirect()->route('subscription.create');
        }

        $request->validate([
            'reservation_date' => ['required', 'date_format:Y-m-d'],
            'reservation_time' => ['required', 'date_format:H:i'],
            'number_of_people' => ['required', 'integer', 'between:1,50'],
        ]);

        $reservation = new Reservation();
        $reservation->user_id = Auth::id();
        $reservation->restaurant_id = $request->input('restaurant_id');
        $reservation->number_of_people = $request->input('number_of_people');
        $reservation->reserved_datetime = $request->input('reservation_date') . ' ' . $request->input('reservation_time');
        $reservation->save();

        return redirect()->route('reservations.index')
            ->with('flash_message', '予約が完了しました。');
    }

    /** 
     * 予約キャンセル機能
     */
    public function destroy(Reservation $reservation)
    {
        if (!Auth::user()->subscribed('premium_plan')) {
            return redirect()->route('subscription.create');
        }
        
        if ($reservation->user_id !== Auth::id()) {
            return redirect()->route('reservations.index')
                ->with('error_message', '不正なアクセスです。');
        }

        $reservation->delete();

        return redirect()->route('reservations.index')
            ->with('flash_message', '予約をキャンセルしました。');
    }
}
