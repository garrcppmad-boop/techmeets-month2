<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'status',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public static function statuses(): array
    {
        return [
            'pending'     => '未着手',
            'in_progress' => '進行中',
            'done'        => '完了',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
