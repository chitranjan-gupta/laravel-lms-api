<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lecture extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'lectures';

    //Specify the primary key type
    protected $keyType = 'string';

    //Indicates if the IDs are auto-incrementing
    public $incrementing = false;

    protected $fillable = [
        'title',
        'description',
        'videoUrl',
        'position',
        'isPublished',
        'isFree',
        'duration',
        'courseId',
        'chapterId'
    ];

    public function chapter(){
        return $this->belongsTo(Chapter::class, 'chapterId');
    }

    public function userProgress(){
        return $this->hasMany(LectureProgress::class, 'lectureId', 'id');
    }

    public function attachments(){
        return $this->hasMany(LectureAttachment::class, 'lectureId', 'id');
    }

    public function muxData(){
        return $this->hasOne(MuxData::class, 'lectureId', 'id');
    }
}
