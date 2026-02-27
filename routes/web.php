<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FyjtFormController;
use App\Http\Controllers\FyjtCaptchaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [FyjtFormController::class, 'index'])->name('fyjt.form');

// 验证 token + captcha（第一步）
Route::post('/verify', [FyjtFormController::class, 'verify'])->name('fyjt.verify');

// 第二步页面（填写剩余科目并最终提交），受中间件保护，必须先通过第一步
Route::get('/step2', [FyjtFormController::class, 'step2'])->name('fyjt.step2')->middleware('fyjt.step1');

// 最终提交，受中间件保护
Route::post('/submit', [FyjtFormController::class, 'submit'])->name('fyjt.submit')->middleware('fyjt.step1');

Route::get('/captcha', [FyjtCaptchaController::class, 'image'])->name('fyjt.captcha');

//Route::get('/', function () {
//    return view('welcome');
//});
