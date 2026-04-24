<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;

class UserDashboardController extends Controller
{
    use AuthorizesRequests;

    public function show(User $user): Response
    {
        abort_unless($user->id === auth()->id(), 403);

        return Inertia::render('User/Dashboard', [
            'user' => $user->load('towers.city', 'towers.street'),
            'orders' => $user->orders()->latest()->paginate(10),
        ]);
    }
}
