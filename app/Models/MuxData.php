<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MuxData extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'mux_data';

    //Specify the primary key type
    protected $keyType = 'string';

    //Indicates if the IDs are auto-incrementing
    public $incrementing = false;

    protected $fillable = [
        'assetId',
        'playbackId',
        'lectureId'
    ];

    public function lecture(){
        return $this->belongsTo(Lecture::class, 'lectureId');
    }
}
