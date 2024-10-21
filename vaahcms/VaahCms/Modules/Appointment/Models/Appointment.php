<?php namespace VaahCms\Modules\Appointment\Models;

use App\ExportData\AppointmentExport;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Faker\Factory;
use Maatwebsite\Excel\Facades\Excel;
use WebReinvent\VaahCms\Libraries\VaahMail;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;

class Appointment extends VaahModel
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;

    //-------------------------------------------------
    protected $table = 'vh_appointments';
    //-------------------------------------------------
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    //-------------------------------------------------
    protected $fillable = [
        'uuid',
        'patient_id',
        'slot_start_time',
        'date',
        'slot_end_time',
        'doctor_id',
        'status',
        'reason',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
    //-------------------------------------------------
    protected $fill_except = [

        'created_by',
        'updated_by',
        'deleted_by',
        'is_active',
        'slot_end_time',
        'uuid'

    ];

    //-------------------------------------------------
    protected $appends = [
    ];

    //-------------------------------------------------
    protected function serializeDate(DateTimeInterface $date)
    {
        $date_time_format = config('settings.global.datetime_format');
        return $date->format($date_time_format);
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
    public static function getCustomColumnFields()
    {
        $model = new self();

        $fillable_columns = $model->fillable;

        $fillable_columns = array_diff($fillable_columns, $model->fill_except);

        $columns_mapping = [

            'doctor_id' => 'Doctor Name',
            'patient_id' => 'Patient Name',
            'slot_start_time' => 'Slot Start Time',
        ];

        $replace_columns_frontend  = array_map(function ($column) use ($columns_mapping) {
            return $columns_mapping[$column] ?? $column;
        }, $fillable_columns);

        return $replace_columns_frontend;
    }
    //-------------------------------------------------
    protected function slotStartTime(): Attribute
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
    public static function formatTime($time, $format = 'g:i A')
    {
        return Carbon::parse($time)
            ->setTimezone("ASIA/KOLKATA")
            ->format($format);
    }

    //-------------------------------------------------
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id')
            ->select('name', 'id');

    }

    //-------------------------------------------------
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id')



            ->select('id','name','shift_start_time','shift_end_time','specialization', 'price_per_session','email');

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
            $from = \Carbon::parse($from)
                ->startOfDay()
                ->toDateTimeString();
        }

        if ($to) {
            $to = \Carbon::parse($to)
                ->endOfDay()
                ->toDateTimeString();
        }

        $query->whereBetween('updated_at', [$from, $to]);
    }

    //-------------------------------------------------
    public static function createItem($request)
    {

        $check_status = self::checkAppointmentTime($request->input('slot_start_time'),
            $request->input('date'), $request->input('doctor_id'));

        if(count($check_status) > 0){
            $response['errors'][] = 'Slot is already booked. Please select 15 minutes later slot';
            return $response;
        }

        $inputs = $request->all();
        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }
        if (!isset($inputs['is_active']) || $inputs['is_active'] == 0) {
            $inputs['is_active'] = 1;
        }
