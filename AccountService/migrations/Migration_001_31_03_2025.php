<?php
namespace Database\Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

class Migration_001_31_03_2025
{
    public function up(): void
    {
        Capsule::schema()->create('accounts', function (Blueprint $table) {
            // Adicione suas colunas aqui
            $table->increments('id');
            $table->bigInteger("userId");
            $table->string("userEmail");
            $table->string("name");
            $table->text("description");
            $table->boolean("status")->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Capsule::schema()->dropIfExists('accounts');
    }
}