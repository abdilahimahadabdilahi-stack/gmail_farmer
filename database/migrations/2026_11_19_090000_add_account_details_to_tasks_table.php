<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'account_email')) {
                $table->string('account_email')->nullable();
            }

            if (! Schema::hasColumn('tasks', 'account_code')) {
                $table->string('account_code')->nullable();
            }

            $table->string('proof_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['account_email', 'account_code']);
        });
    }
};
