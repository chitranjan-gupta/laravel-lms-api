<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KanbanColumn extends Model
{
    use HasFactory, HasUuids;

    //
    protected $table = 'kanban_columns';

    //Specify the primary key type
    protected $keyType = 'string';

    //Indicates if the IDs are auto-incrementing
    public $incrementing = false;

    protected $fillable = [
        'name',
        'position',
        'userId'
    ];

    public function kanbanRows(){
        return $this->hasMany(KanbanRow::class,'kanbanColumnId','id');
    }
}
