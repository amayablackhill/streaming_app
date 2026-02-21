<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // 1. Eliminar columnas redundantes
            $table->dropColumn('user_id'); // Eliminar porque $table->id() ya crea la columna id
            $table->dropColumn('roleID'); // Eliminar porque usaremos role_id
            
            // 2. Asegurar que role_id existe como unsignedBigInteger antes de la FK
            if (!Schema::hasColumn('users', 'role_id')) {
                $table->unsignedBigInteger('role_id')->after('password');
            }
            
            // 3. Añadir la clave foránea (solo si la tabla roles existe)
            if (Schema::hasTable('roles')) {
                $table->foreign('role_id')->references('id')->on('roles');
            }
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
            // 1. Eliminar la clave foránea
            $table->dropForeign(['role_id']);
            
            // 2. Revertir los cambios (opcional, según tus necesidades)
            $table->integer('user_id')->nullable();
            $table->integer('roleID')->nullable();
        });
    }
};