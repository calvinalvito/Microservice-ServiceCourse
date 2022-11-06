<?php

namespace App\Http\Controllers;

use App\ImageCourse;
use App\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ImageCourseController extends Controller
{
    //API Create
    public function create(Request $request)
    {
        //Mendefinisikan validasi
        $rules = [
            'image' => 'required|url',
            'course_id' => 'required|integer'
        ];

        $data = $request->all();
        //Melakukan validasi
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ],400);
        }
        //Cek course id didatabase
        $courseId = $request->input('course_id');
        $course = Course::find($courseId);
        //Jika tidak ditemukan
        if (!$course) {
            return response()->json([
                'status' => 'error',
                'message' => 'course not found'
            ], 404);
        }
        //Menambahkan data image course didatabase
        $imageCourse = ImageCourse::create($data);
        return response()->json([
            'status' => 'success',
            'data' => $imageCourse
        ]);
    }
    //API Destroy
    public function destroy($id)
    {
        //Cek image id didatabase
        $imageCourse = ImageCourse::find($id);
        //Jika tidak ada
        if (!$imageCourse) {
            return response()->json([
                'status' => 'error',
                'message' => 'image course not found'
            ], 404);
        }
        //Jika ada maka akan dihapus
        $imageCourse->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'image course deleted'
        ]);
    }
}
