<?php

namespace Tests\Feature;

use App\Livewire\Partials\TeacherSelect;
use App\Models\Book;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

class KitabAdminTest extends PublicPageTestCase
{
    private function payload(array $teachers, array $overrides = []): array
    {
        return array_merge([
            'title' => 'Sabilal Muhtadin',
            'title_arabic' => 'سبيل المهتدين',
            'language' => 'arab_melayu',
            'publication_status' => 'tercetak',
            'teachers' => $teachers,
            'is_active' => '1',
        ], $overrides);
    }

    private function makeBook(Teacher $teacher, array $attributes = []): Book
    {
        $book = Book::create(array_merge([
            'title' => 'Kitab Uji',
            'slug' => Book::generateSlug($attributes['title'] ?? 'Kitab Uji'),
            'is_active' => true,
        ], $attributes));
        $book->teachers()->attach($teacher->id, ['role' => 'pengarang', 'sort_order' => 0]);

        return $book;
    }

    public function test_non_admin_tidak_bisa_mengakses_admin_kitab(): void
    {
        $teacher = $this->makeTeacher();
        $book = $this->makeBook($teacher);

        Role::firstOrCreate(['name' => 'Penulis', 'guard_name' => 'web']);
        $penulis = User::factory()->create(['email_verified_at' => now()]);
        $penulis->assignRole('Penulis');

        foreach ([User::factory()->create(['email_verified_at' => now()]), $this->kontributor(), $penulis] as $user) {
            $this->actingAs($user);
            $this->get(route('admin.kitab.index'))->assertForbidden();
            $this->get(route('admin.kitab.create'))->assertForbidden();
            $this->post(route('admin.kitab.store'), $this->payload([['teacher_id' => $teacher->id, 'role' => 'pengarang']]))->assertForbidden();
            $this->put(route('admin.kitab.update', $book), $this->payload([['teacher_id' => $teacher->id, 'role' => 'pengarang']]))->assertForbidden();
            $this->delete(route('admin.kitab.destroy', $book))->assertForbidden();
        }

        $this->assertDatabaseCount('books', 1);
    }

    public function test_admin_membuat_kitab_dengan_dua_guru_dan_sampul(): void
    {
        Storage::fake('public');
        $arsyad = $this->makeTeacher(['name' => 'Syekh Muhammad Arsyad', 'wafat_masehi' => '1812-10-03']);
        $pensyarah = $this->makeTeacher(['name' => 'Guru Pensyarah']);

        $this->actingAs($this->superAdmin())
            ->post(route('admin.kitab.store'), $this->payload([
                'b' => ['teacher_id' => $arsyad->id, 'role' => 'pengarang'],
                'a' => ['teacher_id' => $pensyarah->id, 'role' => 'pensyarah'],
            ], ['cover_image' => UploadedFile::fake()->image('cover.jpg', 600, 800)]))
            ->assertRedirect(route('admin.kitab.index'))
            ->assertSessionHasNoErrors();

        $book = Book::firstOrFail();
        $this->assertSame('sabilal-muhtadin', $book->slug);
        $this->assertTrue($book->is_active);
        $this->assertFalse($book->is_featured);
        $this->assertStringStartsWith('books/large/', $book->cover_image);
        Storage::disk('public')->assertExists($book->cover_image);
        Storage::disk('public')->assertExists(str_replace('/large/', '/thumb/', $book->cover_image));

        $pivots = $book->teachers()->get();
        $this->assertSame([$arsyad->id, $pensyarah->id], $pivots->pluck('id')->all());
        $this->assertSame(['pengarang', 'pensyarah'], $pivots->pluck('pivot.role')->all());
        $this->assertSame([0, 1], $pivots->pluck('pivot.sort_order')->map(fn ($v) => (int) $v)->all());
    }

