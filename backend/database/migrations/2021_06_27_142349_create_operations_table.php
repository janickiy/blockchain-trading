<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('create table if not exists operations
        (
            id                uuid      default gen_random_uuid() not null
                constraint operations_pk
                    primary key,
            from_account_id   uuid                                not null,
            action            smallint                            not null,
            target_public_key varchar                             not null,
            target_username   varchar                             not null,
            amount            numeric(18, 9)                      not null,
            condition         smallint                            not null,
            condition_payload jsonb                               not null,
            status            smallint  default 0                 not null,
            is_signal_only    boolean   default false             not null,
            result            jsonb,
            created_at        timestamp default now()             not null,
            updated_at        timestamp default now()             not null
        );');

        DB::statement('alter table operations drop column if exists is_signal_only;');
        DB::statement('alter table operations add column if not exists is_cloner boolean default false not null;');
        DB::statement('alter table operations add column if not exists attempts smallint default 0 not null;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('operations');
    }
}
