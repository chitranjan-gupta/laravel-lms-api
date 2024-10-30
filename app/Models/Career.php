<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Career extends Model
{
    use HasFactory, HasUuids;
    //
    protected $table = 'careers';

    //Specify the primary key type
    protected $keyType = 'string';

    //Indicates if the IDs are auto-incrementing
    public $incrementing = false;

    protected $fillable = [
        'title',
        'contact_no',
        'contact_email',
        'description',
        'location',
        'salary_range',
        'application_deadline',
        'career_url',
        'work_mode',
        'date_posted',
        'responsibilities',
        'benefits',
        'requirements',
        'skills',
        'level',
        'experience',
        'department',
        'companyId'
    ];

    protected $casts = [
        'responsibilities' => 'array', // Cast to an array
        'benefits' => 'array',
        'requirements' => 'array',
        'skills' => 'array',        
    ];

    public function company(){
        return $this->belongsTo(Company::class, 'companyId');
    }
}
