<?php

namespace App\Http\Controllers;

use App\Lesson;
use App\Chapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LessonController extends Controller
{
    //Get All Lesson
    public function index(Request $request)
    {
        //Mengambil semua data lesson
        $lessons = Lesson::query();

        $chapterId = $request->query('chapter_id');
        //Membuat filter berdasarkan chapter id
        $lessons->when($chapterId, function($query) use ($chapterId) {
            return $query->where('chapter_id', '=', $chapterId);
        });

        return response()->json([
            'status' => 'success',
            'data' => $lessons->get()
        ]);
    }
    //API Detail Lesson
    public function show($id)
    {
        //Cek data lesson
        $lesson = Lesson::find($id);
        if (!$lesson) {
            return response()->json([
                'status' => 'error',
                'message' => 'lesson not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $lesson
        ]);
    }
    //API Create
    public function create(Request $request)
    {
        //Deskripsi Validasi
        $rules = [
            'name' => 'required|string',
            'video' => 'required|string',
            'chapter_id' => 'required|integer'
        ];
        //Mengambil semua data dari body
        $data = $request->all();
        //Cek validasi
        $validator = Validator::make($data, $rules);
        //Jika terjadi error
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }
        //Cek data chapter id di database
        $chapterId = $request->input('chapter_id');
        $chapter = Chapter::find($chapterId);
        //Jika tidak ditemukan
        if (!$chapter) {
            return response()->json([
                'status' => 'error',
                'message' => 'chapter not found'
            ]);
        }
        //Menyimpan data lesson ke database
        $lesson = Lesson::create($data);
        return response()->json([
            'status' => 'success',
            'data' => $lesson
        ]);
    }
    //API Update
    public function update(Request $request, $id)
    {
        //Deskripsi validasi validasi
        $rules = [
            'name' => 'string',
            'video' => 'string',
            'chapter_id' => 'integer'
        ];
        //Mengambil data yang dikirimkan
        $data = $request->all();
        //Melakukan validasi
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }
        //Cek data lesson didatabase
        $lesson = Lesson::find($id);
        if (!$lesson) {
            return response()->json([
                'status' => 'error',
                'message' => 'lesson not found'
            ], 404);
        }
        //Cek data chapters id didatabase
        $chapterId = $request->input('chapter_id');
        if($chapterId) {
            $chapter = Chapter::find($chapterId);
            if (!$chapter) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'chapter not found'
                ]);
            }
        }
        //Update data lesson di database
        $lesson->fill($data);
        $lesson->save();
        return response()->json([
            'status' => 'success',
            'data' => $lesson
        ]);
    }
    //API Delete
    public function destroy($id)
    {
        //Cek data lesson di database
        $lesson = Lesson::find($id);
        if (!$lesson) {
            return response()->json([
                'status' => 'error',
                'message' => 'lesson not found'
            ]);
        }
        //Jika data ditemukan maka akan dihapus
        $lesson->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'lesson deleted'
        ]);
    }
}
