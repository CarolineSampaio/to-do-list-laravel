<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category_id',
        'is_completed',
        'completed_by',
        'completed_at',

    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by')
            ->select(['id', 'name']);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')->withTimestamps();
    }

    public function getIsCompletedAttribute($value)
    {
        return (bool) $value;
    }
}
