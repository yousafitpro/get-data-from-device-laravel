<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('is_bankinfo_config')->nullable()->default(0);
            $table->string('counterparty_id')->nullable()->default('N/A');
            $table->string('ledger_id')->nullable()->default('N/A');
            $table->string('ledger_account_id')->nullable()->default('N/A');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function($table) {
            $table->dropColumn('is_bankinfo_config');
            $table->dropColumn('counterparty_id');
            $table->dropColumn('ledger_id');
            $table->dropColumn('ledger_account_id');
        });
    }
}