//         check if Slot exist
        $item = self::where('patient_id', $inputs['patient_id'])
            ->where('doctor_id', $inputs['doctor_id'])->where('date', $inputs['date'])
            ->where('status','!=','0')
            ->withTrashed()->first();
        if ($item) {
            $error_message = "This Patient has already appointment " . $inputs['date'];
            $response['success'] = false;
            $response['errors'][] = $error_message;
            return $response;
        }
        $slot_exists = self::checkDoctorSlot($inputs);
        if($slot_exists=='Invalid Slot')
        {
            $response['errors'][] = 'The Selected Slot is not available for Doctor.';
            return $response;
        }
        elseif ($slot_exists === 'No Slot Available') {
            $response['errors'][] = 'Slot Not available.';
            return $response;
        }

        $item = new self();
        $item->fill($inputs);

        $item->status = 1;
        $item->reason = 'N/A';

        $item->save();

        $subject = 'Appointment Booked - Mail';

        self::sendAppointmentMail($inputs, $subject);

        $response = self::getItem($item->id);
        $response['messages'][] = trans("vaahcms-general.appointment_booked_successfully");
        return $response;

    }

    //-------------------------------------------------
    public static function checkAppointmentTime($slot_start_time, $appointment_date, $doctorId)
    {

        $slot_time = Carbon::parse($slot_start_time);

        $slot_date = $appointment_date;

        $appointments = Appointment::where('doctor_id', $doctorId)
            ->where('date', $slot_date)
            ->where(function ($query) use ($slot_time) {

                $query->whereBetween('slot_start_time', [
                    $slot_time->copy()->subMinutes(30),
                    $slot_time->copy()->addMinutes(30)
                ]);
            })->get();

        if ($appointments->isEmpty()) {

            return [];
        }

        return [
            'status' => 'unavailable',
            'message' => 'The selected slot is unavailable. Please select a different time.',
            'conflicting_appointments' => $appointments
        ];
    }


