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
                'Patient' => $item->patient ? $item->patient->name : 'N/A',
                'Doctor' => $item->doctor ? $item->doctor->name : 'N/A',
                'Email' => $item->doctor ? $item->doctor->email : 'N/A',
                'Specialization' => $item->doctor ? $item->doctor->specialization : 'N/A',
                'Date' => $item->date,
                'slot_start_time' => $item->slot_start_time,
                'status' => $item->status == 1 ? 'Booked' : 'Canceled',
                'reason'=> $item->reason,

            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Patient',
            'Doctor',
            'Email',
            'Specialization',
            'Date',
            'slot_start_time',
            'Status',
            'Reason'
        ];
    }

    /**
     * Custom CSV settings to remove double quotes.
     */
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',
            'enclosure' => '',
        ];
    }
}
