<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubFamilyIdToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('sub_family_id')->nullable()->after('family_id'); // Add sub_family_id after family_id
            $table->foreign('sub_family_id')->references('id')->on('families')->onDelete('set null'); // FK to families
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sub_family_id']); // Drop FK
            $table->dropColumn('sub_family_id');     // Drop the column
        });
    }
}
