<?php

use VaahCms\Modules\Appointment\Http\Controllers\Backend\PatientAppointmentsController;

Route::group(
    [
        'prefix' => 'backend/appointment/patientappointments',
        
        'middleware' => ['web', 'has.backend.access'],
        
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', [PatientAppointmentsController::class, 'getAssets'])
        ->name('vh.backend.appointment.patientappointments.assets');
    /**
     * Get List
     */
    Route::get('/', [PatientAppointmentsController::class, 'getList'])
        ->name('vh.backend.appointment.patientappointments.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', [PatientAppointmentsController::class, 'updateList'])
        ->name('vh.backend.appointment.patientappointments.list.update');
    /**
     * Delete List
     */
    Route::delete('/', [PatientAppointmentsController::class, 'deleteList'])
        ->name('vh.backend.appointment.patientappointments.list.delete');


    /**
     * Fill Form Inputs
     */
    Route::any('/fill', [PatientAppointmentsController::class, 'fillItem'])
        ->name('vh.backend.appointment.patientappointments.fill');

    /**
     * Create Item
     */
    Route::post('/', [PatientAppointmentsController::class, 'createItem'])
        ->name('vh.backend.appointment.patientappointments.create');
    /**
     * Get Item
     */
    Route::get('/{id}', [PatientAppointmentsController::class, 'getItem'])
        ->name('vh.backend.appointment.patientappointments.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', [PatientAppointmentsController::class, 'updateItem'])
        ->name('vh.backend.appointment.patientappointments.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', [PatientAppointmentsController::class, 'deleteItem'])
        ->name('vh.backend.appointment.patientappointments.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', [PatientAppointmentsController::class, 'listAction'])
        ->name('vh.backend.appointment.patientappointments.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', [PatientAppointmentsController::class, 'itemAction'])
        ->name('vh.backend.appointment.patientappointments.item.action');

    //---------------------------------------------------------

});
