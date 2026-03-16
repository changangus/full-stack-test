<?php

use App\Models\TravelLog;
use App\Models\User;
use Illuminate\Support\Carbon;
use Laravel\Passport\Passport;

beforeEach(function () {
    $this->user = User::factory()->create();
    Passport::actingAs($this->user);
});

afterEach(function () {
    Carbon::setTestNow();
});

test('api travel to location and date', function () {

    $this->post(route('travel', ['user' => $this->user->id]), [
        'location' => '41.8902,12.4922',
        'travelTo' => '0080-05-01 12:00:00',
    ])->assertStatus(200)
    ->assertJsonStructure([
        'data' => [
            'user' => [
                'id',
                'name',
                'email',
            ],
            'location',
            'datetime',
            'traveledAt',
            'agentPerspectiveTimestamp',
        ],
    ]);
    $this->assertDatabaseHas(User::class, [
        'id' => $this->user->id,
        'location' => '41.8902,12.4922',
        'traveled_to_date' => '0080-05-01 12:00:00',
    ]);
    $this->assertDatabaseHas('travel_logs', [
        'user_id' => $this->user->id,
        'location' => '41.8902,12.4922',
        'from_location' => null,
    ]);
});

test('api return to present time', function () {
    $this->post(route('travel', ['user' => $this->user->id]), [
        'location' => '41.8902,12.4922',
        'travelTo' => '0080-05-01 12:00:00',
    ]);

    $this->post(route('return', ['user' => $this->user->id]))
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
                'location',
                'datetime',
                'traveledAt',
                'agentPerspectiveTimestamp',
            ],
        ]);
    $this->assertDatabaseHas(User::class, [
        'id' => $this->user->id,
        'location' => null,
        'traveled_to_date' => now()->toDateTimeString(),
    ]);
    $this->assertDatabaseHas('travel_logs', [
        'user_id' => $this->user->id,
        'location' => null,
        'from_location' => '41.8902,12.4922',
    ]);
});

test('api forward one week into the future', function () {
    $this->post(route('travel', ['user' => $this->user->id]), [
        'location' => '41.8902,12.4922',
        'travelTo' => '0080-05-01 12:00:00',
    ]);

    $this->post(route('forward', ['user' => $this->user->id]))
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
                'location',
                'datetime',
                'traveledAt',
                'agentPerspectiveTimestamp',
            ],
        ]);
    $this->assertDatabaseHas(User::class, [
        'id' => $this->user->id,
        'traveled_to_date' => '0080-05-08 12:00:00',
    ]);
    $this->assertDatabaseHas('travel_logs', [
        'user_id' => $this->user->id,
        'location' => '41.8902,12.4922',
        'from_location' => '41.8902,12.4922',
    ]);
});

test('api reverse one week into the past', function () {
    $this->post(route('travel', ['user' => $this->user->id]), [
        'location' => '41.8902,12.4922',
        'travelTo' => '0080-05-01 12:00:00',
    ]);

    $this->post(route('back', ['user' => $this->user->id]))
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
                'location',
                'datetime',
                'traveledAt',
                'agentPerspectiveTimestamp',
            ],
        ]);
    $this->assertDatabaseHas(User::class, [
        'id' => $this->user->id,
        'traveled_to_date' => '0080-04-24 12:00:00',
    ]);
    $this->assertDatabaseHas('travel_logs', [
        'user_id' => $this->user->id,
        'location' => '41.8902,12.4922',
        'from_location' => '41.8902,12.4922',
    ]);
});

test('locationAt returns correct location after a travel event', function () {
    $this->post(route('travel', ['user' => $this->user->id]), [
        'location' => '41.8902,12.4922',
        'travelTo' => '0080-05-01 12:00:00',
    ]);

    $this->call('GET', route('locationAt', ['user' => $this->user->id]), ['at' => now()->toDateTimeString()])
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                'user' => ['id' => $this->user->id],
                'location' => '41.8902,12.4922',
            ],
        ]);
});

test('locationAt returns null when agent has not yet traveled', function () {
    $this->call('GET', route('locationAt', ['user' => $this->user->id]), ['at' => now()->toDateTimeString()])
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                'location' => null,
            ],
        ]);
});

test('locationAt returns null when at timestamp predates first travel event', function () {
    $this->post(route('travel', ['user' => $this->user->id]), [
        'location' => '41.8902,12.4922',
        'travelTo' => '0080-05-01 12:00:00',
    ]);

    $this->call('GET', route('locationAt', ['user' => $this->user->id]), ['at' => now()->subYear()->toDateTimeString()])
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                'location' => null,
            ],
        ]);
});

test('locationAt returns most recent location when multiple log entries exist', function () {
    Carbon::setTestNow('2026-03-13 10:00:00');

    $this->post(route('travel', ['user' => $this->user->id]), [
        'location' => '41.8902,12.4922',
        'travelTo' => '0080-05-01 12:00:00',
    ]);

    Carbon::setTestNow('2026-03-13 11:00:00');

    $this->post(route('travel', ['user' => $this->user->id]), [
        'location' => '40.4319,116.5704',
        'travelTo' => '1300-06-15 09:00:00',
    ]);

    Carbon::setTestNow('2026-03-13 12:00:00');

    // Query at 10:30 — should return Rome coords (first travel)
    $this->call('GET', route('locationAt', ['user' => $this->user->id]), ['at' => '2026-03-13 10:30:00'])
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                'location' => '41.8902,12.4922',
            ],
        ]);

    // Query at 11:30 — should return Great Wall coords (second travel)
    $this->call('GET', route('locationAt', ['user' => $this->user->id]), ['at' => '2026-03-13 11:30:00'])
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                'location' => '40.4319,116.5704',
            ],
        ]);
});
