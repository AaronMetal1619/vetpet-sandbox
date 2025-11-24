<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubscriptionFieldsToUsersTable extends Migration
{
  public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('subscription_type')->nullable();
        $table->boolean('subscription_active')->default(false);
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['subscription_type', 'subscription_active']);
    });
}

}
