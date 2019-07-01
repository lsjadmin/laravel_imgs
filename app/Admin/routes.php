<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->resource('goodsInfo', GoodsController::class);
    $router->resource('orderInfo', OrderController::class);

    $router->resource('wxmessage', WxImageController::class);
    $router->any('/addImage', 'WxImageController@addImage');

    //图片
    $router->resource('img', ImgController::class);
   
});
