<?php  namespace VaahCms\Modules\Appointment\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class ExtendController extends Controller
{

    //----------------------------------------------------------
    public function __construct()
    {
    }
    //----------------------------------------------------------
    public static function topLeftMenu()
    {
        $links = [];

        $response['success'] = true;
        $response['data'] = $links;

        return vh_response($response);

    }
    //----------------------------------------------------------
    public static function topRightUserMenu()
    {
        $links = [];

        $response['success'] = true;
        $response['data'] = $links;

        return vh_response($response);
    }
    //----------------------------------------------------------
    public static function sidebarMenu()
    {
        $links = [];


        $links[0] = [
            'icon' => 'table',
            'label'=> 'Appointment',
            'link'=> route('vh.backend.appointment'),
            'items' => [],
        ];

        $links[0]['items'][] = [
            'icon' => 'home',
            'label'=> 'Dashboard',
            'link'=> route('vh.backend.appointment'),
        ];

        $links[0]['items'][] = [
            'icon' => 'user',
            'label'=> 'Doctors',
            'link'=> route('vh.backend.appointment')."#/doctors",
        ];

        $links[0]['items'][] = [
            'icon' => 'users',
            'label'=> 'Patients',
            'link'=> route('vh.backend.appointment')."#/patients",
        ];

        $links[0]['items'][] = [
            'icon' => 'calendar',
            'label'=> 'Book Appointments',
            'link'=> route('vh.backend.appointment')."#/appointments",
        ];



        if(version_compare(config('vaahcms.version'), '2.0.0', '<' )){
            $links[0]['link'] = route('vh.backend.appointment');
        } else{
            $links[0]['url'] = route('vh.backend.appointment');
        }


        $response['success'] = true;
        $response['data'] = $links;

        return vh_response($response);
    }
    //----------------------------------------------------------

}
