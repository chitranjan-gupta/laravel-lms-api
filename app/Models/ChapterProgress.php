<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChapterProgress extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'chapter_progress';

    //Specify the primary key type
    protected $keyType = 'string';

    //Indicates if the IDs are auto-incrementing
    public $incrementing = false;

    protected $fillable = [
        'userId',
        'chapterId',
        'isCompleted'
    ];

    public function chapter(){
        return $this->belongsTo(Chapter::class, 'id', 'chapterId');
    }
}
