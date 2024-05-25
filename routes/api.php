<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChapterAttachmentController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\ChapterProgressController;
use App\Http\Controllers\CourseAttachmentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LectureAttachmentController;
use App\Http\Controllers\LectureController;
use App\Http\Controllers\LectureProgressController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebhookController;

Route::group(['middleware' => 'jwt.cookie', 'api'], function ($routes) {
    Route::group(['prefix' => '/user'], function () {
        Route::post('/signup', [AuthController::class, 'userRegister']);
        Route::post('/signin', [AuthController::class, 'userLogin']);
        Route::get('/auth', [UserController::class, 'userProfile']);
        Route::get('/refresh', [AuthController::class, 'refresh']);
        Route::get('/logout', [AuthController::class, 'logout']);
    });
    Route::group(['prefix' => '/admin'], function () {
        Route::post('/courses', [AdminController::class, 'courses']);
        Route::post('/chapter', [AdminController::class, 'chapter']);
        Route::post('/lecture', [AdminController::class, 'lecture']);
        Route::post('/analytics', [AdminController::class, 'analytics']);
        Route::group(['prefix' => '/categories'], function () {
            Route::post('', [AdminController::class, 'categories']);
            Route::post('/add', [AdminController::class, 'addCategory']);
            Route::post('/edit', [AdminController::class, 'editCategory']);
            Route::post('/delete', [AdminController::class, 'deleteCategory']);
        });
        Route::post('/users', [AdminController::class, 'users']);
        Route::post('/subadmins', [AdminController::class, 'subadmins']);
        Route::group(['prefix' => '/applications'], function () {
            Route::post('', [ApplicationController::class, 'index']);
            Route::post('/apply', [ApplicationController::class, 'apply']);
            Route::post('/approve', [ApplicationController::class, 'approve']);
            Route::post('/reject', [ApplicationController::class, 'reject']);
        });
    });
    Route::get('/categories', [CategoryController::class, 'categories']);
    Route::group(['prefix' => '/courses'], function () {
        Route::get('', [CourseController::class, 'courses']);
        Route::post('', [CourseController::class, 'create']);
        Route::post('/search', [CourseController::class, 'search']);
        Route::group(['prefix' => '/{courseId}'], function () {
            Route::get('', [CourseController::class, 'get']);
            Route::patch('', [CourseController::class, 'set']);
            Route::delete('', [CourseController::class, 'delete']);
            Route::group(['prefix' => '/attachments'], function () {
                Route::post('', [CourseAttachmentController::class, 'create']);
                Route::delete('/{attachmentId}', [CourseAttachmentController::class, 'delete']);
            });
            Route::group(['prefix' => '/chapters'], function () {
                Route::post('', [ChapterController::class, 'create']);
                Route::group(['prefix' => '/{chapterId}'], function () {
                    Route::patch('', [ChapterController::class, 'set']);
                    Route::delete('', [ChapterController::class, 'delete']);
                    Route::group(['prefix' => '/attachments'], function () {
                        Route::post('', [ChapterAttachmentController::class, 'create']);
                        Route::delete('/{attachmentId}', [ChapterAttachmentController::class, 'delete']);
                    });
                    Route::group(['prefix' => '/lectures'], function () {
                        Route::post('', [LectureController::class, 'create']);
                        Route::group(['prefix' => '/{lectureId}'], function () {
                            Route::patch('', [LectureController::class, 'set']);
                            Route::delete('', [LectureController::class, 'delete']);
                            Route::group(['prefix' => '/attachments'], function () {
                                Route::post('', [LectureAttachmentController::class, 'create']);
                                Route::delete('/{attachmentId}', [LectureAttachmentController::class, 'delete']);
                            });
                            Route::put('/progress', [LectureProgressController::class, 'index']);
                            Route::patch('/publish', [LectureController::class, 'publish']);
                            Route::patch('/unpublish', [LectureController::class, 'unpublish']);
                        });
                        Route::put('/reorder', [LectureController::class, 'reorder']);
                    });
                    Route::put('/progress', [ChapterProgressController::class, 'index']);
                    Route::patch('/publish', [ChapterController::class, 'publish']);
                    Route::patch('/unpublish', [ChapterController::class, 'unpublish']);
                });
                Route::put('/reorder', [ChapterController::class, 'reorder']);
            });
            Route::post('/checkout', [StripeController::class, 'checkout']);
            Route::patch('/publish', [CourseController::class, 'publish']);
            Route::patch('/unpublish', [CourseController::class, 'unpublish']);
        });
        Route::group(['prefix' => '/user'], function () {
            Route::post('', [UserController::class, 'index']);
            Route::post('/chapter', [UserController::class, 'chapter']);
            Route::post('/course', [UserController::class, 'course']);
            Route::post('/lecture', [UserController::class, 'lecture']);
            Route::post('/progress', [UserController::class, 'progress']);
            Route::post('/purchase', [UserController::class, 'purchase']);
        });
    });
    Route::post('/purchases', [PurchaseController::class, 'purchases']);
    Route::post('/webhook', [WebhookController::class, 'index']);
});
