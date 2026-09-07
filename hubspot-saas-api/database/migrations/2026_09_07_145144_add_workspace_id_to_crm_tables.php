<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | COMPANIES
        |--------------------------------------------------------------------------
        */
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('workspace_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | CLIENTS
        |--------------------------------------------------------------------------
        */
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('workspace_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | DEALS
        |--------------------------------------------------------------------------
        */
        Schema::table('deals', function (Blueprint $table) {
            $table->foreignId('workspace_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('workspace_id');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('workspace_id');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('workspace_id');
        });
    }
};
