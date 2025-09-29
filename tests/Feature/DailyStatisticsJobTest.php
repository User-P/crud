<?php

namespace Tests\Feature;

use App\Events\DailyStatisticsCalculated;
use App\Jobs\CalculateDailyStatisticsJob;
use App\Models\Country;
use App\Models\DailyStatistic;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Pruebas del job que calcula y persiste estadísticas diarias.
 */
class DailyStatisticsJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * Verifica que el job genere un snapshot de estadísticas para la fecha indicada.
     */
    public function job_persists_daily_statistics_snapshot(): void
    {
        Event::fake();

        $referenceDate = Carbon::create(2025, 9, 30, 12, 0, 0);
        Carbon::setTestNow($referenceDate);

        $targetDate = Carbon::yesterday()->startOfDay();

        Country::create([
            'name' => 'México',
            'code' => 'MX',
            'region' => 'Americas',
            'population' => 100,
        ]);

        Country::create([
            'name' => 'España',
            'code' => 'ES',
            'region' => 'Europe',
            'population' => 200,
        ]);

        $previousUser = User::factory()->create([
            'created_at' => $targetDate->copy()->subDay(),
            'email_verified_at' => $targetDate->copy()->subDay(),
        ]);

        $adminUser = User::factory()->create([
            'role' => 'admin',
            'created_at' => $targetDate->copy()->addHours(10),
            'email_verified_at' => $targetDate->copy()->addHours(10),
        ]);

        DB::table('personal_access_tokens')->insert([
            'tokenable_type' => User::class,
            'tokenable_id' => $adminUser->id,
            'name' => 'auth_token',
            'token' => hash('sha256', Str::random(40)),
            'abilities' => json_encode(['*']),
            'created_at' => $targetDate->copy()->addHours(9),
            'updated_at' => $targetDate->copy()->addHours(11),
            'last_used_at' => $targetDate->copy()->addHours(11),
            'expires_at' => null,
        ]);

        $job = new CalculateDailyStatisticsJob($targetDate);
        $job->handle();

        $this->assertDatabaseCount('daily_statistics', 1);

        /** @var DailyStatistic $statistics */
        $statistics = DailyStatistic::firstOrFail();

        $this->assertTrue($statistics->date->isSameDay($targetDate));
        $this->assertEquals(2, $statistics->total_users);
        $this->assertEquals(1, $statistics->new_registrations);
        $this->assertEquals(1, $statistics->active_users);
        $this->assertEquals(2, $statistics->total_countries);
        $this->assertEquals(1, $statistics->admin_users);
        $this->assertEquals(100.0, (float) $statistics->verification_rate);
        $this->assertSame(['Americas' => 1, 'Europe' => 1], $statistics->users_by_region);
        $this->assertEquals(1, $statistics->registrations_by_hour[10]);

        Event::assertDispatched(DailyStatisticsCalculated::class, function ($event) use ($targetDate) {
            return $event->statistics->date->isSameDay($targetDate);
        });

        Carbon::setTestNow();
    }
}

