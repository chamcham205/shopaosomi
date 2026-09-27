<?php

namespace App\Models;

use App\Support\PublicImage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable=['title','subtitle','image_path','button_text','target_url','sort_order','status','starts_at','ends_at'];
    protected function casts(): array { return ['sort_order'=>'integer','starts_at'=>'datetime','ends_at'=>'datetime']; }
    public function scopeVisible(Builder $query): Builder { return $query->where('status','active')->where(fn($q)=>$q->whereNull('starts_at')->orWhere('starts_at','<=',now()))->where(fn($q)=>$q->whereNull('ends_at')->orWhere('ends_at','>=',now())); }
    public function getImageUrlAttribute(): string
    {
        return PublicImage::url($this->image_path, 'assets/images/banner-fallback.svg');
    }
}
