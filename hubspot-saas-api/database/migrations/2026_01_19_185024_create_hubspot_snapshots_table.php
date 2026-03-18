<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hubspot_snapshots', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('portal_id');

            $table->string('company_name')->nullable();
            $table->string('region')->nullable();
            $table->string('timezone')->nullable();

            $table->unsignedInteger('contacts')->default(0);
            $table->unsignedInteger('companies')->default(0);
            $table->unsignedInteger('deals')->default(0);

            $table->date('snapshot_date');

            $table->timestamps();

            $table->unique(['portal_id', 'snapshot_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hubspot_snapshots');
    }
};
