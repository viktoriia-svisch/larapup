<?php
namespace Tymon\JWTAuth\Providers;
use Namshi\JOSE\JWS;
use Tymon\JWTAuth\JWT;
use Tymon\JWTAuth\Factory;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Manager;
use Tymon\JWTAuth\JWTGuard;
use Tymon\JWTAuth\Blacklist;
use Lcobucci\JWT\Parser as JWTParser;
use Tymon\JWTAuth\Http\Parser\Parser;
use Tymon\JWTAuth\Http\Parser\Cookies;
use Illuminate\Support\ServiceProvider;
use Lcobucci\JWT\Builder as JWTBuilder;
use Tymon\JWTAuth\Providers\JWT\Namshi;
use Tymon\JWTAuth\Http\Middleware\Check;
use Tymon\JWTAuth\Providers\JWT\Lcobucci;
use Tymon\JWTAuth\Http\Parser\AuthHeaders;
use Tymon\JWTAuth\Http\Parser\InputSource;
use Tymon\JWTAuth\Http\Parser\QueryString;
use Tymon\JWTAuth\Http\Parser\RouteParams;
use Tymon\JWTAuth\Contracts\Providers\Auth;
use Tymon\JWTAuth\Contracts\Providers\Storage;
use Tymon\JWTAuth\Validators\PayloadValidator;
use Tymon\JWTAuth\Http\Middleware\Authenticate;
use Tymon\JWTAuth\Http\Middleware\RefreshToken;
use Tymon\JWTAuth\Claims\Factory as ClaimFactory;
use Tymon\JWTAuth\Console\JWTGenerateSecretCommand;
use Tymon\JWTAuth\Http\Middleware\AuthenticateAndRenew;
use Tymon\JWTAuth\Contracts\Providers\JWT as JWTContract;
abstract class AbstractServiceProvider extends ServiceProvider
{
    protected $middlewareAliases = [
        'jwt.auth' => Authenticate::class,
        'jwt.check' => Check::class,
        'jwt.refresh' => RefreshToken::class,
        'jwt.renew' => AuthenticateAndRenew::class,
    ];
    abstract public function boot();
    public function register()
    {
        $this->registerAliases();
        $this->registerJWTProvider();
        $this->registerAuthProvider();
        $this->registerStorageProvider();
        $this->registerJWTBlacklist();
        $this->registerManager();
        $this->registerTokenParser();
        $this->registerJWT();
        $this->registerJWTAuth();
        $this->registerPayloadValidator();
        $this->registerClaimFactory();
        $this->registerPayloadFactory();
        $this->registerJWTCommand();
        $this->commands('tymon.jwt.secret');
    }
    protected function extendAuthGuard()
    {
        $this->app['auth']->extend('jwt', function ($app, $name, array $config) {
            $guard = new JWTGuard(
                $app['tymon.jwt'],
                $app['auth']->createUserProvider($config['provider']),
                $app['request']
            );
            $app->refresh('request', $guard, 'setRequest');
            return $guard;
        });
    }
    protected function registerAliases()
    {
        $this->app->alias('tymon.jwt', JWT::class);
        $this->app->alias('tymon.jwt.auth', JWTAuth::class);
        $this->app->alias('tymon.jwt.provider.jwt', JWTContract::class);
        $this->app->alias('tymon.jwt.provider.jwt.namshi', Namshi::class);
        $this->app->alias('tymon.jwt.provider.jwt.lcobucci', Lcobucci::class);
        $this->app->alias('tymon.jwt.provider.auth', Auth::class);
        $this->app->alias('tymon.jwt.provider.storage', Storage::class);
        $this->app->alias('tymon.jwt.manager', Manager::class);
        $this->app->alias('tymon.jwt.blacklist', Blacklist::class);
        $this->app->alias('tymon.jwt.payload.factory', Factory::class);
        $this->app->alias('tymon.jwt.validators.payload', PayloadValidator::class);
    }
    protected function registerJWTProvider()
    {
        $this->registerNamshiProvider();
        $this->registerLcobucciProvider();
        $this->app->singleton('tymon.jwt.provider.jwt', function ($app) {
            return $this->getConfigInstance('providers.jwt');
        });
    }
    protected function registerNamshiProvider()
    {
        $this->app->singleton('tymon.jwt.provider.jwt.namshi', function ($app) {
            return new Namshi(
                new JWS(['typ' => 'JWT', 'alg' => $this->config('algo')]),
                $this->config('secret'),
                $this->config('algo'),
                $this->config('keys')
            );
        });
    }
    protected function registerLcobucciProvider()
    {
        $this->app->singleton('tymon.jwt.provider.jwt.lcobucci', function ($app) {
            return new Lcobucci(
                new JWTBuilder(),
                new JWTParser(),
                $this->config('secret'),
                $this->config('algo'),
                $this->config('keys')
            );
        });
    }
    protected function registerAuthProvider()
    {
        $this->app->singleton('tymon.jwt.provider.auth', function () {
            return $this->getConfigInstance('providers.auth');
        });
    }
    protected function registerStorageProvider()
    {
        $this->app->singleton('tymon.jwt.provider.storage', function () {
            return $this->getConfigInstance('providers.storage');
        });
    }
    protected function registerManager()
    {
        $this->app->singleton('tymon.jwt.manager', function ($app) {
            $instance = new Manager(
                $app['tymon.jwt.provider.jwt'],
                $app['tymon.jwt.blacklist'],
                $app['tymon.jwt.payload.factory']
            );
            return $instance->setBlacklistEnabled((bool) $this->config('blacklist_enabled'))
                            ->setPersistentClaims($this->config('persistent_claims'));
        });
    }
    protected function registerTokenParser()
    {
        $this->app->singleton('tymon.jwt.parser', function ($app) {
            $parser = new Parser(
                $app['request'],
                [
                    new AuthHeaders,
                    new QueryString,
                    new InputSource,
                    new RouteParams,
                    new Cookies($this->config('decrypt_cookies')),
                ]
            );
            $app->refresh('request', $parser, 'setRequest');
            return $parser;
        });
    }
    protected function registerJWT()
    {
        $this->app->singleton('tymon.jwt', function ($app) {
            return (new JWT(
                $app['tymon.jwt.manager'],
                $app['tymon.jwt.parser']
            ))->lockSubject($this->config('lock_subject'));
        });
    }
    protected function registerJWTAuth()
    {
        $this->app->singleton('tymon.jwt.auth', function ($app) {
            return (new JWTAuth(
                $app['tymon.jwt.manager'],
                $app['tymon.jwt.provider.auth'],
                $app['tymon.jwt.parser']
            ))->lockSubject($this->config('lock_subject'));
        });
    }
    protected function registerJWTBlacklist()
    {
        $this->app->singleton('tymon.jwt.blacklist', function ($app) {
            $instance = new Blacklist($app['tymon.jwt.provider.storage']);
            return $instance->setGracePeriod($this->config('blacklist_grace_period'))
                            ->setRefreshTTL($this->config('refresh_ttl'));
        });
    }
    protected function registerPayloadValidator()
    {
        $this->app->singleton('tymon.jwt.validators.payload', function () {
            return (new PayloadValidator)
                ->setRefreshTTL($this->config('refresh_ttl'))
                ->setRequiredClaims($this->config('required_claims'));
        });
    }
    protected function registerClaimFactory()
    {
        $this->app->singleton('tymon.jwt.claim.factory', function ($app) {
            $factory = new ClaimFactory($app['request']);
            $app->refresh('request', $factory, 'setRequest');
            return $factory->setTTL($this->config('ttl'))
                           ->setLeeway($this->config('leeway'));
        });
    }
    protected function registerPayloadFactory()
    {
        $this->app->singleton('tymon.jwt.payload.factory', function ($app) {
            return new Factory(
                $app['tymon.jwt.claim.factory'],
                $app['tymon.jwt.validators.payload']
            );
        });
    }
    protected function registerJWTCommand()
    {
        $this->app->singleton('tymon.jwt.secret', function () {
            return new JWTGenerateSecretCommand;
        });
    }
    protected function config($key, $default = null)
    {
        return config("jwt.$key", $default);
    }
    protected function getConfigInstance($key)
    {
        $instance = $this->config($key);
        if (is_string($instance)) {
            return $this->app->make($instance);
        }
        return $instance;
    }
}
