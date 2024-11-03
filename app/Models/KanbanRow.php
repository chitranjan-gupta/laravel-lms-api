<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KanbanRow extends Model
{
    use HasFactory, HasUuids;

    //
    protected $table = 'kanban_rows';

    //Specify the primary key type
    protected $keyType = 'string';

    //Indicates if the IDs are auto-incrementing
    public $incrementing = false;

    protected $fillable = [
        'title',
        'subtitle',
        'position',
        'status',
        'applied_date',
        'rejected_date',
        'notes',
        'tags',
        'kanbanColumnId',
        'careerId'
    ];

    protected $casts = [
        'tags' => 'array', // Cast to an array
    ];

    public function kanbanColumn(){
        return $this->belongsTo(KanbanColumn::class, 'kanbanColumnId');
    }
}