    public function test_satu_guru_boleh_memegang_dua_peran_berbeda(): void
    {
        $teacher = $this->makeTeacher();

        $this->actingAs($this->superAdmin())
            ->post(route('admin.kitab.store'), $this->payload([
                ['teacher_id' => $teacher->id, 'role' => 'pengarang'],
                ['teacher_id' => $teacher->id, 'role' => 'pensyarah'],
            ]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('book_teacher', 2);
    }

    public function test_validasi_guru_dan_tautan(): void
    {
        $admin = $this->superAdmin();
        $teacher = $this->makeTeacher();
        $pending = $this->makeTeacher(['name' => 'Guru Pending', 'contribution_status' => 'pending']);

        $this->actingAs($admin)
            ->post(route('admin.kitab.store'), $this->payload([]))
            ->assertSessionHasErrors('teachers');

        $this->post(route('admin.kitab.store'), $this->payload([
            ['teacher_id' => $teacher->id, 'role' => 'pengarang'],
            ['teacher_id' => $teacher->id, 'role' => 'pengarang'],
        ]))->assertSessionHasErrors('teachers');

        $this->post(route('admin.kitab.store'), $this->payload([
            ['teacher_id' => $pending->id, 'role' => 'pengarang'],
        ]))->assertSessionHasErrors('teachers.0.teacher_id');

        $this->post(route('admin.kitab.store'), $this->payload([
            ['teacher_id' => $teacher->id, 'role' => 'penulis'],
        ]))->assertSessionHasErrors('teachers.0.role');

        $this->post(route('admin.kitab.store'), $this->payload(
            [['teacher_id' => $teacher->id, 'role' => 'pengarang']],
            ['external_url' => 'javascript:alert(1)']
        ))->assertSessionHasErrors('external_url');

        $this->assertDatabaseCount('books', 0);
    }

    public function test_update_mengganti_guru_dan_sampul_tanpa_mengubah_slug(): void
    {
        Storage::fake('public');
        $admin = $this->superAdmin();
        $lama = $this->makeTeacher(['name' => 'Guru Lama']);
        $baru = $this->makeTeacher(['name' => 'Guru Baru']);

        $this->actingAs($admin)->post(route('admin.kitab.store'), $this->payload(
            [['teacher_id' => $lama->id, 'role' => 'pengarang']],
            ['cover_image' => UploadedFile::fake()->image('a.jpg', 300, 400)]
        ));
        $book = Book::firstOrFail();
        $sampulLama = $book->cover_image;

        $this->put(route('admin.kitab.update', $book), $this->payload(
            [['teacher_id' => $baru->id, 'role' => 'penerjemah']],
            ['title' => 'Judul Diganti', 'cover_image' => UploadedFile::fake()->image('b.jpg', 300, 400)]
        ))->assertSessionHasNoErrors();

        $book->refresh();
        $this->assertSame('Judul Diganti', $book->title);
        $this->assertSame('sabilal-muhtadin', $book->slug);
        $this->assertSame([$baru->id], $book->teachers()->pluck('teachers.id')->all());
        $this->assertSame('penerjemah', $book->teachers()->first()->pivot->role);
        Storage::disk('public')->assertMissing($sampulLama);
        Storage::disk('public')->assertMissing(str_replace('/large/', '/thumb/', $sampulLama));
        Storage::disk('public')->assertExists($book->cover_image);
    }

    public function test_slug_bisa_diubah_eksplisit_dan_bentrok_diberi_sufiks(): void
    {
        $admin = $this->superAdmin();
        $teacher = $this->makeTeacher();
        $rows = [['teacher_id' => $teacher->id, 'role' => 'pengarang']];

        $this->actingAs($admin)->post(route('admin.kitab.store'), $this->payload($rows));
        $this->post(route('admin.kitab.store'), $this->payload($rows));

        $this->assertSame(['sabilal-muhtadin', 'sabilal-muhtadin-1'], Book::orderBy('id')->pluck('slug')->all());

        $book = Book::where('slug', 'sabilal-muhtadin-1')->firstOrFail();
        $this->put(route('admin.kitab.update', $book), $this->payload($rows, ['slug' => 'syarah-sabilal']))
            ->assertSessionHasNoErrors();
        $book->refresh();
        $this->assertSame('syarah-sabilal', $book->slug);

        $this->put(route('admin.kitab.update', $book), $this->payload($rows, ['slug' => 'sabilal-muhtadin']))
            ->assertSessionHasErrors('slug');
    }

    public function test_deskripsi_disanitasi(): void
    {
        $teacher = $this->makeTeacher();

        $this->actingAs($this->superAdmin())->post(route('admin.kitab.store'), $this->payload(
            [['teacher_id' => $teacher->id, 'role' => 'pengarang']],
            ['description' => '<script>alert(1)</script><p>Kitab fiqih</p>']
        ));

        $description = Book::firstOrFail()->description;
        $this->assertStringNotContainsString('<script', $description);
        $this->assertStringContainsString('Kitab fiqih', $description);
    }

    public function test_hapus_kitab_menghapus_pivot_dan_sampul_tetapi_guru_tetap(): void
    {
        Storage::fake('public');
        $teacher = $this->makeTeacher();

        $this->actingAs($this->superAdmin())->post(route('admin.kitab.store'), $this->payload(
            [['teacher_id' => $teacher->id, 'role' => 'pengarang']],
            ['cover_image' => UploadedFile::fake()->image('a.jpg', 300, 400)]
        ));
        $book = Book::firstOrFail();

        $this->delete(route('admin.kitab.destroy', $book))->assertRedirect(route('admin.kitab.index'));

        $this->assertDatabaseCount('books', 0);
        $this->assertDatabaseCount('book_teacher', 0);
        $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);
        Storage::disk('public')->assertMissing($book->cover_image);
    }

    public function test_guru_yang_terhubung_kitab_tidak_bisa_dihapus(): void
    {
        $teacher = $this->makeTeacher();
        $this->makeBook($teacher);

        $this->expectException(QueryException::class);

        $teacher->delete();
    }

    public function test_form_admin_dapat_dirender(): void
    {
        $teacher = $this->makeTeacher();
        $book = $this->makeBook($teacher);
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('admin.kitab.index'))->assertOk()->assertSee('Kitab Uji');
        $this->get(route('admin.kitab.create'))->assertOk();
        $this->get(route('admin.kitab.edit', $book))->assertOk()->assertSee($teacher->name);
    }

    public function test_teacher_select_default_tetap_menyaring_guru_wafat(): void
    {
        $this->makeTeacher(['name' => 'Ahmad Hidup']);
        $this->makeTeacher(['name' => 'Ahmad Wafat', 'wafat_masehi' => '1990-01-01']);
        $this->makeTeacher(['name' => 'Ahmad Pending', 'contribution_status' => 'pending']);

        Livewire::test(TeacherSelect::class)
            ->set('search', 'Ahmad')
            ->assertSee('Ahmad Hidup')
            ->assertDontSee('Ahmad Wafat')
            ->assertSee('Ahmad Pending');

        Livewire::test(TeacherSelect::class, ['includeWafat' => true, 'onlyPublic' => true])
            ->set('search', 'Ahmad')
            ->assertSee('Ahmad Hidup')
            ->assertSee('Ahmad Wafat')
            ->assertDontSee('Ahmad Pending');
    }
}
