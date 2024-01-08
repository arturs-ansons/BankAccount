<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;
    use Laravel\Sanctum\HasApiTokens;

    class User extends Authenticatable
    {
        use HasApiTokens, HasFactory, Notifiable;

        protected $fillable = [
            'firstname',
            'lastname',
            'email',
            'password',
        ];

        protected $hidden = [
            'password',
        ];

        protected $casts = [];

        public function balances(): HasMany
        {
            return $this->hasMany(Balance::class, 'user_id', 'id');
        }

        public function transaction(): HasMany
        {
            return $this->hasMany(Transaction::class, 'user_id', 'id');
        }

    }
