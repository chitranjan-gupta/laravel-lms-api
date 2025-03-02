<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleDetail extends Model
{
    use HasFactory, HasUuids;

    // Specify the table name if different from the model's default plural name
    protected $table = 'google_details';

    //Specify the primary key type
    protected $keyType = 'string';

    //Indicates if the IDs are auto-incrementing
    public $incrementing = false;

    protected $casts = [
        'expires_at' => 'datetime',  // Ensure Carbon instance handling
    ];

    // Define the fillable fields for mass assignment
    protected $fillable = [
        'google_id',
        'avatar', 
        'access_token', 
        'refresh_token', 
        'token_type', 
        'expires_at',
        'userId', // The foreign key to the users table
    ];

    // Relationship to the User model
    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }
}
