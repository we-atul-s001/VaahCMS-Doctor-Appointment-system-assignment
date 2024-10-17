<?php namespace VaahCms\Modules\Appointment\Models;

use App\ExportData\DoctorExport;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Faker\Factory;
use Maatwebsite\Excel\Facades\Excel;
use mysql_xdevapi\Exception;
use WebReinvent\VaahCms\Libraries\VaahMail;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;

class Doctor extends VaahModel
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;

    //-------------------------------------------------
    protected $table = 'vh_doctors';
    //-------------------------------------------------
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    //-------------------------------------------------
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'email',
        'phone',
        'price_per_session',
        'specialization',
        'shift_start_time',
        'shift_end_time',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
    //-------------------------------------------------
    protected $fill_except = [

    ];

    //-------------------------------------------------
    protected $appends = [
        'appointments_count',
        'appointments_list'
    ];

    //-------------------------------------------------
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id', 'id');
    }

    public function getAppointmentsCountAttribute(): int
    {
        return $this->appointments()->whereNotIn('status', [0, 2])->count();
    }

    public function getAppointmentsListAttribute(): array
    {

        return $this->appointments()->with(['patient', 'doctor'])->get()->map(function ($appointment) {
            return [
                'id' => $appointment->id,
                'patient_name' => $appointment->patient->name,
                'price_per_session' => $appointment->doctor->price_per_session,
                'status' => $appointment->status,
                'date' => $appointment->date,
                'slot_start_time' => $appointment->slot_start_time,
                'reason' => $appointment->reason,
            ];
        })->toArray();
    }

    //-------------------------------------------------
    public static function getUnFillableColumns()
    {
        return [
            'uuid',
            'created_by',
            'updated_by',
            'deleted_by',
        ];
    }

    //-------------------------------------------------
    public static function getFillableColumns()
    {
        $model = new self();
        $except = $model->fill_except;
        $fillable_columns = $model->getFillable();
        $fillable_columns = array_diff(
            $fillable_columns, $except
        );
        return $fillable_columns;
    }


    //-------------------------------------------------
    public static function getEmptyItem()
    {
        $model = new self();
        $fillable = $model->getFillable();
        $empty_item = [];
        foreach ($fillable as $column) {
            $empty_item[$column] = null;
        }

        $empty_item['is_active'] = 1;

        return $empty_item;
    }

    //-------------------------------------------------

    public function createdByUser()
    {
        return $this->belongsTo(User::class,
            'created_by', 'id'
        )->select('id', 'uuid', 'first_name', 'last_name', 'email');
    }

    //-------------------------------------------------
    public function updatedByUser()
    {
        return $this->belongsTo(User::class,
            'updated_by', 'id'
        )->select('id', 'uuid', 'first_name', 'last_name', 'email');
    }

    //-------------------------------------------------
    public function deletedByUser()
    {
        return $this->belongsTo(User::class,
            'deleted_by', 'id'
        )->select('id', 'uuid', 'first_name', 'last_name', 'email');
    }

    //-------------------------------------------------
    public function getTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()
            ->getColumnListing($this->getTable());
    }

    //-------------------------------------------------
    public function scopeExclude($query, $columns)
    {
        return $query->select(array_diff($this->getTableColumns(), $columns));
    }

    //-------------------------------------------------
    public function scopeBetweenDates($query, $from, $to)
    {

        if ($from) {
            $from = Carbon::parse($from)
                ->startOfDay()
                ->toDateTimeString();
        }

        if ($to) {
            $to = Carbon::parse($to)
                ->endOfDay()
                ->toDateTimeString();
        }

        $query->whereBetween('updated_at', [$from, $to]);
    }

    //-------------------------------------------------
    public static function createItem($request)
    {

        $inputs = $request->all();

        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }
        if (isset($inputs['shift_start_time']) && isset($inputs['shift_end_time'])) {
            if (strtotime($inputs['shift_end_time']) <= strtotime($inputs['shift_start_time'])) {
                $response['success'] = false;
                $response['errors'][] = "Shift end time is not valid time";
                return $response;
            }
        }

        if (!isset($inputs['is_active']) || $inputs['is_active'] == 0) {
            $inputs['is_active'] = 1;
        }

        // check if name exist
        $item = self::where('name', $inputs['name'])->withTrashed()->first();

        if ($item) {
            $error_message = "This name is already exist" . ($item->deleted_at ? ' in trash.' : '.');
            $response['success'] = false;
            $response['messages'][] = $error_message;
            return $response;
        }

        // check if slug exist
        $item = self::where('slug', $inputs['slug'])->withTrashed()->first();

        if ($item) {
            $error_message = "This slug is already exist" . ($item->deleted_at ? ' in trash.' : '.');
            $response['success'] = false;
            $response['messages'][] = $error_message;
            return $response;
        }

        $item = new self();
        $item->fill($inputs);
        $item->save();

        $response = self::getItem($item->id);
        $response['messages'][] = trans("vaahcms-general.saved_successfully");
        return $response;

    }

    //-------------------------------------------------

    protected function shiftStartTime(): Attribute
    {

        return Attribute::make(
            get: function (string $value = null,) {
                $timezone = Session::get('user_timezone');

                return Carbon::parse($value)
                    ->setTimezone($timezone)
                    ->format('H:i');
            },
        );
    }

    //-------------------------------------------------
    public static function formatTime($time, $format = 'H:i:s A')
    {
        return Carbon::parse($time)
            ->setTimezone("ASIA/KOLKATA")
            ->format($format);
    }

    //-------------------------------------------------
    protected function shiftEndTime(): Attribute
    {
        return Attribute::make(
            get: function (string $value = null) {
                $timezone = Session::get('user_timezone');
                return Carbon::parse($value)
                    ->setTimezone($timezone)
                    ->format('H:i');
            },
        );
    }


    //-------------------------------------------------
    public function scopeGetSorted($query, $filter)
    {

        if (!isset($filter['sort'])) {
            return $query->orderBy('id', 'desc');
        }

        $sort = $filter['sort'];


        $direction = Str::contains($sort, ':');

        if (!$direction) {
            return $query->orderBy($sort, 'asc');
        }

        $sort = explode(':', $sort);

        return $query->orderBy($sort[0], $sort[1]);
    }

    //-------------------------------------------------
    public function scopeIsActiveFilter($query, $filter)
    {

        if (!isset($filter['is_active'])
            || is_null($filter['is_active'])
            || $filter['is_active'] === 'null'
        ) {
            return $query;
        }
        $is_active = $filter['is_active'];

        if ($is_active === 'true' || $is_active === true) {
            return $query->where('is_active', 1);
        } else {
            return $query->where(function ($q) {
                $q->whereNull('is_active')
                    ->orWhere('is_active', 0);
            });
        }

    }

    //-------------------------------------------------
    public function scopeTrashedFilter($query, $filter)
    {

        if (!isset($filter['trashed'])) {
            return $query;
        }
        $trashed = $filter['trashed'];

        if ($trashed === 'include') {
            return $query->withTrashed();
        } else if ($trashed === 'only') {
            return $query->onlyTrashed();
        }

    }

    //-------------------------------------------------
    public function scopeSearchFilter($query, $filter)
    {

        if (!isset($filter['q'])) {
            return $query;
        }
        $search_array = explode(' ', $filter['q']);
        foreach ($search_array as $search_item) {
            $query->where(function ($q1) use ($search_item) {
                $q1->where('name', 'LIKE', '%' . $search_item . '%')
                    ->orWhere('slug', 'LIKE', '%' . $search_item . '%')
                    ->orWhere('id', 'LIKE', $search_item . '%');
            });
        }

    }

    //-------------------------------------------------
    public function scopeIsFieldFilter($query, $field_filter)
    {
        $filter_type = array_keys($field_filter);
        if (count($field_filter) <= 0) {
            return $query;
        }
        foreach ($filter_type as $filter) {

            $filter_value = $field_filter[$filter];
            switch ($filter) {

                case 'specialization' :
                    $query->whereIn('specialization', $filter_value);
                    break;
                case 'price':
                    if (is_string($filter_value)) {
                        dd($filter_value);
                        $parts = explode('-', $filter_value);

                        if (count($parts) === 2) {
                            $min_price = floatval(preg_replace('/[^0-9.]/', '', $parts[0]));
                            $max_price = floatval(preg_replace('/[^0-9.]/', '', $parts[1]));

                            $query->whereBetween('price_per_session', [$min_price, $max_price]);
                        } else {
                            \Log::error('Invalid price filter format: ' . $filter_value);
                        }
                    } else {
                        \Log::error('Expected string for price filter, received: ' . gettype($filter_value));
                    }
                    break;
                case 'timings' :
                    $parts = explode('-', $filter_value);
                    $min_time = Carbon::parse($parts[0])->format('g:i A');
                    $max_time = Carbon::parse($parts[1])->format('g:i A');
                    $query->whereRaw('TIME(shift_start_time) BETWEEN ? AND ?', [$min_time, $max_time]);
                    break;
            }
        }
        return $query;
    }

    //-------------------------------------------------
    public static function getList($request)
    {
        $list = self::getSorted($request->filter);
        $list->isActiveFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);
        if ($request->has('field_filter')) {
            $list->isFieldFilter($request->field_filter);
        }

        $rows = config('vaahcms.per_page');

        if ($request->has('rows')) {
            $rows = $request->rows;
        }

        $list = $list->select('id', 'name', 'email', 'phone', 'shift_start_time', 'price_per_session',
            'shift_end_time', 'specialization', 'is_active', 'created_at', 'updated_at');
        $list = $list->paginate($rows);


        $response['success'] = true;
        $response['data'] = $list;

        return $response;
    }


    //-------------------------------------------------
    public static function updateList($request)
    {

        $inputs = $request->all();

        $rules = array(
            'type' => 'required',
        );

        $messages = array(
            'type.required' => trans("vaahcms-general.action_type_is_required"),
        );


        $validator = \Validator::make($inputs, $rules, $messages);
        if ($validator->fails()) {

            $errors = errorsToArray($validator->errors());
            $response['success'] = false;
            $response['errors'] = $errors;
            return $response;
        }

        if (isset($inputs['items'])) {
            $items_id = collect($inputs['items'])
                ->pluck('id')
                ->toArray();
        }

        $items = self::whereIn('id', $items_id);

        switch ($inputs['type']) {
            case 'deactivate':
                $items->withTrashed()->where(['is_active' => 1])
                    ->update(['is_active' => null]);
                break;
            case 'activate':
                $items->withTrashed()->where(function ($q) {
                    $q->where('is_active', 0)->orWhereNull('is_active');
                })->update(['is_active' => 1]);
                break;
            case 'trash':
                self::whereIn('id', $items_id)
                    ->get()->each->delete();
                Appointment::whereIn('doctor_id', $items_id)
                    ->get()->each->delete();
                break;
            case 'restore':
                self::whereIn('id', $items_id)->onlyTrashed()
                    ->get()->each->restore();
                break;
        }

        $response['success'] = true;
        $response['data'] = true;
        $response['messages'][] = trans("vaahcms-general.action_successful");

        return $response;
    }

    //-------------------------------------------------
    public static function deleteList($request): array
    {
        $inputs = $request->all();

        $rules = array(
            'type' => 'required',
            'items' => 'required',
        );

        $messages = array(
            'type.required' => trans("vaahcms-general.action_type_is_required"),
            'items.required' => trans("vaahcms-general.select_items"),
        );

        $validator = \Validator::make($inputs, $rules, $messages);
        if ($validator->fails()) {

            $errors = errorsToArray($validator->errors());
            $response['success'] = false;
            $response['errors'] = $errors;
            return $response;
        }

        $items_id = collect($inputs['items'])->pluck('id')->toArray();

        $appointments = Appointment::whereIn('doctor_id', $items_id)->get();

        foreach ($appointments as $appointment) {

            $patient = Patient::find($appointment->patient_id);


            if ($patient) {

                $inputs = [
                    'doctor_id' => $appointment->doctor_id,
                    'patient_email' => $patient->email,
                    'date' => $appointment->date,
                    'slot_start_time' => $appointment->slot_start_time,
                ];


                $appointment->status = 2;
                $appointment->save();
                $subject = 'Appointment Cancellation Notice';


                self::sendCancelAppointmentMail($inputs, $subject);
            }
        }


        self::whereIn('id', $items_id)->forceDelete();

        $response['success'] = true;
        $response['data'] = true;
        $response['messages'][] = trans("vaahcms-general.action_successful");

        return $response;
    }

    //-------------------------------------------------
    public static function sendCancelAppointmentMail($inputs, $subject)
    {

        $doctor = Doctor::find($inputs['doctor_id']);


        $message_patient = sprintf(
            'Hello, your appointment with Dr. %s has been canceled as the doctor is unavailable. Please select a different doctor to reschedule your appointment.',
            $doctor->name,

        );


        VaahMail::dispatchGenericMail($subject, $message_patient, $inputs['patient_email']);
    }


    //-------------------------------------------------
    public static function listAction($request, $type): array
    {

        $list = self::query();

        if ($request->has('filter')) {
            $list->getSorted($request->filter);
            $list->isActiveFilter($request->filter);
            $list->trashedFilter($request->filter);
            $list->searchFilter($request->filter);
        }

        switch ($type) {
            case 'activate-all':
                $list->withTrashed()->where(function ($q) {
                    $q->where('is_active', 0)->orWhereNull('is_active');
                })->update(['is_active' => 1]);
                break;
            case 'deactivate-all':
                $list->withTrashed()->where(['is_active' => 1])
                    ->update(['is_active' => null]);
                break;
            case 'trash-all':
                $list->get()->each->delete();
                break;
            case 'restore-all':
                $list->onlyTrashed()->get()
                    ->each->restore();
                break;
            case 'delete-all':
                $list->forceDelete();
                break;
            case 'create-100-records':
            case 'create-1000-records':
            case 'create-5000-records':
            case 'create-10000-records':

                if (!config('appointment.is_dev')) {
                    $response['success'] = false;
                    $response['errors'][] = 'User is not in the development environment.';

                    return $response;
                }

                preg_match('/-(.*?)-/', $type, $matches);

                if (count($matches) !== 2) {
                    break;
                }

                self::seedSampleItems($matches[1]);
                break;
        }

        $response['success'] = true;
        $response['data'] = true;
        $response['messages'][] = trans("vaahcms-general.action_successful");

        return $response;
    }

    //-------------------------------------------------
    public static function getItem($id)
    {

        $item = self::select('id', 'name', 'phone', 'email', 'specialization', 'shift_start_time', 'shift_end_time', 'price_per_session', 'is_active', 'created_at', 'updated_at')
            ->where('id', $id)
            ->withTrashed()
            ->first();


        if (!$item) {
            $response['success'] = false;
            $response['errors'][] = 'Record not found with ID: ' . $id;
            return $response;
        }
        $response['success'] = true;
        $response['data'] = $item;

        return $response;
    }

    //-------------------------------------------------
    public static function updateItem($request, $id)
    {
        $inputs = $request->all();

        // Validate the inputs
        $validation = self::validation($inputs, $id);
        if (!$validation['success']) {
            return $validation;
        }
        if (!isset($inputs['is_active']) || $inputs['is_active'] == 0) {
            $inputs['is_active'] = 1;
        }
        // Check if name already exists
        $item = self::where('id', '!=', $id)
            ->withTrashed()
            ->where('name', $inputs['name'])
            ->first();

        if ($item) {
            $error_message = "This name already exists" . ($item->deleted_at ? ' in trash.' : '.');
            return [
                'success' => false,
                'errors' => [$error_message]
            ];
        }

        // Check if slug already exists


        if ($item) {
            $error_message = "This slug already exists" . ($item->deleted_at ? ' in trash.' : '.');
            return [
                'success' => false,
                'errors' => [$error_message]
            ];
        }

        $item = self::where('id', $id)
            ->withTrashed()
            ->first();

        $working_hours_changed = ($item->shift_start_time != $inputs['shift_start_time']) ||
            ($item->shift_end_time != $inputs['shift_end_time']);


        $item->fill($inputs);
        $item->save();

        if ($working_hours_changed) {


            $appointments = Appointment::where('doctor_id', $id)
                ->where('patient_id', '!=', null)
                ->get();

            foreach ($appointments as $appointment) {


                if ($appointment->status == 1) {
                    $subject = 'Appointment Rescheduled - Doctor Working Hours Changed';
                    self::sendRescheduleMail($appointment, $subject);


                    $appointment->status = 0;
                    $appointment->reason = "Doctor working hours changed. Please reschedule your appointment.";
                    $appointment->save();
                }
            }
        }


        $response = self::getItem($item->id);
        $response['messages'][] = trans("vaahcms-general.saved_successfully");
        return $response;
    }


    public static function sendRescheduleMail($appointment, $subject)
    {

        if (!$appointment->doctor_id || !$appointment->patient_id) {
            return false;
        }

        try {
            $doctor = Doctor::find($appointment->doctor_id);
            $patient = Patient::find($appointment->patient_id);
            $date = Carbon::parse($appointment->date)->toDateString();

            $appointment_url = vh_get_assets_base_url() . '/backend/appointment#/appointments/form/' . $appointment->id;

            $message_patient = sprintf(
                'Hello, %s. Unfortunately, your appointment with Dr. %s on %s has been affected due to a change in the doctor\'s working hours. Please reschedule your appointment. <br><br>
    <a href="%s" style="text-decoration:none;">
        <button style="background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
            Reschedule Here
        </button>
    </a><br><br>Thank you.',
                $patient->name,
                $doctor->name,
                $date,
                $appointment_url
            );

            $message_doctor = sprintf(
                'Hello, Dr. %s. Your appointment with %s on %s has been canceled due to a change in your working hours. The patient will be notified to reschedule.',
                $doctor->name,
                $patient->name,
                $date
            );
        } catch (\Exception $e) {
            return false;

        }


        VaahMail::dispatchGenericMail($subject, $message_doctor, $doctor->email);
        VaahMail::dispatchGenericMail($subject, $message_patient, $patient->email);
    }

    //-------------------------------------------------
    public static function deleteItem($request, $id): array
    {
        $item = self::where('id', $id)->withTrashed()->first();
        if (!$item) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms-general.record_does_not_exist");
            return $response;
        }
        $item->forceDelete();

        $response['success'] = true;
        $response['data'] = [];
        $response['messages'][] = trans("vaahcms-general.record_has_been_deleted");

        return $response;
    }

    //-------------------------------------------------
    public static function itemAction($request, $id, $type): array
    {
        switch ($type) {
            case 'activate':
                self::where('id', $id)
                    ->withTrashed()
                    ->update(['is_active' => 1]);
                break;
            case 'deactivate':
                self::where('id', $id)
                    ->withTrashed()
                    ->update(['is_active' => null]);
                break;
            case 'trash':
                self::find($id)
                    ->delete();
                break;
            case 'restore':
                self::where('id', $id)
                    ->onlyTrashed()
                    ->first()->restore();
                break;
        }

        return self::getItem($id);
    }

    //-------------------------------------------------

    public static function validation($inputs, $id = null)
    {

        $rules = array(
            'name' => 'required|max:150',
            'email' => 'required|email' . ($id ? '|unique:vh_doctors,email,' . $id : '|unique:vh_doctors,email'),
            'phone' => 'required|min:7|max:16',
            'specialization' => 'required|max:150',
        );

        $validator = \Validator::make($inputs, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors();
            $response['success'] = false;
            $response['errors'] = $messages->all();
            return $response;
        }

        $response['success'] = true;
        return $response;

    }

    //-------------------------------------------------
    public static function getActiveItems()
    {
        $item = self::where('is_active', 1)
            ->withTrashed()
            ->first();
        return $item;
    }

    //-------------------------------------------------
    //-------------------------------------------------
    public static function seedSampleItems($records = 100)
    {

        $i = 0;

        while ($i < $records) {
            $inputs = self::fillItem(false);

            $item = new self();
            $item->fill($inputs);
            $item->save();

            $i++;

        }

    }


    //-------------------------------------------------
    public static function fillItem($is_response_return = true)
    {
        $request = new Request([
            'model_namespace' => self::class,
            'except' => self::getUnFillableColumns()
        ]);
        $fillable = VaahSeeder::fill($request);
        if (!$fillable['success']) {
            return $fillable;
        }
        $inputs = $fillable['data']['fill'];

        $faker = Factory::create();

        $random_specialization = [
            'Cardiologist',
            'Dermatologist',
            'Endocrinologist',
            'Gastroenterologist',
            'Hematologist',
            'Nephrologist',
            'Neurologist',
            'Oncologist',
            'Ophthalmologist',
        ];

        $inputs['name'] = $faker->name;
        $inputs['slug'] = Str::slug($inputs['name']);
        $phone_length = rand(7, 16);
        $inputs['phone'] = (int)$faker->numerify(str_repeat('#', $phone_length));
        $inputs['specialization'] = $random_specialization[array_rand($random_specialization)];
        $inputs['shift_start_time'] = $faker->time($format = 'g:i A', $max = '11:59 AM');

        while (strpos($inputs['shift_start_time'], 'PM') !== false) {
            $inputs['shift_start_time'] = $faker->time($format = 'g:i A', $max = '11:59 AM');
        }

        $shift_start_timestamp = strtotime($inputs['shift_start_time']);

        $shift_end_timestamp = $shift_start_timestamp + (4 * 60 * 60);


        $inputs['shift_end_time'] = date('g:i A', $shift_end_timestamp);

        $inputs['price_per_session'] = $faker->numberBetween(100, 500);


        $inputs['is_active'] = $faker->randomElement([1]);

        /*
         * You can override the filled variables below this line.
         * You should also return relationship from here
         */

        if (!$is_response_return) {
            return $inputs;
        }

        $response['success'] = true;
        $response['data']['fill'] = $inputs;
        return $response;
    }


    //-------------------------------------------------
    public static function getDoctorsCountList()
    {
        $total_doctors = Doctor::count();
        $total_patients = Patient::count();


        $total_booked_appointments = Appointment::where('status', 1)->count();
        $total_cancelled_appointments = Appointment::whereIn('status', [2])->count();
        $total_rescheduled_appointments = Appointment::whereIn('status', [0])->count();

        return response()->json([
            'totalDoctors' => $total_doctors,
            'totalPatients' => $total_patients,
            'totalBookedAppointments' => $total_booked_appointments,
            'totalCancelledAppointments' => $total_cancelled_appointments,
            'totalRescheduledAppointments' => $total_rescheduled_appointments
        ]);
    }


    //-------------------------------------------------
    public static function getSpecializations(){

        $specializations = self::distinct()->pluck('specialization');
        $time_ranges = self::select('shift_start_time', 'shift_end_time')->get();


        return response()->json([
            'specializations' => $specializations,
            'time_ranges' => $time_ranges->toArray()
        ]);

    }

    //-------------------------------------------------
    /*
     * It extracts the JSON data from the request.
       It loops through each doctor record.
       It uses the updateOrCreate method to either update an existing record or insert a new one
        based on the doctor's email.
       It ensures that shift times are correctly formatted, and marks the doctor as active.
       Finally, it returns a success message.
     *
     */
    public static function bulkImport(Request $request)
    {
        $records_processed = 0;
        $records_skipped = 0;

        try {
            $file_contents = $request->json()->all();

            if (!$file_contents) {
                return response()->json(['success' => false, 'message' => 'No data provided.'], 400);
            }

            $allowed_fields = ['name', 'email', 'price', 'phone', 'specialization', 'shift_start_time', 'shift_end_time'];

            $errors = [
                'email_errors' => [],
                'phone_errors' => [],
            ];

            // Collect emails and phones from input data
            $emails = array_column($file_contents, 'email');
            $phones = array_column($file_contents, 'phone');

            // Check existing records in the database by email or phone
            $existing_doctors = self::whereIn('email', $emails)
                ->orWhereIn('phone', $phones)
                ->withTrashed()
                ->get(['email', 'phone']);

            // Array to store processed existing emails and phones
            $existing_emails = $existing_doctors->pluck('email')->toArray();
            $existing_phones = $existing_doctors->pluck('phone')->toArray();

            foreach ($file_contents as $content) {
               
                foreach ($content as $key => $value) {
                    $content[$key] = trim($value, '"');

                    if ($key === 'price' && $value === 'NA') {
                        $content[$key] = null;
                    }
                }


                $content = array_intersect_key($content, array_flip($allowed_fields));

                // Skip records with missing email or phone
                if (empty($content['email'])) {
                    $errors['email_errors'][] = "Email is required for doctor: " . ($content['name'] ?? 'unknown');
                    $records_skipped++;
                    continue;
                }

                if (empty($content['phone'])) {
                    $errors['phone_errors'][] = "Phone number is required for doctor: {$content['name']}.";
                    $records_skipped++;
                    continue;
                }

                // Skip if the email or phone already exists in the database
                if (in_array($content['email'], $existing_emails)) {
                    $errors['email_errors'][] = "The email {$content['email']} is already stored for another doctor.";
                    $records_skipped++;
                    continue;
                }


                $content['price'] = $content['price'] ?? 0.00;
                $content['specialization'] = $content['specialization'] ?? 'General';

                if (isset($content['shift_start_time'], $content['shift_end_time'])) {
                    $start_time = strtotime($content['shift_start_time']);
                    $end_time = strtotime($content['shift_end_time']);

                    if ($start_time === false || $end_time === false || $start_time >= $end_time) {
                        $errors['phone_errors'][] = "Invalid or incorrect shift times for doctor: {$content['name']}.";
                        $records_skipped++;
                        continue;
                    }
                }



                self::updateOrCreate(
                    [
                        'email' => $content['email'],
                        'phone' => $content['phone'],
                    ],
                    [
                        'name' => $content['name'],
                        'slug' => Str::slug($content['name']),
                        'price_per_session' => $content['price'],
                        'specialization' => $content['specialization'],
                        'shift_start_time' => date('Y-m-d H:i:s', strtotime($content['shift_start_time'])),
                        'shift_end_time' => date('Y-m-d H:i:s', strtotime($content['shift_end_time'])),
                        'is_active' => 1,
                    ]
                );

                $records_processed++;
            }

            // Prepare response
            $response = [];

            if ($records_processed > 0) {
                $response['messages'][] = trans("vaahcms-general.imported_successfully");
            }

            if (!empty($errors['email_errors']) || !empty($errors['phone_errors'])) {
                $response['error'] = $errors;
            }

            if ($records_processed == 0 && $records_skipped > 0) {
                $response['message'] = "No new records were imported due to errors. All provided data already exists.";
            } elseif ($records_processed > 0 && $records_skipped > 0) {
                $response['message'] = "{$records_processed} records were successfully imported. {$records_skipped} records were skipped due to errors.";
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred during import: ' . $e->getMessage()], 500);
        }
    }




    //-------------------------------------------------
    /*
     * It uses the DoctorExport class to generate a CSV file containing all the doctor records.
       It returns the file as a download.
     *
     */
    public static function bulkExport()
    {
        return Excel::download(new DoctorExport,'doctorsList.csv');
    }



}
