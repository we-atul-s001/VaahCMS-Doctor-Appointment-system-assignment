<?php

namespace App\ExportData;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use VaahCms\Modules\Appointment\Models\Doctor;

class DoctorExport implements FromCollection, WithHeadings, WithCustomCsvSettings
{

    public function collection()
    {
        return Doctor::all()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'email' => $item->email,
                'phone' => $item->phone,
                'specialization' => $item->specialization,
                'price_per_session' => $item->price_per_session ?? 0,
                'shift_start_time' => $item->shift_start_time,
                'shift_end_time' => $item->shift_end_time,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'id',
            'name',
            'email',
            'phone',
            'specialization',
            'price',
            'shift_start_time',
            'shift_end_time',
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
