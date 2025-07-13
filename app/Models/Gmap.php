<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Storage;


class Gmap extends Model
{
    use HasFactory, HasUuids;

    /**
     * 主キーがUUIDなので incrementing をfalse
     */
    public $incrementing = false;

    /**
     * 主キーの型
     */
    protected $keyType = 'string';

    /**
     * 一括代入を許可する属性
     */
    protected $fillable = [
        'title',
        'comment',
        'latitude',
        'longitude',
        'picture_path',
        'magic_word',
        'user_id',
    ];

    protected $appends = ['picture_url'];

    /**
     * ユーザーとのリレーション
     * Gmap belongs to a User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * magic_wordをセットする際にMD5ハッシュ化
     */
    public function setMagicWordAttribute($value)
    {
        $this->attributes['magic_word'] = $value ? md5($value) : '';
    }


    public function getPictureUrlAttribute()
    {
        if (! $this->picture_path) {
            return null;
        }
        return Storage::disk('public')->url($this->picture_path);
    }
}
