<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->integer('category_id');
            $table->string('product_name');
            $table->string('sku');
            $table->string('qty');
            $table->string('price');
<<<<<<< HEAD
            $table->string('sale_price');
=======
            $table->string('discount_percentage');
>>>>>>> bc40bab051467a571c4fee195a934ea1931e57a7
            $table->string('dimension')->nullable();
            $table->string('weight')->nullable();
            $table->string('brand')->nullable();
            $table->string('size')->nullable();
            $table->string('status')->nullable();
            $table->string('contact');
            $table->json('colors')->nullable();
            $table->string('bike')->nullable();
            $table->string('van')->nullable();
            $table->string('feature_img')->nullable();


//            $table->string('feature_img');
//            $table->text('product_description');
//            $table->string('color');
//            $table->string('size');
//            $table->string('price');
//            $table->string('qty');
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
        Schema::dropIfExists('products');
    }
}
