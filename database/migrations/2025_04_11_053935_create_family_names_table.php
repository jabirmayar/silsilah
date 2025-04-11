<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFamilyNamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('families', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->uuid('parent_id')->nullable(); // For hierarchy (parent family)
            $table->timestamps();
            $table->index('parent_id');
        });

        // Add foreign key constraint after table creation
        Schema::table('families', function (Blueprint $table) {
            $table->foreign('parent_id')
                ->references('id')
                ->on('families')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('families');
    }
}