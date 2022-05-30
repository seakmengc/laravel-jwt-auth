<?php

namespace App\Services\Vendor;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PHPOpenSourceSaver\JWTAuth\Contracts\Providers\Storage;

class JwtDBStorage implements Storage
{
    /**
     * @param string $key
     * @param mixed  $value
     * @param int    $minutes
     *
     * @return void
     */
    public function add($key, $value, $minutes)
    {
        $this->getTable()->updateOrInsert([
            'jti' => $key,
        ], [
            'value' => json_encode($value),
            'expires_at' => now()->addMinutes($minutes)
        ]);
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function forever($key, $value)
    {
        $this->getTable()->updateOrInsert([
            'jti' => $key,
        ], [
            'value' => json_encode($value),
            'expires_at' => null,
        ]);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        $val = $this->getQueryByKey($key)->first()?->value;

        return json_decode($val ?? '', true);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function destroy($key)
    {
        return $this->getQueryByKey($key)->limit(1)->delete() > 1;
    }

    /**
     * @return void
     */
    public function flush()
    {
        $this->getTable()->truncate();
    }

    private function getTable()
    {
        return DB::table('blacklisted_tokens');
    }

    private function getQueryByKey(string $key)
    {
        return $this->getTable()->select('value')->where('jti', $key);
    }
}