<?php
use VaahCms\Modules\Appointment\Http\Controllers\Backend\DoctorsController;
/*
 * API url will be: <base-url>/public/api/appointment/doctors
 */
Route::group(
    [
        'prefix' => 'appointment/doctors',
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', [DoctorsController::class, 'getAssets'])
        ->name('vh.backend.appointment.api.doctors.assets');
    /**
     * Get List
     */
    Route::get('/', [DoctorsController::class, 'getList'])
        ->name('vh.backend.appointment.api.doctors.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', [DoctorsController::class, 'updateList'])
        ->name('vh.backend.appointment.api.doctors.list.update');
    /**
     * Delete List
     */
    Route::delete('/', [DoctorsController::class, 'deleteList'])
        ->name('vh.backend.appointment.api.doctors.list.delete');


    /**
     * Create Item
     */
    Route::post('/', [DoctorsController::class, 'createItem'])
        ->name('vh.backend.appointment.api.doctors.create');
    /**
     * Get Item
     */
    Route::get('/{id}', [DoctorsController::class, 'getItem'])
        ->name('vh.backend.appointment.api.doctors.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', [DoctorsController::class, 'updateItem'])
        ->name('vh.backend.appointment.api.doctors.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', [DoctorsController::class, 'deleteItem'])
        ->name('vh.backend.appointment.api.doctors.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', [DoctorsController::class, 'listAction'])
        ->name('vh.backend.appointment.api.doctors.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', [DoctorsController::class, 'itemAction'])
        ->name('vh.backend.appointment.api.doctors.item.action');



});
