<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'courses';

    public $incrementing = false; // UUIDS are non-incrementing

    protected $keyType = 'string';

    protected $fillable = [
        'title', 'description',
        'imageUrl', 'price',
        'isPublished', 'categoryId',
        'userId'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'categoryId');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class, 'courseId', 'id');
    }

    public function attachments()
    {
        return $this->hasMany(CourseAttachment::class, 'courseId', 'id');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'courseId', 'id');
    }
}
