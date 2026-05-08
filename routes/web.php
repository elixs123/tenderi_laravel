<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Tenders\ListTenders;
use App\Livewire\Admin\CpvManagement;
use App\Livewire\User\TenderProgress;
use App\Livewire\Auth\Login;
use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Livewire\Tenders\MarketRadar;
use App\Notifications\NewTenderDetected;
use App\Models\Procedure;
use App\Models\CpvCode;
use App\Livewire\User\Settings;
use Illuminate\Support\Facades\Notification;

Route::get('/login', Login::class)->name('login')->middleware('guest');

Route::get('/cpv-pregled', function () {
    $kodovi = CpvCode::all();
    
    $output = "<h1>Lista CPV Kodova</h1><hr>";
    foreach ($kodovi as $k) {
        $output .= "<b>{$k->code}</b> - {$k->description}<br> - Root: {$k->root_description}<br>";
    }
    
    return response($output);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', function (Illuminate\Http\Request $request) {
        Illuminate\Support\Facades\Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    })->name('logout');

    Route::get('/', ListTenders::class)->name('home');

    Route::get('/postavke', Settings::class)->name('user.settings');

    Route::get('/tenders', ListTenders::class)->name('tenders.index');

    Route::get('/cpv-kodovi', CpvManagement::class)->name('cpv.management');

    Route::get('/tender-progress/{id}', TenderProgress::class)->name('tender.progress');

    Route::get('/dashboard', DashboardIndex::class)->name('dashboard');

    Route::get('/tenders/announcements', MarketRadar::class)
        ->name('tenders.announcements');
});

Route::get('/test', function () {
    dd(phpinfo());
    $users = DB::connection('pantheon')->table('the_setitem')->limit(10)->get();

    dd($users);
    // $procedure = Procedure::latest()->first() ?? new Procedure([
    //     'id' => 24, // Ovo nam treba za tvoj URL
    //     'name' => 'NABAVKA MATERIJALA ZA VODOVOD I KANALIZACIJU',
    //     'contracting_authority_name' => 'KJKP VIK d.o.o. Sarajevo',
    //     'estimated_value' => 8500.00,
    //     'ejn_id' => '12345-6-7-8/26'
    // ]);

    // $user = \App\Models\User::first();

    // Notification::route('mail', 'elvis.sarajcic@pennyplus.com')->notify(new NewTenderDetected($procedure));

    // return "Mail je poslan! Provjeri Mailtrap (port 2525) ili storage/logs/laravel.log";
});

require __DIR__.'/settings.php';
