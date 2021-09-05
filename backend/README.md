Copy .env.example to .env

`php artisan key:generate`

Generate JWT secret

`php artisan jwt:secret`

Running The Scheduler

`* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1`

Run listen NSQ

`php artisan operation:listen-txns-events`

Artisan command create user

`php artisan user:create`
