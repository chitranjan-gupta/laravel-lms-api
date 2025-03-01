<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Autofill extends Model
{
    use HasFactory, HasUuids;

    protected $table = "autofills";

    //Specify the primary key type
    protected $keyType = 'string';

    //Indicates if the IDs are auto-incrementing
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'userId', 'data',
    ];

    protected $casts = [
        'data' => 'array',  // Cast the JSON column to an array
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }
}
