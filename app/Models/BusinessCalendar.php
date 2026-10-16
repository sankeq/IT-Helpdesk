<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessCalendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'timezone',
        'work_start_time',
        'work_end_time',
        'work_days',
        'holidays',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'work_days' => 'array',
            'holidays' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function slaPolicies(): HasMany
    {
        return $this->hasMany(SlaPolicy::class);
    }

    /**
     * Check if a given date is a working day (working day of week and not a holiday).
     */
    public function isWorkDay(Carbon $date): bool
    {
        $dateInTz = $date->copy()->setTimezone($this->timezone ?? 'Asia/Makassar');
        $workDays = $this->work_days ?? [1, 2, 3, 4, 5]; // 1 = Monday, 5 = Friday

        // Check if day of week is in work days
        if (! in_array($dateInTz->dayOfWeekIso, $workDays)) {
            return false;
        }

        // Check if date is in holidays
        $holidays = $this->holidays ?? [];
        $dateString = $dateInTz->format('Y-m-d');
        if (in_array($dateString, $holidays)) {
            return false;
        }

        return true;
    }

    /**
     * Check if a timestamp is currently within working hours on a working day.
     */
    public function isWithinWorkingHours(Carbon $date): bool
    {
        if (! $this->isWorkDay($date)) {
            return false;
        }

        $dateInTz = $date->copy()->setTimezone($this->timezone ?? 'Asia/Makassar');
        $startTime = Carbon::createFromTimeString($this->work_start_time ?? '08:00', $this->timezone ?? 'Asia/Makassar')
            ->setDate($dateInTz->year, $dateInTz->month, $dateInTz->day);
        $endTime = Carbon::createFromTimeString($this->work_end_time ?? '17:00', $this->timezone ?? 'Asia/Makassar')
            ->setDate($dateInTz->year, $dateInTz->month, $dateInTz->day);

        return $dateInTz->gte($startTime) && $dateInTz->lt($endTime);
    }

    /**
     * Add business minutes to a given date, skipping non-working hours, weekends, and holidays.
     */
    public function addBusinessMinutes(Carbon $from, int $minutes): Carbon
    {
        $current = $from->copy()->setTimezone($this->timezone ?? 'Asia/Makassar');
        $remaining = $minutes;

        while ($remaining > 0) {
            // If current is not a work day, fast forward to the start of the next working day
            if (! $this->isWorkDay($current)) {
                $current->addDay()->setTimeFromTimeString($this->work_start_time ?? '08:00');

                continue;
            }

            $dayStart = Carbon::createFromTimeString($this->work_start_time ?? '08:00', $this->timezone ?? 'Asia/Makassar')
                ->setDate($current->year, $current->month, $current->day);
            $dayEnd = Carbon::createFromTimeString($this->work_end_time ?? '17:00', $this->timezone ?? 'Asia/Makassar')
                ->setDate($current->year, $current->month, $current->day);

            // If before work start, snap to work start
            if ($current->lt($dayStart)) {
                $current = $dayStart->copy();
            }

            // If after work end, advance to next day at work start
            if ($current->gte($dayEnd)) {
                $current->addDay()->setTimeFromTimeString($this->work_start_time ?? '08:00');

                continue;
            }

            // Minutes available today from current time to end of work day
            $minutesAvailableToday = $current->diffInMinutes($dayEnd, false);

            if ($remaining <= $minutesAvailableToday) {
                $current->addMinutes($remaining);
                $remaining = 0;
            } else {
                $remaining -= $minutesAvailableToday;
                $current->addDay()->setTimeFromTimeString($this->work_start_time ?? '08:00');
            }
        }

        return $current->setTimezone('UTC');
    }

    /**
     * Calculate elapsed business minutes between two timestamps.
     */
    public function calculateBusinessMinutesBetween(Carbon $start, Carbon $end): int
    {
        if ($start->gte($end)) {
            return 0;
        }

        $current = $start->copy()->setTimezone($this->timezone ?? 'Asia/Makassar');
        $target = $end->copy()->setTimezone($this->timezone ?? 'Asia/Makassar');
        $totalMinutes = 0;

        while ($current->lt($target)) {
            if (! $this->isWorkDay($current)) {
                $current->addDay()->setTimeFromTimeString($this->work_start_time ?? '08:00');

                continue;
            }

            $dayStart = Carbon::createFromTimeString($this->work_start_time ?? '08:00', $this->timezone ?? 'Asia/Makassar')
                ->setDate($current->year, $current->month, $current->day);
            $dayEnd = Carbon::createFromTimeString($this->work_end_time ?? '17:00', $this->timezone ?? 'Asia/Makassar')
                ->setDate($current->year, $current->month, $current->day);

            if ($current->lt($dayStart)) {
                $current = $dayStart->copy();
            }

            if ($current->gte($dayEnd)) {
                $current->addDay()->setTimeFromTimeString($this->work_start_time ?? '08:00');

                continue;
            }

            $effectiveEnd = $target->lt($dayEnd) ? $target : $dayEnd;
            $minutes = $current->diffInMinutes($effectiveEnd);
            $totalMinutes += $minutes;

            if ($target->lte($dayEnd)) {
                break;
            }

            $current->addDay()->setTimeFromTimeString($this->work_start_time ?? '08:00');
        }

        return $totalMinutes;
    }
}
