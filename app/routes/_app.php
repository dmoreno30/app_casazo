<?php

app()->get('/', function () {
    response()->json(['message' => 'Congrats!! You\'re on Leaf API']);
});
//app()->post('/getData', 'GetDataController@index');
app()->post('/getDataEvent', 'getDataEventController@index');
app()->post('/getDataContact', 'getDataContactController@index');
app()->post('/getDealOrder', 'getDataDealOrderController@index');
app()->post('/getDealQuote', 'getDataDealQuoteController@index');
