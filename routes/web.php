<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (session('agent_authenticated')) {
        return redirect('/dashboard');
    }
    return redirect('/login');
});

Route::get('/login', \App\Livewire\Auth\Login::class)->name('login');

Route::middleware(\App\Http\Middleware\AgentAuth::class)->group(function () {
    Route::get('/dashboard', \App\Livewire\Agent\Dashboard::class)->name('dashboard');
    Route::get('/operations', \App\Livewire\Agent\Operations::class)->name('operations');
    Route::get('/operations/{tab}', \App\Livewire\Agent\Operations::class)
        ->name('operations.tab')
        ->where('tab', 'cash-in|cash-out');
    Route::get('/enroll', \App\Livewire\Agent\Enroll::class)->name('enroll');
    Route::get('/transactions', \App\Livewire\Agent\Transactions::class)->name('transactions');
    Route::get('/statement', \App\Livewire\Agent\Statement::class)->name('statement');
    Route::get('/commission', \App\Livewire\Agent\Commission::class)->name('commission');
    Route::get('/profile', \App\Livewire\Agent\Profile::class)->name('profile');
});

Route::post('/logout', function (\App\Contracts\Api\AgentAuthApi $auth) {
    if (session('agent_access_token')) {
        $auth->logout();
    }
    session()->flush();
    return redirect('/login');
})->name('logout');
