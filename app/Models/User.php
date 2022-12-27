<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    protected $fillable = [
        'displayName', 'email', 'localId',
    ];
    public function getAuthIdentifierName()
    {
        return 'localId';
    }
    public function getAuthIdentifier()
    {
        return $this->localId;
    }
}
