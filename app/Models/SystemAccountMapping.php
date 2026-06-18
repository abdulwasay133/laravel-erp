<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemAccountMapping extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    public static function getAccount($key)
    {
        return static::where('key', $key)->value('chart_of_account_id');
    }
}
