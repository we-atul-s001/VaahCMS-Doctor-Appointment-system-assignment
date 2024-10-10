<?php

use VaahCms\Modules\Appointment\Http\Controllers\Backend\DoctorsController;

Route::group(
    [
        'prefix' => 'backend/appointment/doctors',

        'middleware' => ['web', 'has.backend.access'],

],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', [DoctorsController::class, 'getAssets'])
        ->name('vh.backend.appointment.doctors.assets');
    /**
     * Get List
     */
    Route::get('/', [DoctorsController::class, 'getList'])
        ->name('vh.backend.appointment.doctors.list');

    /**
     * Get Doctors Count
     */
    Route::get('/doctor-count', [DoctorsController::class, 'getDoctorsCount'])
        ->name('vh.backend.appointment.doctors.count');

    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', [DoctorsController::class, 'updateList'])
        ->name('vh.backend.appointment.doctors.list.update');
    /**
     * Delete List
     */
    Route::delete('/', [DoctorsController::class, 'deleteList'])
        ->name('vh.backend.appointment.doctors.list.delete');


    /**
     * Fill Form Inputs
     */
    Route::any('/fill', [DoctorsController::class, 'fillItem'])
        ->name('vh.backend.appointment.doctors.fill');

    /**
     * Create Item
     */
    Route::post('/', [DoctorsController::class, 'createItem'])
        ->name('vh.backend.appointment.doctors.create');
    /**
     * Get Item
     */
    Route::get('/{id}', [DoctorsController::class, 'getItem'])
        ->name('vh.backend.appointment.doctors.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', [DoctorsController::class, 'updateItem'])
        ->name('vh.backend.appointment.doctors.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', [DoctorsController::class, 'deleteItem'])
        ->name('vh.backend.appointment.doctors.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', [DoctorsController::class, 'listAction'])
        ->name('vh.backend.appointment.doctors.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', [DoctorsController::class, 'itemAction'])
        ->name('vh.backend.appointment.doctors.item.action');

    //---------------------------------------------------------

    /**
     * Bulk Import
     */
    Route::post('/bulk-import', [DoctorsController::class, 'bulkImport'])
        ->name('vh.backend.appointment.doctors.bulk-import');
});
