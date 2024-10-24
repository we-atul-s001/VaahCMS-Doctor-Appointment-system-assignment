<?php

namespace App\ExportData;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use VaahCms\Modules\Appointment\Models\Doctor;

class DoctorExport implements FromCollection, WithHeadings, WithCustomCsvSettings
{
    protected $selected_ids;

    public function __construct($selected_ids = null)
    {

        if (is_string($selected_ids)) {

            $this->selected_ids = explode(',', $selected_ids);
        } elseif (is_array($selected_ids)) {
            $this->selected_ids = $selected_ids;
        } else {

            $this->selected_ids = [];
        }
    }

    public function collection()
    {
        $query = Doctor::query();

        if (!empty($this->selected_ids)) {
            $query->whereIn('id', $this->selected_ids);
        }

        return $query->get()->map(function ($item) {
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
