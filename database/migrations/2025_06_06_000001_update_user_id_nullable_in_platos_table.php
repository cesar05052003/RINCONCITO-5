<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateUserIdNullableInPlatosTable extends Migration
{
    protected function foreignKeyExists($table, $foreignKey)
    {
        $databaseName = DB::getDatabaseName();
        $result = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = ? 
              AND TABLE_NAME = ? 
              AND CONSTRAINT_NAME = ?
        ", [$databaseName, $table, $foreignKey]);

        return !empty($result);
    }

    public function up()
    {
        if (Schema::hasColumn('platos', 'user_id')) {
            $foreignKeyName = 'platos_user_id_foreign';
            if ($this->foreignKeyExists('platos', $foreignKeyName)) {
                Schema::table('platos', function (Blueprint $table) use ($foreignKeyName) {
                    $table->dropForeign($foreignKeyName);
                });
            }

            Schema::table('platos', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });

            Schema::table('platos', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('platos', 'user_id')) {
            $foreignKeyName = 'platos_user_id_foreign';
            if ($this->foreignKeyExists('platos', $foreignKeyName)) {
                Schema::table('platos', function (Blueprint $table) use ($foreignKeyName) {
                    $table->dropForeign($foreignKeyName);
                });
            }

            Schema::table('platos', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable(false)->change();
            });

            Schema::table('platos', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }
}
