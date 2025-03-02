<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChapterAttachmentController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\ChapterProgressController;
use App\Http\Controllers\CourseAttachmentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\LectureAttachmentController;
use App\Http\Controllers\LectureController;
use App\Http\Controllers\LectureProgressController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AutofillController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\FileUploadController;

Route::group(['middleware' => 'jwt.cookie', 'api'], function ($routes) {
    Route::group(['prefix' => '/user'], function () {
        Route::post('/signup', [AuthController::class, 'register']);
        Route::post('/signin', [AuthController::class, 'login']);
        Route::get('/oauth-register', [AuthController::class, 'oauth_register']);
        Route::get('/oauth-login', [AuthController::class, 'oauth_login']);
        Route::get('/oauth-success', [AuthController::class, 'oauth_success']);
        Route::get('/oauth-fail', [AuthController::class, 'oauth_fail']);
        Route::get('/oauth-me', [AuthController::class, 'oauth_me']);
        Route::get('/oauth-update', [AuthController::class, 'oauth_update']);
        Route::get('/auth', [UserController::class, 'userProfile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::get('/refresh', [AuthController::class, 'refresh']);
        Route::get('/logout', [AuthController::class, 'logout']);
    });
    Route::group(['prefix' => '/admin'], function () {
        Route::get('/courses', [AdminController::class, 'courses']);
        Route::post('/chapter', [AdminController::class, 'chapter']);
        Route::post('/lecture', [AdminController::class, 'lecture']);
        Route::get('/analytics', [AdminController::class, 'analytics']);
        Route::group(['prefix' => '/categories'], function () {
            Route::get('', [AdminController::class, 'categories']);
            Route::post('/add', [AdminController::class, 'addCategory']);
            Route::put('/edit', [AdminController::class, 'editCategory']);
            Route::delete('/delete', [AdminController::class, 'deleteCategory']);
        });
        Route::group(['prefix' => '/users'], function(){
            Route::get('', [AdminController::class, 'users']);
            Route::delete('', [AdminController::class, 'delete_users']);
        });
        Route::group(['prefix' => '/subadmins'], function() {
            Route::get('', [AdminController::class, 'subadmins']);
            Route::delete('', [AdminController::class, 'delete_subadmins']);
        });
        Route::group(['prefix' => '/applications'], function () {
            Route::get('', [ApplicationController::class, 'index']);
            Route::post('/apply', [ApplicationController::class, 'apply']);
            Route::post('/approve', [ApplicationController::class, 'approve']);
            Route::post('/reject', [ApplicationController::class, 'reject']);
        });
        Route::group(['prefix' => '/notifications'], function() {
            Route::get('', [NotificationController::class, 'all']);
            Route::delete('', [NotificationController::class, 'delete']);
        });
    });
    Route::group(['prefix' => '/notifications'], function() {
        Route::get('', [NotificationController::class, 'read']);
        Route::post('', [NotificationController::class, 'add']);
        Route::delete('', [NotificationController::class, 'delete']);
    });
    Route::group(['prefix' => '/autofill'], function() {
        Route::get('', [AutofillController::class, 'get']);
        Route::get('/all', [AutofillController::class, 'getAll']);
        Route::post('', [AutofillController::class, 'set']);
    });
    Route::group(['prefix' => '/storage'], function(){
        Route::get('{any}', [FileUploadController::class, 'download'])->where('any', '.*');
        Route::post('/upload', [FileUploadController::class, 'upload']);
        Route::delete('{any}', [FileUploadController::class, 'download'])->where('any', '.*');
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
            Route::post('/intent', [StripeController::class, 'intent']);
            Route::post('/completeintent', [StripeController::class, 'completeIntent']);
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
    Route::group(['prefix' => '/companies'], function(){
        Route::get('', [CompanyController::class, 'all']);
        Route::post('', [CompanyController::class, 'create']);
        Route::group(['prefix' => '/{companyId}'], function(){
            Route::get('', [CompanyController::class, 'get']);
            Route::patch('', [CompanyController::class, 'set']);
            Route::delete('', [CompanyController::class, 'delete']);
            Route::group(['prefix' => '/careers'], function(){
                Route::get('', [CareerController::class, 'all']);
                Route::post('', [CareerController::class, 'create']);
                Route::group(['prefix' => '/{careerId}'], function(){
                    Route::get('', [CareerController::class, 'get']);
                    Route::patch('', [CareerController::class, 'set']);
                    Route::delete('', [CareerController::class, 'delete']);
                });        
            });
        });
    });
    Route::group(['prefix' => '/careers'], function(){
        Route::get('', [CareerController::class, 'careers']);
        Route::group(['prefix' => '/{careerId}'], function(){
            Route::get('', [CareerController::class, 'find']);
        });
    });
    Route::group(['prefix' => '/kanban'], function(){
        Route::group(['prefix' => '/columns'], function(){
            Route::get('', [KanbanController::class, 'all_columns']);
            Route::post('', [KanbanController::class, 'create_column']);
            Route::patch('', [KanbanController::class, 'edit_column']);
            Route::delete('', [KanbanController::class, 'delete_column']);
        });
        Route::group(['prefix' => '/rows'], function(){
            Route::get('', [KanbanController::class, 'all_rows']);
            Route::post('', [KanbanController::class, 'create_row']);
            Route::patch('', [KanbanController::class, 'edit_row']);
            Route::delete('', [KanbanController::class, 'delete_row']);
            Route::post('/reorder', [KanbanController::class, 'reorder']);
            Route::post('/bookmark', [KanbanController::class, 'bookmark_row']);
        });
    });
    Route::post('/purchases', [PurchaseController::class, 'purchases']);
    Route::post('/webhook', [WebhookController::class, 'index']);
});
