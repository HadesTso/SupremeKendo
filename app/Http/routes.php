<?php


Route::get('/', function (){
    return response('GO GO GO');
})->middleware(['web']);