<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LectureProgress extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'lecture_progress';

    //Specify the primary key type
    protected $keyType = 'string';

    //Indicates if the IDs are auto-incrementing
    public $incrementing = false;

    protected $fillable = [
        'userId',
        'lectureId',
        'isCompleted'
    ];

    public function lecture(){
        return $this->belongsTo(Lecture::class, 'lectureId');
    }
}
