<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('create table if not exists accounts
        (
            id         uuid      default gen_random_uuid() not null
                constraint accounts_pk
                    primary key,
            mnemonic   varchar                             not null,
            password   varchar                             not null,
            public_key varchar                             not null,
            username   varchar                             not null,
            created_at timestamp default now()             not null,
            updated_at timestamp default now()             not null
        );');

        DB::statement('create unique index if not exists accounts_public_key_uindex on accounts (public_key);');
        DB::statement('alter table accounts add column if not exists balance bigint default 0 not null;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP TABLE accounts');
    }
}
