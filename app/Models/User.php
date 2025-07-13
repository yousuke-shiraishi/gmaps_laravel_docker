<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * このモデルで一括代入を許可する属性
     *
     * ここに username, birth を追加
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'birth',
    ];

    /**
     * 属性を隠す（配列化するとき）
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * キャストする属性
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * JWTSubjectの必須メソッド
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * JWTSubjectの必須メソッド
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * ユーザーが持つGmap（1:Nリレーション）
     */
    public function gmaps()
    {
        return $this->hasMany(Gmap::class);
    }
}
