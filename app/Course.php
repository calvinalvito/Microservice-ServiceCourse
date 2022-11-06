<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $table = 'courses';

    protected $fillable = [
        'name', 'certificate', 'thumbnail', 'type',
        'status', 'price', 'level', 'description', 'mentor_id'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:m:s',
        'updated_at' => 'datetime:Y-m-d H:m:s'
    ];
    //Untuk mendapatkan data dari table mentor
    public function mentor()
    {
        return $this->belongsTo('App\Mentor');
    }
    //Untuk mendapatkan data dari table chapters
    public function chapters()
    {
        //Akan diurutkan
        return $this->hasMany('App\Chapter')->orderBy('id', 'ASC');
    }
    //Untuk mendapatkan data dari table images
    public function images()
    {
        return $this->hasMany('App\ImageCourse')->orderBy('id', 'DESC');
    }
}
