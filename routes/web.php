<?php
use App\Http\Controllers\PointTrackerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/point-tracker', [PointTrackerController::class, 'index'])->name('point-tracker.index');
Route::post('/point-tracker', [PointTrackerController::class, 'store'])->name('point-tracker.store');
Route::put('/point-tracker/{pointEntry}', [PointTrackerController::class, 'update'])->name('point-tracker.update');
Route::delete('/point-tracker/{pointEntry}', [PointTrackerController::class, 'destroy'])->name('point-tracker.destroy');
Route::get('/point-tracker/report', [PointTrackerController::class, 'report'])->name('point-tracker.report');
Route::get('/point-tracker/report/export-pdf', [PointTrackerController::class, 'exportPdf'])->name('point-tracker.export-pdf');
Route::get('/point-tracker/report/export-excel', [PointTrackerController::class, 'exportExcel'])->name('point-tracker.export-excel');