<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id', 'branch_id', 'date', 'status', 'notes'
        // check_in/check_out are handled in sessions now
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // Always eager-load sessions when Attendance is fetched
    protected $with = ['sessions'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class);
    }

    /**
     * Total worked minutes for this attendance (all sessions summed)
     */
    public function getTotalWorkedMinutesAttribute(): int
    {
        return $this->sessions->sum(function ($session) {
            if ($session->check_in && $session->check_out) {
                return Carbon::parse($session->check_in)->diffInMinutes(Carbon::parse($session->check_out));
            }
            return 0;
        });
    }

    /**
     * Total worked hours as decimal (e.g. 7.50)
     */
    public function getTotalWorkedHoursAttribute(): float
    {
        $mins = $this->total_worked_minutes;
        return round($mins / 60, 2);
    }
}
