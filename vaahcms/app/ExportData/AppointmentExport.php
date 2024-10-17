<?php

namespace App\ExportData;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use VaahCms\Modules\Appointment\Models\Appointment;
use VaahCms\Modules\Appointment\Models\Doctor;

class AppointmentExport implements FromCollection, WithHeadings, WithCustomCsvSettings
{

    public function collection()
    {

        return Appointment::with(['doctor', 'patient'])->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'patient_name' => $item->patient ? $item->patient->name : 'N/A',
                'doctor_name' => $item->doctor ? $item->doctor->name : 'N/A',
                'slot_start_time' => $item->slot_start_time,
                'slot_end_time' => $item->slot_end_time,
                'status' => $item->status == 1 ? 'Booked' : 'Canceled',
                'reason'=> $item->reason,

            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Patient Name',
            'Doctor Name',
            'Start Time',
            'End Time',
            'Status',
            'Reason'
        ];
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',
            'enclosure' => '"',
        ];
    }
}
