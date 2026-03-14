<?php

namespace App\Repositories;

use App\Models\TravelLog;
use App\Models\User;
use Illuminate\Support\Carbon;

class TimeTravelRepository
{
    public function travel(string $location, Carbon $date, User $user): User
    {
        $now = now();
        $fromLocation = $user->location;
        $user->update(['location' => $location, 'traveled_to_date' => $date, 'traveled_at_date' => $now]);

        TravelLog::create([
            'user_id' => $user->id,
            'from_location' => $fromLocation,
            'location' => $location,
            'departure_timestamp' => $now,
            'traveled_to_date' => $user->traveled_to_date,
        ]);

        return $user;
    }

    public function return(User $user): User
    {
        $now = now();
        $fromLocation = $user->location;
        $user->update(['location' => null, 'traveled_to_date' => $now, 'traveled_at_date' => $now]);
        TravelLog::create([
            'user_id' => $user->id,
            'from_location' => $fromLocation,
            'location' => null,
            'departure_timestamp' => $now,
            'traveled_to_date' => $now,
        ]);

        return $user;
    }

    public function forward($user): User
    {
        $now = now();
        $fromLocation = $user->location;
        $user->update(['traveled_to_date' => $user->traveled_to_date->add($user->traveled_at_date->diffInSeconds($now), 'seconds')->addWeek(), 'traveled_at_date' => $now]);

        TravelLog::create([
            'user_id' => $user->id,
            'from_location' => $fromLocation,
            'location' => $user->location,
            'departure_timestamp' => $now,
            'traveled_to_date' => $user->traveled_to_date,
        ]);

        return $user;
    }

    public function locationAt(User $user, Carbon $at): ?TravelLog
    {
        return TravelLog::where('user_id', $user->id)
            ->where('departure_timestamp', '<=', $at)
            ->orderByDesc('departure_timestamp')
            ->first();
    }

    public function back(User $user): User
    {
        $now = now();
        $fromLocation = $user->location;
        $user->update(['traveled_to_date' => $user->traveled_to_date->add($user->traveled_at_date->diffInSeconds($now), 'seconds')->subWeek(), 'traveled_at_date' => $now]);

        TravelLog::create([
            'user_id' => $user->id,
            'from_location' => $fromLocation,
            'location' => $user->location,
            'departure_timestamp' => $now,
            'traveled_to_date' => $user->traveled_to_date,
        ]);

        return $user;
    }
}
