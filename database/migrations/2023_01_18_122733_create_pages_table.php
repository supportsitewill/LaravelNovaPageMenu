<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->integer('order')->unsigned();
            $table->integer('parent_id')->unsigned()->nullable()->default(0);
            $table->boolean('active')->default(0);
            $table->boolean('hide')->default(0);
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('external_link')->nullable();
            $table->boolean('blank')->default(0);
            $table->string('headline')->nullable();
            $table->longText('body')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->enum('type', ['Home', 'Contacts', '404'])->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pages');
    }
};
