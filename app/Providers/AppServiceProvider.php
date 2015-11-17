<?php

namespace Oplan\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Hashing\Hasher;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app['hash'] = $this->app->share(function () {
            return new MD5Hasher();
        });
    }
    
    
    public function provides() {
        return array('hash');
    }
    
}



class MD5Hasher implements Hasher {

    public function make($value, array $options = array()) {
        return hash('md5', $value);
    }

    public function check($value, $hashedValue, array $options = array()) {
        return $this->make($value) === $hashedValue;
    }

    public function needsRehash($hashedValue, array $options = array()) {
        return false;
    }
}
