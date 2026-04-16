<?php

use Illuminate\Support\Facades\Route;
use App\Events\TestEvent;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-broadcast', function () {
    $data = ['message' => 'Hello, this is a test event!'];
    $userId = 1; // Substitua pelo ID do usuário que deve receber o evento

    broadcast(new TestEvent($data, $userId));

    return 'Evento disparado!';
});
