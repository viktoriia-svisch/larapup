<?php
namespace Illuminate\Auth\Passwords;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
class DatabaseTokenRepository implements TokenRepositoryInterface
{
    protected $connection;
    protected $hasher;
    protected $table;
    protected $hashKey;
    protected $expires;
    public function __construct(ConnectionInterface $connection, HasherContract $hasher,
                                $table, $hashKey, $expires = 60)
    {
        $this->table = $table;
        $this->hasher = $hasher;
        $this->hashKey = $hashKey;
        $this->expires = $expires * 60;
        $this->connection = $connection;
    }
    public function create(CanResetPasswordContract $user)
    {
        $email = $user->getEmailForPasswordReset();
        $this->deleteExisting($user);
        $token = $this->createNewToken();
        $this->getTable()->insert($this->getPayload($email, $token));
        return $token;
    }
    protected function deleteExisting(CanResetPasswordContract $user)
    {
        return $this->getTable()->where('email', $user->getEmailForPasswordReset())->delete();
    }
    protected function getPayload($email, $token)
    {
        return ['email' => $email, 'token' => $this->hasher->make($token), 'created_at' => new Carbon];
    }
    public function exists(CanResetPasswordContract $user, $token)
    {
        $record = (array) $this->getTable()->where(
            'email', $user->getEmailForPasswordReset()
        )->first();
        return $record &&
               ! $this->tokenExpired($record['created_at']) &&
                 $this->hasher->check($token, $record['token']);
    }
    protected function tokenExpired($createdAt)
    {
        return Carbon::parse($createdAt)->addSeconds($this->expires)->isPast();
    }
    public function delete(CanResetPasswordContract $user)
    {
        $this->deleteExisting($user);
    }
    public function deleteExpired()
    {
        $expiredAt = Carbon::now()->subSeconds($this->expires);
        $this->getTable()->where('created_at', '<', $expiredAt)->delete();
    }
    public function createNewToken()
    {
        return hash_hmac('sha256', Str::random(40), $this->hashKey);
    }
    public function getConnection()
    {
        return $this->connection;
    }
    protected function getTable()
    {
        return $this->connection->table($this->table);
    }
    public function getHasher()
    {
        return $this->hasher;
    }
}
