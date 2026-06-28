<?php

namespace App\Models;

use App\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\Village;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasRoles;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'province_code',
        'city_code',
        'district_code',
        'village_code',
        'gender',
        'birth_year',
        'one_signal_id',
        'google_id',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'kontributor_since' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }

    public function province()
    {
        // Parameter: (Model Tujuan, foreign_key_lokal, owner_key_di_tabel_tujuan)
        return $this->belongsTo(Province::class, 'province_code', 'code');
    }

    /**
     * Relasi ke Kota/Kabupaten
     *
     * * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city()
    {
        return $this->belongsTo(City::class, 'city_code', 'code');
    }

    /**
     * Relasi ke Kecamatan
     *
     * * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function district()
    {
        return $this->belongsTo(District::class, 'district_code', 'code');
    }

    /**
     * Relasi ke Desa/Kelurahan
     *
     * * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function village()
    {
        return $this->belongsTo(Village::class, 'village_code', 'code');
    }

    public function assemblies()
    {
        return $this->hasMany(Assembly::class);
    }

    public function contributions()
    {
        return $this->hasMany(Contribution::class);
    }

    public function followingAssemblies()
    {
        return $this->belongsToMany(Assembly::class, 'assembly_user');
    }

    public function likedWirids()
    {
        return $this->belongsToMany(Wirid::class, 'wirid_user');
    }

    public function scheduleNotes()
    {
        return $this->hasMany(ScheduleNote::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function updateBadge(): bool
    {
        $badge = match (true) {
            $this->total_khidmah_points >= 501 => 'Khadam Syaikhuna',
            $this->total_khidmah_points >= 101 => 'Penuntut Ilmu',
            default => 'Jamaah Aktif',
        };

        if ($this->badge_title !== $badge) {
            $this->badge_title = $badge;
            $this->save();

            return true;
        }

        return false;
    }

    public function nextBadgeThreshold(): ?int
    {
        return match ($this->badge_title) {
            'Jamaah Aktif' => 101,
            'Penuntut Ilmu' => 501,
            default => null,
        };
    }

    public function foundations()
    {
        return $this->belongsToMany(Foundation::class, 'foundation_user');
    }

    public function rewardClaims()
    {
        return $this->hasMany(RewardClaim::class);
    }

    /**
     * Apakah user pernah berhasil menerima reward (klaim berstatus paid).
     * Reward bersifat sekali seumur hidup.
     */
    public function hasPaidRewardClaim(): bool
    {
        return $this->rewardClaims()
            ->where('status', RewardClaim::STATUS_PAID)
            ->exists();
    }

    /**
     * Apakah user berhak mengajukan klaim reward saat ini (evaluasi snapshot).
     * Syarat: program aktif, role Kontributor, XP >= threshold, belum pernah paid,
     * dan tidak sedang memiliki klaim pending.
     */
    public function eligibleForReward(): bool
    {
        $setting = RewardSetting::current();

        if (! $setting->is_active) {
            return false;
        }

        if (! $this->hasRole('Kontributor')) {
            return false;
        }

        if ($this->total_khidmah_points < $setting->min_xp) {
            return false;
        }

        if ($this->hasPaidRewardClaim()) {
            return false;
        }

        return ! $this->rewardClaims()
            ->where('status', RewardClaim::STATUS_PENDING)
            ->exists();
    }

    /**
     * Bentuk username unik dari nama untuk URL profil publik kontributor.
     * Menambahkan suffix angka jika terjadi bentrokan.
     */
    public function generateUniqueUsername(): string
    {
        $base = Str::slug($this->name) ?: 'kontributor';
        $username = $base;
        $count = 1;

        while (static::where('username', $username)
            ->where('id', '!=', $this->id)
            ->exists()) {
            $username = $base.'-'.$count++;
        }

        return $username;
    }
}
