<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    public const frequencyDaily = 0;

    public const frequencyOnlyWeekDays = 5;

    public const frequencyOnlyWeekends = 2;

    public const frequencySpecific = -1;

    public const frequencyWeekly = 7;

    public const frequencyMonthly = 30;

    public const frequencyYearly = 365;

    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'campaigns';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'start_date', 'end_date', 'execution_time', 'frequency', 'parameters', 'title', 'body', 'deep_link',
    ];

    public function executions(): HasMany
    {
        return $this->hasMany(Execution::class);
    }
}
