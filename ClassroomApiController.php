<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Libs\GoogleDrive;
use Illuminate\Support\Facades\Session;

class DevelClassroomController extends Controller
{

    /**
    * アクティブクラスリストを取得する
    * $client: Googleクライアント
    */
    public function develListCourse($client) {
        $optParams = array(
            "teacherId" => 'me',
            "courseStates" => 'ACTIVE'
        );
        $service = new \Google_Service_Classroom($client);
        $resopnse = $service->courses->listCourses($optParams);
        return $resopnse->getCourses();
    }


    /**
    * WebサイトをClassroomに公開
    * $client: Googleクライアント
    * $courseId: Google ClassroomのコースID
    */
    public function develUrlShare($client, $courseId) {

        // Google Classroomサービス オブジェクトを生成
        $service = new \Google_Service_Classroom($client);

        try {

            // Google ClassroomのコースワークとしてURLを公開
            $postBody = new \Google_Service_Classroom_CourseWork(array(
                "workType" => "ASSIGNMENT",
                "title" => "Share form API Demo", // コースワークタイトル
                "state" => "PUBLISHED", // 公開状態に設定
                "materials"=>[
                    'link'=>[
                        'url'=>'http://gfejp-demo.com/', // 公開するURL
                        'title'=>'API Demo', // URLの名前
                        'thumbnailUrl'=>'https://classroom.google.com/webthumbnail?url=http://gfejp-demo.com/'
                    ]
                ]
            ));
            $coursework = $service->courses_courseWork->create($courseId, $postBody, array());

            $resopnse = array(
                "alternateLink" => $coursework->alternateLink,
                "msg" => null
            );
            return $resopnse;

        } catch(\Exception $e) {
            $msg = "処理に失敗しました。クラスが存在するか、課題を作成できるか、確認してください。";
            $resopnse = array(
                "alternateLink" => null,
                "msg" => $msg
            );
            return $resopnse;
        }
    }


    public function devel_url_share($client) {
        $courses = $this->develListCourse($client); 
        return view('listcourse_ok')->with('courses', $courses);
    }

    public function devel_url_share_execute(Request $request) {
        $access_token = Session::get('access_token');
        $client = app('App\Http\Controllers\OauthController')->getDriveClient();
        $client->setAccessToken(json_encode($access_token));

        $courseId = $request->input("courseId");
        $data =$this->develUrlShare($client, $courseId);

        Session::forget('access_token');
        Session::flash('access_token', null);

        return view('urlshare_ok')->with('data', $data);
    }

}
