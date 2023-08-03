<?php

namespace App\Http\Controllers;

use Facade\FlareClient\Http\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function empty_success() {
        return response()->json(['message' => 'Thao tác thành công'], 200);
    }

    public function error_update() {
        return response()->json(['message' => 'Không thể cập nhật bản ghi này!'], 403);
    }

    public function error_delete() {
        return response()->json(['message' => 'Không thể xóa bản ghi này!'], 403);
    }

    public function no_record() {
        return response()->json(['message' => 'Không tìm thấy bản ghi nào phù hợp!'], 404);
    }

    public function success_create_update($record) {
        return response()->json($record, 200);
    }

    public function get_list($record) {
        return response()->json($record, 200);
    }

    public function upload_images($files, $file_names) {
        foreach ($files as $file) {
            $destinationPath = 'images';
            $myimage = $file->getClientOriginalName();
            array_push($file_names, $myimage);
            $file->move(public_path($destinationPath), $myimage);
        }

        return $file_names;
    }

    public function show_images($filenames)
    {
        $files = [];
        foreach ($filenames as $filename) {
            $path = public_path('images/' . $filename);

            if (!File::exists($path)) {
                return parent::no_record();
            }

            $file = File::get($path);
            $type = File::mimeType($path);

            $files[] = [
                'name' => $filename,
                'data' => base64_encode($file),
                'type' => $type,
            ];
        }

        return $files;
    }

}
