<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('series', function($table)
		{
		    $table->increments('id');
		    $table->string('name');
		    $table->string('language');
		    $table->text('overview');
		    $table->bigInteger('firstaired');
		    $table->string('imdb_id')->nullable();
		    $table->integer('tvdb_id')->nullable();
		    $table->integer('tvrage_id')->nullable();
		    $table->integer('network_id')->nullable();
		    $table->string('airday')->nullable();
		    $table->string('airtime')->nullable();
		    $table->string('contentrating')->nullable();
		    $table->integer('runtime')->nullable();
		    $table->string('status')->nullable();
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
		//
	}

}
