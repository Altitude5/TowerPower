<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Models\Delivery;
use App\Models\Schedule;
use App\Models\SubOrder;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class DeliveryAssignmentService
{
    /**
     * Assign a delivery to a sub-order based on the nearest available schedule.
     */
    public function assignToSubOrder(SubOrder $subOrder): ?Delivery
    {
        $order = $subOrder->order;
        $tower = $order->tower;

        $nextDate = $this->findNextDeliveryDate(
            shopId: $subOrder->shop_id,
            cityId: $tower->city_id,
            from: now()
        );

        if ($nextDate === null) {
            return null;
        }

        $schedule = $nextDate['schedule'];

        return Delivery::create([
            'sub_order_id' => $subOrder->id,
            'schedule_id' => $schedule->id,
            'delivery_person_id' => $schedule->delivery_person_id,
            'customer_id' => $order->user_id,
            'tower_id' => $order->tower_id,
            'shop_id' => $subOrder->shop_id,
            'city_id' => $schedule->city_id,
            'date' => $nextDate['date'],
            'status' => DeliveryStatus::Scheduled,
        ]);
    }

    /**
     * Find the next available delivery date for a shop in a city.
     */
    public function findNextDeliveryDate(int $shopId, int $cityId, CarbonInterface $from): ?array
    {
        // 1. Load all positive schedules for this shop+city
        $positiveSchedules = Schedule::where('shop_id', $shopId)
            ->where('city_id', $cityId)
            ->where('type', 'positive')
            ->get();

        // 2. Load all negative schedules (block-outs) for this shop+city
        $negativeSchedules = Schedule::where('shop_id', $shopId)
            ->where('city_id', $cityId)
            ->where('type', 'negative')
            ->get();

        // 3. Expand each positive schedule into candidate dates
        $candidates = [];
        $windowDays = config('delivery.schedule_window_days', 60);
        $limit = Carbon::instance($from)->addDays($windowDays);

        foreach ($positiveSchedules as $schedule) {
            $dates = $this->expandSchedule($schedule, $from, $limit);
            foreach ($dates as $date) {
                $candidates[] = ['date' => $date, 'schedule' => $schedule];
            }
        }

        // 4. Collect blocked dates from negative schedules
        $blockedDates = [];
        foreach ($negativeSchedules as $schedule) {
            $blocked = $this->expandSchedule($schedule, $from, $limit);
            foreach ($blocked as $date) {
                $blockedDates[] = $date->toDateString();
            }
        }

        // 5. Filter out blocked dates, sort ascending, return nearest
        $available = array_filter(
            $candidates,
            fn ($c) => ! in_array($c['date']->toDateString(), $blockedDates)
        );

        usort($available, fn ($a, $b) => $a['date']->lt($b['date']) ? -1 : 1);

        return $available[0] ?? null;
    }

    /**
     * Expand a schedule into a list of dates within a range.
     */
    private function expandSchedule(Schedule $schedule, CarbonInterface $from, CarbonInterface $limit): array
    {
        $dates = [];
        $cursor = Carbon::instance($from);

        // If today matches, it can be a candidate (unless it's late, but we'll stick to instructions)
        while ($cursor->lte($limit)) {
            if ($this->scheduleMatchesDate($schedule, $cursor)) {
                $dates[] = $cursor->copy();
            }
            $cursor->addDay();
        }

        return $dates;
    }

    /**
     * Check if a schedule matches a specific date.
     */
    private function scheduleMatchesDate(Schedule $schedule, CarbonInterface $date): bool
    {
        return match ($schedule->recurrence) {
            'one_time' => $schedule->date?->isSameDay($date),
            'daily' => true,
            'weekdays_sunday' => $date->dayOfWeek >= CarbonInterface::SUNDAY
                && $date->dayOfWeek <= CarbonInterface::THURSDAY,
            'weekdays_monday' => $date->dayOfWeek >= CarbonInterface::MONDAY
                && $date->dayOfWeek <= CarbonInterface::FRIDAY,
            'weekly_single_day' => $date->dayOfWeek === $schedule->day_of_week,
            'weekend_friday' => in_array($date->dayOfWeek, [CarbonInterface::FRIDAY, CarbonInterface::SATURDAY]),
            'weekend_saturday' => in_array($date->dayOfWeek, [CarbonInterface::SATURDAY, CarbonInterface::SUNDAY]),
            default => false,
        };
    }
}
