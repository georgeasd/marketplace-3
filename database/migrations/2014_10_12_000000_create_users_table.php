<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for 'users' table creation
 */
class CreateUsersTable extends Migration
{
    /**
     * Table name
     */
    const TABLE = 'users';
    /**
     * Search index name
     */
    const SEARCH_INDEX = 'i_users_search';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->increments('id');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('activation_token');
            $table->string('display_name')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->string('options')->nullable();
            $table->tinyInteger('status')->comment('0 == inactive, 1 == active, 2 == banned');
            $table->text('description')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index(['email', 'username', 'display_name'], self::SEARCH_INDEX);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(self::TABLE);
    }
}
