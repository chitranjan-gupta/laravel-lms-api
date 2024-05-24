<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    use HasFactory, HasUuids;

    //
    protected $table = 'chapters';

    //Specify the primary key type
    protected $keyType = 'string';

    //Indicates if the IDs are auto-incrementing
    public $incrementing = false;

    protected $fillable = [
        'title',
        'description',
        'position',
        'isPublished',
        'isFree',
        'courseId',
        'duration'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'courseId');
    }

    public function userProgress()
    {
        return $this->hasMany(ChapterProgress::class, 'chapterId', 'id');
    }

    public function lectures()
    {
        return $this->hasMany(Lecture::class, 'chapterId', 'id');
    }

    public function attachments()
    {
        return $this->hasMany(ChapterAttachment::class ,'chapterId', 'id');
    }
}
