<?php

namespace App\Http\Controllers;

use App\Http\Requests\LocationAtRequest;
use App\Http\Requests\TravelRequest;
use App\Http\Resources\TimeTravelResource;
use App\Models\User;
use App\Repositories\TimeTravelRepository;
use Illuminate\Http\JsonResponse;

class TimeTravelController extends Controller
{
    public function __construct(protected TimeTravelRepository $repository)
    {
    }

    public function travel(TravelRequest $request, User $user): TimeTravelResource
    {
        return new TimeTravelResource($this->repository->travel($request->location(), $request->travelTo(), $user));
    }

    public function return(User $user): TimeTravelResource
    {
        return new TimeTravelResource($this->repository->return($user));
    }

    public function forward(User $user): TimeTravelResource
    {
        return new TimeTravelResource($this->repository->forward($user));
    }

    public function back(User $user): TimeTravelResource
    {
        return new TimeTravelResource($this->repository->back($user));
    }

    public function locationAt(LocationAtRequest $request, User $user): JsonResponse
    {
        $log = $this->repository->locationAt($user, $request->at());

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'location' => $log?->location,
                'at' => $request->at()->toDateTimeString(),
            ],
        ]);
    }
}
