<?php

namespace App\Http\Controllers;

use App\Course;
use App\Mentor;
use App\Review;
use App\MyCourse;
use App\Chapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    //API Get List
    public function index(Request $request)
    {
        //Mengambil semua data
        $courses = Course::query();

        $name = $request->query('name');
        $status = $request->query('status');

        //Digunakan untuk fitur filter mencari nama course
        $courses->when($name, function($query) use ($name) {
            return $query->whereRaw("name LIKE '%".strtolower($name)."%'");
        });
        //Filter mencari data dari status
        $courses->when($status, function($query) use ($status) {
            return $query->where('status', '=', $status);
        });

        return response()->json([
            'status' => 'success',
            //Page 10
            'data' => $courses->paginate(10)
        ]);
    }
    //Detail Course
    public function show($id)
    {
        //Cek course didatabase dengan menampilkan data chapter, mentor dan image
        $course = Course::with('chapters.lessons')
        ->with('mentor')
        ->with('images')
        ->find($id);
        //Jika terjadi error
        if (!$course) {
            return response()->json([
                'status' => 'error',
                'message' => 'course not found'
            ]);
        }
        //Mengembil data review
        $reviews = Review::where('course_id', '=', $id)->get()->toArray();
        if (count($reviews) > 0) {
            $userIds = array_column($reviews, 'user_id');
            $users = getUserByIds($userIds);
            if ($users['status'] === 'error') {
                $reviews = [];
            } else {
                foreach($reviews as $key => $review) {
                    $userIndex = array_search($review['user_id'], array_column($users['data'], 'id'));
                    $reviews[$key]['users'] = $users['data'][$userIndex];
                }
            }
        }
        //Cek total student, videos dan final videos
        $totalStudent = MyCourse::where('course_id', '=', $id)->count();
        $totalVideos = Chapter::where('course_id', '=', $id)->withCount('lessons')->get()->toArray();
        $finalTotalVideos = array_sum(array_column($totalVideos, 'lessons_count'));
        
        $course['reviews'] = $reviews;
        $course['total_videos'] = $finalTotalVideos;
        $course['total_student'] = $totalStudent;

        return response()->json([
            'status' => 'success',
            'data' => $course
        ]);
    }
    //API Create
    public function create(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'certificate' => 'required|boolean',
            'thumbnail' => 'string|url',
            'type' => 'required|in:free,premium',
            'status' => 'required|in:draft,published',
            'price' => 'integer',
            'level' => 'required|in:all-level,beginner,intermediate,advance',
            'mentor_id' => 'required|integer',
            'description' => 'string'
        ];
        //Mengambil semua data body
        $data = $request->all();
        //Melakukan validasi data dan rules
        $validator = Validator::make($data, $rules);
        //Jika terjadi error
        if ($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }
        //Melakukan cek apakah data mentor ada di database
        $mentorId = $request->input('mentor_id');
        //Jika terjadi error
        $mentor = Mentor::find($mentorId);
        if (!$mentor) {
            return response()->json([
                'status' => 'error',
                'message' => 'mentor not found'
            ], 404);
        }
        //Melakukan create data course didatabase
        $course = Course::create($data);
        return response()->json([
            'status' => 'success',
            'data' => $course
        ]);
    }
    //API Update
    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'string',
            'certificate' => 'boolean',
            'thumbnail' => 'string|url',
            'type' => 'in:free,premium',
            'status' => 'in:draft,published',
            'price' => 'integer',
            'level' => 'in:all-level,beginner,intermediate,advance',
            'mentor_id' => 'integer',
            'description' => 'string'
        ];

        $data = $request->all();

        $validator = Validator::make($data, $rules);

        if ($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }
        //Melakukan cek course id apakah ada di database
        $course = Course::find($id);
        if (!$course) {
            return response()->json([
                'status' => 'error',
                'message' => 'course not found'
            ], 404);
        }
        //Melakukan cek mentor id apakah ada didatabase
        $mentorId = $request->input('mentor_id');
        if ($mentorId) {
            $mentor = Mentor::find($mentorId);
            if (!$mentor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'mentor not found'
                ], 404);
            }
        }
        //Melakukan update data
        $course->fill($data);
        $course->save();
        //Melakukan pengembalian
        return response()->json([
            'status' => 'success',
            'data' => $course
        ]);
    }
    //API Delete
    public function destroy($id)
    {
        //Mencari data course id
        $course = Course::find($id);
        //Jika terjadi error
        if (!$course) {
            return response()->json([
                'status' => 'error',
                'message' => 'course not found'
            ], 404);
        }
        //Hapus course
        $course->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'course deleted'
        ]);
    }
}
