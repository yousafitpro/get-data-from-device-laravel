<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('bank_id')->nullable();
            $table->bigInteger('loc_bank_id')->nullable();
            $table->bigInteger('business_id')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('plaid_access_token');
            $table->string('railz_ai_access_token');
            $table->string('business_name');
            $table->string('plaid_account_title');
            $table->string('plaid_loc_account_title');
            $table->string('plaid_loc_access_token');
            $table->boolean('is_active')->default(0)->nullable();
            $table->boolean('is_railz_ai_con')->default(0)->nullable();
            $table->boolean('is_bankinfo_config')->default(0)->nullable();
            $table->boolean('is_loc_bankinfo_config')->default(0)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
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
