<?php

declare(strict_types=1);


use think\facade\Route;


Route::group('/:v/', function () {
    Route::rule('index', '/:v.Index/index', 'GET');
    Route::rule('detail', '/:v.Index/detail', 'GET');
    Route::rule('ranking', '/:v.Index/ranking', 'GET');
    Route::rule('category', '/:v.Index/category', 'GET');
    Route::rule('search', '/:v.Index/search', 'GET');
    Route::rule('keyword', '/:v.Index/search_keyword', 'GET');
    // 获取记录
    Route::rule('record', '/:v.Index/record', 'GET');
    // 记录分数
    Route::rule('record', '/:v.Index/recordQuestion', 'POST');
    Route::rule('search', '/:v.Index/recordSearch', 'POST');
    Route::rule('share', '/:v.Index/share', 'POST');
    Route::rule('register', '/:v.Index/register', 'POST');
    Route::rule('user', '/:v.Index/updateUserInfo', 'POST');
    Route::rule('user', '/:v.Index/user', 'GET');
    Route::rule('behavior', '/:v.Index/user_behavior', 'POST');
    Route::rule('ad', '/:v.Index/saveAd', 'POST');
});
