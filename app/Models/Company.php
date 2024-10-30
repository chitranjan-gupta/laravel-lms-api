<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory, HasUuids;

    //
    protected $table = 'companies';

    //Specify the primary key type
    protected $keyType = 'string';

    //Indicates if the IDs are auto-incrementing
    public $incrementing = false;

    protected $fillable = [
        'name',
        'industry',
        'sector_type',
        'location',
        'contact_no',
        'contact_email',
        'website_url',
        'careers_url',
        'logo_url',
        'description',
        'culture',
        'userId'
    ];

    public function careers(){
        return $this->hasMany(Career::class, 'companyId', 'id');
    }
}
