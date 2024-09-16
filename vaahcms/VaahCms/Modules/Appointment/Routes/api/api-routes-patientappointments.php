<?php
use VaahCms\Modules\Appointment\Http\Controllers\Backend\PatientAppointmentsController;
/*
 * API url will be: <base-url>/public/api/appointment/patientappointments
 */
Route::group(
    [
        'prefix' => 'appointment/patientappointments',
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', [PatientAppointmentsController::class, 'getAssets'])
        ->name('vh.backend.appointment.api.patientappointments.assets');
    /**
     * Get List
     */
    Route::get('/', [PatientAppointmentsController::class, 'getList'])
        ->name('vh.backend.appointment.api.patientappointments.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', [PatientAppointmentsController::class, 'updateList'])
        ->name('vh.backend.appointment.api.patientappointments.list.update');
    /**
     * Delete List
     */
    Route::delete('/', [PatientAppointmentsController::class, 'deleteList'])
        ->name('vh.backend.appointment.api.patientappointments.list.delete');


    /**
     * Create Item
     */
    Route::post('/', [PatientAppointmentsController::class, 'createItem'])
        ->name('vh.backend.appointment.api.patientappointments.create');
    /**
     * Get Item
     */
    Route::get('/{id}', [PatientAppointmentsController::class, 'getItem'])
        ->name('vh.backend.appointment.api.patientappointments.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', [PatientAppointmentsController::class, 'updateItem'])
        ->name('vh.backend.appointment.api.patientappointments.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', [PatientAppointmentsController::class, 'deleteItem'])
        ->name('vh.backend.appointment.api.patientappointments.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', [PatientAppointmentsController::class, 'listAction'])
        ->name('vh.backend.appointment.api.patientappointments.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', [PatientAppointmentsController::class, 'itemAction'])
        ->name('vh.backend.appointment.api.patientappointments.item.action');



});
