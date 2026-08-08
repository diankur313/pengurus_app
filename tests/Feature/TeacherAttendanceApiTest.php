<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TeacherAttendanceApiTest extends TestCase
{
    use DatabaseTransactions;

    // ─────────────────────────────────────────────
    //  Attendance history date filtering
    // ─────────────────────────────────────────────

    /** @test */
    public function it_returns_all_attendance_when_no_date_filter()
    {
        Sanctum::actingAs(User::factory()->create());

        $teacher = Teacher::factory()->create(['name' => 'Ustadz Test']);
        TeacherAttendance::factory()->count(3)->create(['teacher_id' => $teacher->id]);

        $res = $this->getJson('/api/teacher-attendance');

        $res->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_filters_by_date_from()
    {
        Sanctum::actingAs(User::factory()->create());

        $teacher = Teacher::factory()->create();
        TeacherAttendance::factory()->create(['teacher_id' => $teacher->id, 'attendance_date' => '2026-07-01 08:00:00']);
        TeacherAttendance::factory()->create(['teacher_id' => $teacher->id, 'attendance_date' => '2026-08-01 09:00:00']);

        $res = $this->getJson('/api/teacher-attendance?date_from=2026-08-01');

        $res->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attendance_date', '2026-08-01T09:00:00.000000Z');
    }

    /** @test */
    public function it_filters_by_date_to()
    {
        Sanctum::actingAs(User::factory()->create());

        $teacher = Teacher::factory()->create();
        TeacherAttendance::factory()->create(['teacher_id' => $teacher->id, 'attendance_date' => '2026-07-01 08:00:00']);
        TeacherAttendance::factory()->create(['teacher_id' => $teacher->id, 'attendance_date' => '2026-08-01 09:00:00']);

        $res = $this->getJson('/api/teacher-attendance?date_to=2026-07-15');

        $res->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attendance_date', '2026-07-01T08:00:00.000000Z');
    }

    /** @test */
    public function it_filters_by_date_range_inclusive()
    {
        Sanctum::actingAs(User::factory()->create());

        $teacher = Teacher::factory()->create();
        TeacherAttendance::factory()->create(['teacher_id' => $teacher->id, 'attendance_date' => '2026-08-01 08:00:00']);
        TeacherAttendance::factory()->create(['teacher_id' => $teacher->id, 'attendance_date' => '2026-08-03 09:00:00']);
        TeacherAttendance::factory()->create(['teacher_id' => $teacher->id, 'attendance_date' => '2026-08-05 10:00:00']);

        $res = $this->getJson('/api/teacher-attendance?date_from=2026-08-01&date_to=2026-08-03');

        $res->assertOk()
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function it_rejects_invalid_date_format()
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/teacher-attendance?date_from=01-08-2026')
            ->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }

    /** @test */
    public function it_rejects_date_from_greater_than_date_to()
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/teacher-attendance?date_from=2026-08-10&date_to=2026-08-01')
            ->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'date_from tidak boleh lebih besar dari date_to.');
    }

    /** @test */
    public function it_retains_query_params_in_pagination_links()
    {
        Sanctum::actingAs(User::factory()->create());

        $teacher = Teacher::factory()->create();
        TeacherAttendance::factory()->count(3)->create(['teacher_id' => $teacher->id]);

        $res = $this->getJson('/api/teacher-attendance?date_from=2026-01-01&per_page=2&teacher_id=' . $teacher->id);

        $res->assertOk();
        $res->assertJsonPath('meta.per_page', 2);
    }

    // ─────────────────────────────────────────────
    //  Combined filters
    // ─────────────────────────────────────────────

    /** @test */
    public function it_combines_date_filter_with_teacher_id()
    {
        Sanctum::actingAs(User::factory()->create());

        $t1 = Teacher::factory()->create();
        $t2 = Teacher::factory()->create();

        TeacherAttendance::factory()->create(['teacher_id' => $t1->id, 'attendance_date' => '2026-08-01 08:00:00']);
        TeacherAttendance::factory()->create(['teacher_id' => $t2->id, 'attendance_date' => '2026-08-01 09:00:00']);

        $res = $this->getJson('/api/teacher-attendance?date_from=2026-08-01&date_to=2026-08-01&teacher_id=' . $t1->id);

        $res->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.teacher.id', $t1->id);
    }

    // ─────────────────────────────────────────────
    //  Photo URL contract
    // ─────────────────────────────────────────────

    /** @test */
    public function teacher_photo_is_null_when_not_set()
    {
        Sanctum::actingAs(User::factory()->create());

        Teacher::factory()->create(['photo' => null]);

        $res = $this->getJson('/api/teachers');

        $res->assertOk()
            ->assertJsonPath('data.0.photo', null);
    }

    /** @test */
    public function teacher_photo_returns_absolute_url_for_relative_path()
    {
        Sanctum::actingAs(User::factory()->create());

        Teacher::factory()->create(['photo' => 'teachers/some-photo.jpg']);

        $res = $this->getJson('/api/teachers');

        $res->assertOk();
        $this->assertStringContainsString(
            '/storage/teachers/some-photo.jpg',
            $res->json('data.0.photo')
        );
    }

    /** @test */
    public function teacher_photo_preserves_already_absolute_url()
    {
        Sanctum::actingAs(User::factory()->create());

        Teacher::factory()->create(['photo' => 'https://cdn.example.com/photo.jpg']);

        $res = $this->getJson('/api/teachers');

        $res->assertOk()
            ->assertJsonPath('data.0.photo', 'https://cdn.example.com/photo.jpg');
    }

    /** @test */
    public function attendance_nested_teacher_uses_absolute_photo_url()
    {
        Sanctum::actingAs(User::factory()->create());

        $teacher = Teacher::factory()->create(['photo' => 'teachers/nested.jpg']);
        TeacherAttendance::factory()->create(['teacher_id' => $teacher->id, 'attendance_date' => '2026-08-01 10:00:00']);

        $res = $this->getJson('/api/teacher-attendance');

        $res->assertOk();
        $this->assertStringContainsString(
            '/storage/teachers/nested.jpg',
            $res->json('data.0.teacher.photo')
        );
    }
}
