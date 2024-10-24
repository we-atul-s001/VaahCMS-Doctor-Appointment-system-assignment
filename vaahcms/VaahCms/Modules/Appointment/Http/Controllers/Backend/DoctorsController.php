<?php namespace VaahCms\Modules\Appointment\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use VaahCms\Modules\Appointment\Models\Doctor;


class DoctorsController extends Controller
{


    //----------------------------------------------------------
    public function __construct()
    {

    }

    //----------------------------------------------------------

    public function getAssets(Request $request)
    {
        $doctor_permission_slug = 'appointment-has-access-of-doctor';
        $patient_permission_slug = 'appointment-has-access-of-patient';


        if (!\Auth::user()->hasPermission($doctor_permission_slug) && \Auth::user()->hasPermission($patient_permission_slug)) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return response()->json($response);
        }
        try{

            $data = [];

            $data['permission'] =$data['permission'] =  \Auth::user()->permissions(true);;
            $data['rows'] = config('vaahcms.per_page');

            $data['fillable']['columns'] = Doctor::getFillableColumns();
            $data['fillable']['except'] = Doctor::getUnFillableColumns();
            $data['empty_item'] = Doctor::getEmptyItem();
            $data['count_appointment'] = (new \VaahCms\Modules\Appointment\Models\Doctor)->getAppointmentsCountAttribute();


            $data['actions'] = [];

            $response['success'] = true;
            $response['data'] = $data;

        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
        }

        return $response;
    }

    //----------------------------------------------------------
    public function getList(Request $request)
    {
        try{
            return Doctor::getList($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function updateList(Request $request)
    {
        try{
            return Doctor::updateList($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");

            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function listAction(Request $request, $type)
    {


        try{
            return Doctor::listAction($request, $type);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;

        }
    }
    //----------------------------------------------------------

    public function getDoctorStatus(Request $request, $id)
        {
            try{
                return Doctor::getDoctorStatus($id);
            }catch (\Exception $e) {
                $response = [];
                $response['success'] = false;
                if (env('APP_DEBUG')) {
                    $response['errors'][] = $e->getMessage();
                    $response['hint'] = $e->getTrace();
                }else{
                    $response['errors'][] = trans("vaahcms-general.something_went_wrong");
                }
                return $response;
            }
        }
    //----------------------------------------------------------
    public function deleteList(Request $request)
    {
        try{
            return Doctor::deleteList($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function fillItem(Request $request)
    {
        try{
            return Doctor::fillItem($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function createItem(Request $request)
    {
        try{
            return Doctor::createItem($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function getItem(Request $request, $id)
    {
        try{
            return Doctor::getItem($id);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function updateItem(Request $request,$id)
    {
        try{
            return Doctor::updateItem($request,$id);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function deleteItem(Request $request,$id)
    {
        try{
            return Doctor::deleteItem($request,$id);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function itemAction(Request $request,$id,$action)
    {
        try{
            return Doctor::itemAction($request,$id,$action);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------

    public function getDoctorsCount(Request $request)
    {
        try {
            return Doctor::getDoctorsCountList($request);
        } catch (\Exception $e) {
            $response = [];
            $response['success'] = false;
            if (env('APP_DEBUG')) {
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else {
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function getSpecializations(Request $request)
    {
        try {
            return Doctor::getSpecializations($request);
        } catch (\Exception $e) {
            $response = [];
            $response['success'] = false;
            if (env('APP_DEBUG')) {
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else {
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function bulkImport(Request $request)
    {
        try {
            return Doctor::bulkImport($request);
        } catch (\Exception $e) {
            $response = [];
            $response['success'] = false;
            if (env('APP_DEBUG')) {
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else {
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function bulkExport(Request $request)
    {
        try {
            return Doctor::bulkExport($request);
        } catch (\Exception $e) {
            $response = [];
            $response['success'] = false;
            if (env('APP_DEBUG')) {
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else {
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
        }
    }
}
