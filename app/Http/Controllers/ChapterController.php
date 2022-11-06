<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Chapter;
use App\Course;
use Illuminate\Support\Facades\Validator;

class ChapterController extends Controller
{
    //API Get All Data Chapters
    public function index(Request $request)
    {
        //Mengambil semua data chapters
        $chapters = Chapter::query();
        $courseId = $request->query('course_id');
        //Membuat filter dengan course id
        $chapters->when($courseId, function($query) use ($courseId) {
            return $query->where('course_id', '=', $courseId);
        });

        return response()->json([
            'status' => 'success',
            'data' => $chapters->get()
        ]);
    }
    //API Detail Chapters
    public function show($id)
    {
        //Melakukan cek chapters id
        $chapter = Chapter::find($id);
        if (!$chapter) {
            return response()->json([
                'status' => 'error',
                'message' => 'chapter not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $chapter
        ]);
    }
    //API Create
    public function create(Request $request)
    {
        //Mendeskripsikan validasi
        $rules = [
            'name' => 'required|string',
            'course_id' => 'required|integer'
        ];
        //Mengambil semua data
        $data = $request->all();

        $validator = Validator::make($data, $rules);
        //Melakukan cek error
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }
        //Melakukan cek course id di database
        $courseId = $request->input('course_id');
        $course = Course::find($courseId);
        //Jika tidak ada
        if (!$course) {
            return response()->json([
                'status' => 'error',
                'message' => 'course not found'
            ], 404);
        }
        //Melakukan create data chapter
        $chapter = Chapter::create($data);
        return response()->json([
            'status' => 'success',
            'data' => $chapter
        ]);
    }

    //API Update
    public function update(Request $request, $id)
    {
        //Mendeskripsian validasi
        $rules = [
            'name' => 'string',
            'course_id' => 'integer'
        ];

        $data = $request->all();

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }
        //Cek data chapter didatabase
        $chapter = Chapter::find($id);
        if (!$chapter) {
            return response()->json([
                'status' => 'error',
                'message' => 'chapter not found'
            ], 404);
        }
        //Melakukan cek data course id
        $courseId = $request->input('course_id');
        if($courseId) {
            $course = Course::find($courseId);
            if (!$course) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'course not found'
                ], 404);
            }
        }
        //Update data chapter
        $chapter->fill($data);
        $chapter->save();

        return response()->json([
            'status' => 'success',
            'data' => $chapter
        ]);
    }
    //API Delete
    public function destroy($id)
    {
        //Melakukan cek data chapters
        $chapter = Chapter::find($id);
        if (!$chapter) {
            return response()->json([
                'status' => 'error',
                'message' => 'chapter not found'
            ], 404);
        }
        //Jika ditemukan maka akan dihapus
        $chapter->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'chapter deleted'
        ]);
    }
}