//-------------------------------------------------
    public static function checkDoctorSlot($data)
    {
        $timezone = Session::get('user_timezone');
        $start_time = $data['slot_start_time'];


        $doctor_shift_time = Doctor::where('id', $data['doctor_id'])
            ->where('shift_start_time', '<=', $start_time)
            ->exists();
        if (!$doctor_shift_time) {

            return 'Invalid Slot';
        }

        $slots_exist = self::where('doctor_id', $data['doctor_id'])->where('date', $data['date'])->where(function ($query)
        use ($start_time) {
            $query
                ->where('slot_start_time', '>', $start_time);
        })->withTrashed()->exists();
        if ($slots_exist) {
            return 'No Slot Available';
        }
        else{
            return false;        }

    }

    //-------------------------------------------------
    public static function sendAppointmentMail($inputs, $subject)
    {
        $doctor = Doctor::find($inputs['doctor_id']);
        $patient = Patient::find($inputs['patient_id']);
        $date = Carbon::parse($inputs['date'])->toDateString();
        $slot_start_time = self::formatTime($inputs['slot_start_time']);
        $message_patient = sprintf(
            'Hello, %s, You have an appointment is scheduled with Dr. %s on %s at %s',
            $patient->name,
            $doctor->name,
            $date,
            $slot_start_time,

        );
        $message_doctor=sprintf(
            'Hello, DR. %s, You have an appointment scheduled with Patient %s on %s at %s',
            $doctor->name,
            $patient->name,
            $date,
            $slot_start_time
        );
        VaahMail::dispatchGenericMail($subject, $message_doctor, $doctor->email);
        VaahMail::dispatchGenericMail($subject, $message_patient, $patient->email);

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

    public function scopeIsStatusFilter($query, $filter)
    {

        if (!isset($filter['status'])
            || is_null($filter['status'])
            || $filter['status'] === 'null'
        ) {
            return $query;
        }
        $is_active = $filter['status'];

        if ($is_active === 'true' || $is_active === true) {
            return $query->where('status', 1);
        } else {
            return $query->where(function ($q) {
                $q->whereNull('status')
                    ->orWhere('status', 0);
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
                $q1->whereHas('doctor', function ($query) use ($search_item) {
                    $query->where('name', 'LIKE', '%' . $search_item . '%');
                })
                    ->orWhereHas('patient', function ($query) use ($search_item) {
                        $query->where('name', 'LIKE', '%' . $search_item . '%');
                    })
                    ->orWhere(function ($query) use ($search_item) {
                        $search_item = strtolower($search_item);

                        if ($search_item === 'booked') {
                            $query->where('status', 1);
                        } elseif ($search_item === 'cancelled') {
                            $query->whereIn('status', [0, 2]);
                        }
                    });

            });
        }

    }


    //-------------------------------------------------
    public static function getList($request)
    {
        $list = self::getSorted($request->filter);
        $list->isActiveFilter($request->filter);
        $list->isStatusFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);
        $list->with(['patient', 'doctor']);

        $rows = config('vaahcms.per_page');

        if ($request->has('rows')) {
            $rows = $request->rows;
        }

        $list = $list->select('id', 'doctor_id', 'patient_id', 'date', 'slot_start_time',
            'reason','status','is_active', 'created_at', 'updated_at');
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
        self::whereIn('id', $items_id)->forceDelete();

        $response['success'] = true;
        $response['data'] = true;
        $response['messages'][] = trans("vaahcms-general.action_successful");

        return $response;
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

                if (!config('appoinments.is_dev')) {
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


        $item = self::select('id','doctor_id', 'patient_id', 'slot_start_time', 'date', 'reason','status', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by')
            ->with(['createdByUser', 'updatedByUser', 'doctor', 'patient'])
            ->withTrashed()
            ->first();

        if (!$item) {
            $response['success'] = false;
            $response['errors'][] = 'Record not found with ID: ' . $id;
            return $response;
        }


//        $item->doctor_name = $item->doctor ? $item->doctor->name : null;
//        $item->patient_name = $item->patient ? $item->patient->name : null;

        $response['success'] = true;
        $response['data'] = $item;

        return $response;


    }

    //--------------------Update the appointment-----------------------------
    public static function updateItem($request, $id)
    {
        $inputs = $request->all();

        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }

        if (!isset($inputs['is_active']) || $inputs['is_active'] == 0) {
            $inputs['is_active'] = 1;
        }
        $item = self::where('id', $id)->withTrashed()->first();

        if (!$item) {
            $response['success'] = false;
            $response['errors'][] = "Item not found.";
            return $response;
        }


        $item->fill($inputs);
        $item->status = 1;
        $item->reason = 'Appointment Rescheduled';
        $item->save();


        $subject = 'Appointment Updated - Mail';
        self::sendAppointmentMail($inputs, $subject);


        $response = self::getItem($item->id);
        $response['messages'][] = trans("vaahcms-general.appointment_updated_successfully");

        return $response;
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
        $subject='Appointment Cancelled - Mail';
        $patient = Patient::find($item['patient_id']);
        $doctor = Doctor::find($item['doctor_id']);

        $date = Carbon::parse($item['date'])->toDateString();

        $item->status = 2;
        $user = \Auth::user();

        $item->reason = "Cancelled by Patient";

        $item->save();
        $message = sprintf(
            'Hello %s, Your appointment with Dr. %s on %s is cancelled by patient.',
            $patient->name,
            $doctor->name,
            $date
        );

        VaahMail::dispatchGenericMail($subject, $message, $patient->email);

        $response['success'] = true;
        $response['data'] = [];
        $response['messages'][] = trans("vaahcms-general.appointment_cancelled_successfully");

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

    public static function validation($inputs)
    {

        $rules = array(

            'slot_start_time' => 'required',
            'date' => 'required',
            'doctor_id' => 'required',
            'patient_id' => 'required',
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
    public static function bulkImport(Request $request)
    {
        try {
            $file_contents = $request->json()->all();
            dd($file_contents);

            if (!$file_contents) {
                return response()->json(['error' => 'No data provided.'], 400);
            }

            $header_mapping = [
                'doctor' => ['doctor', 'Doctor'],
                'patient' => ['patient', 'Patient'],
                'email' => ['email', 'Email'],
                'specialization' => ['specialization', 'Specialization'],
                'date' => ['date', 'Date'],
                'slot_start_time' => ['slot_start_time', 'Slot_Start_Time'],
                'reason' => ['reason', 'Reason'],
            ];

            $errors = [
                'email_errors' => [],
                'missing_fields_header' => [],
                'availability_errors' => [],
                'nameErrors' => [],
            ];

            $first_row = $file_contents[0] ?? [];
            $columns = array_map('trim', array_map('strtolower', array_keys($first_row)));

            $field_map = [];
            foreach ($header_mapping as $db_field => $aliases) {
                foreach ($columns as $column) {
                    if (in_array(strtolower($column), array_map('strtolower', $aliases))) {
                        $field_map[$db_field] = $column;
                        break;
                    }
                }
            }

            $required_fields = ['doctor', 'patient', 'email', 'specialization', 'date', 'slot_start_time', 'reason'];
            foreach ($required_fields as $required_field) {
                if (!isset($field_map[$required_field])) {
                    $errors['missing_fields_header'][] = "Missing required field: {$required_field}.";
                }
            }

            if (!empty($errors['missing_fields_header'])) {
              $errors['missing_fields_header'] = array_unique($errors['missing_fields_header']);
            }

            $records_processed = 0;

            foreach ($file_contents as $index => $content) {
                $mapped_content = [];
                foreach ($field_map as $db_field => $csv_header) {
                    $mapped_content[$db_field] = isset($content[$csv_header]) ? trim($content[$csv_header], '"') : null;
                }



                if (empty($mapped_content['email']) || !filter_var($mapped_content['email'], FILTER_VALIDATE_EMAIL)) {
                    $errors['email_errors'][] = "Error in row {$index}: Invalid or missing email format.";
                    continue;
                }


                $doctor = Doctor::where('email', $mapped_content['email'])->first();
                if (!$doctor) {
                    $errors['email_errors'][] = "Doctor with email '{$mapped_content['email']}' not found.";
                    continue;
                }

                // Check if the patient exists
                $patient = Patient::where('name', $mapped_content['patient'])->first();
                if (!$patient) {
                    $errors['nameErrors'][] = "Error in row {$index}: Patient '{$mapped_content['patient']}' not found.";
                    continue;
                }

                $existing_appointment = self::where('doctor_id', $doctor->id)
                    ->where('patient_id', $patient->id)
                    ->where('date', date('Y-m-d', strtotime($mapped_content['date'])))
                    ->where('slot_start_time', date('Y-m-d H:i:s', strtotime($mapped_content['slot_start_time'])))
                    ->first();

                if ($existing_appointment) {
                    $errors['availability_errors'][] = "Error in row {$index}: Appointment already exists for Doctor '{$mapped_content['doctor']}' and Patient '{$mapped_content['patient']}' on '{$mapped_content['date']}' at '{$mapped_content['slot_start_time']}'.";
                    continue;
                }

                if ($doctor->specialization !== $mapped_content['specialization']) {
                    $errors['availability_errors'][] = "Error in row {$index}: Specialization '{$mapped_content['specialization']}' does not match doctor specialization.";
                    continue;
                }


                self::updateOrCreate([
                    'doctor_id' => $doctor->id,
                    'patient_id' => $patient->id,
                    'slot_start_time' => date('Y-m-d H:i:s', strtotime($mapped_content['slot_start_time'])),
                    'date' => date('Y-m-d', strtotime($mapped_content['date'])),
                    'status' => 1,
                    'reason' => $mapped_content['reason'],
                    'is_active' => 1,
                ]);

                $records_processed++;
            }

            $response = [];
            if ($records_processed > 0) {
                $response['messages'][] = trans("vaahcms-general.imported_successfully");
            }

            if (!empty($errors['email_errors']) || !empty($errors['availability_errors']) || !empty($errors['nameErrors'])) {
                $response['error'] = $errors;
            }

            if ($records_processed == 0) {
                $response['message'] = "No new records were processed due to errors.";
            } elseif ($records_processed > 0) {
                $response['message'] = "{$records_processed} records were successfully processed.";
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



    //-------------------------------------------------

    public static function bulkExport()
    {

        return Excel::download(new AppointmentExport, 'appointmentsList.csv');
}
    //-------------------------------------------------


}
