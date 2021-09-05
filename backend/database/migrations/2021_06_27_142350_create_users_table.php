<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('create table if not exists users
        (
            id         uuid      default gen_random_uuid() not null
                constraint users_pk
                    primary key,
            email      varchar                             not null,
            password   varchar                             not null,
            created_at timestamp default now()             not null,
            updated_at timestamp default now()             not null
        );');
        DB::statement('create unique index if not exists users_email_uindex on users (email);');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
