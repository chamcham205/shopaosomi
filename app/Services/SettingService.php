<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingService
{
    private const CACHE_KEY='site_settings';
    private const DEFAULTS=['site_name'=>'SHIRT STORE','site_description'=>'Áo sơ mi nam chỉn chu cho mọi ngày.','contact_email'=>'','contact_phone'=>'','address'=>'','low_stock_threshold'=>'5','default_theme'=>'system','announcement'=>''];

    public function all(): array { return Cache::rememberForever(self::CACHE_KEY,fn()=>array_merge(self::DEFAULTS,Setting::pluck('value','key')->all())); }
    public function get(string $key,mixed $fallback=null): mixed { return $this->all()[$key]??$fallback; }
    public function lowStockThreshold(): int { return max(0,(int)$this->get('low_stock_threshold',5)); }
    public function put(array $values): void { DB::transaction(fn()=>collect($values)->each(fn($value,$key)=>Setting::updateOrCreate(['key'=>$key],['value'=>$value]))); Cache::forget(self::CACHE_KEY); }
}
