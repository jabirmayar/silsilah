<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFamilyIdToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('family_id')->nullable()->after('parent_id'); // Add family_id column after parent_id
            $table->foreign('family_id')->references('id')->on('families')->onDelete('set null'); // Foreign key constraint to families table
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
            $table->dropForeign(['family_id']); // Drop the foreign key constraint
            $table->dropColumn('family_id'); // Drop the family_id column
        });
    }
}
