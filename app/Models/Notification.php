<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'notifications';

    //Specify the primary key type
    protected $keyType = 'string';

    //Indicates if the IDs are auto-incrementing
    public $incrementing = false;

    protected $fillable = [
        'expopushtoken',
        'userId',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'userId');
    }
}
