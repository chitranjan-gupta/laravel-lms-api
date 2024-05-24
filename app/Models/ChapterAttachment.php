<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChapterAttachment extends Model
{
    use HasFactory, HasUuids;

    //Specify the table related to This Model
    protected $table = "chapter_attachments";

    //Specify the primary key type
    protected $keyType = 'string';

    //Indicates if the IDs are auto-incrementing
    public $incrementing = false;

    protected $fillable = [
        'name', 'url', 'chapterId'
    ];

    public function chapter(){
        return $this->belongsTo(Chapter::class, 'chapterId');
    }
}
