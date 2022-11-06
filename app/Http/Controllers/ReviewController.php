<?php

namespace App\Http\Controllers;

use App\Course;
use App\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    //API Create
    public function create(Request $request)
    {
        //Deskripsi Validasi
        $rules = [
            'user_id' => 'required|integer',
            'course_id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'note' => 'string'
        ];
        //Mengambil semua data dari body
        $data = $request->all();
        //Melakukan validasi
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }
        //Cek data course id di database
        $courseId = $request->input('course_id');
        $course = Course::find($courseId);
        if (!$course) {
            return response()->json([
                'status' => 'error',
                'message' => 'course not found'
            ]);
        }
        //Cek data user id didatabase dengan service user
        $userId = $request->input('user_id');
        $user = getUser($userId);

        if ($user['status'] === 'error') {
            return response()->json([
                'status' => $user['status'],
                'message' => $user['message']
            ], $user['http_code']);
        }
        //Mengatasi duplikasi data
        $isExistReview = Review::where('course_id', '=', $courseId)
                                ->where('user_id', '=', $userId)
                                ->exists();

        if ($isExistReview) {
            return response()->json([
                'status' => 'error',
                'message' => 'review already exist'
            ], 409);
        }
        //Create data baru didatabase
        $review = Review::create($data);
        return response()->json([
            'status' => 'success',
            'data' => $review
        ]);
    }
    //API Update
    public function update(Request $request, $id)
    {
        //Deskripsi Validasi
        $rules = [
            'rating' => 'integer|min:1|max:5',
            'note' => 'string'
        ];
        //Mengambil semua data kecuali user_id dan course_id
        $data = $request->except('user_id', 'course_id');
        //Melakukan validasi
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }
        //Cek review id di database
        $review = Review::find($id);
        if (!$review) {
            return response()->json([
                'status' => 'error',
                'message' => 'review not found'
            ], 404);
        }
        //Akan diupdate
        $review->fill($data);
        $review->save();

        return response()->json([
            'status' => 'success',
            'data' => $review
        ]);
    }
    //API Delete
    public function destroy($id)
    {
        //Cek review id di database
        $review = Review::find($id);
        if (!$review) {
            return response()->json([
                'status' => 'error',
                'message' => 'review not found'
            ], 404);
        }
        //Akan didelete
        $review->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'review deleted'
        ]);
    }
}
