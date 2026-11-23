<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
 public function up(): void { Schema::table('users', function(Blueprint $t){ $t->text('roles')->nullable()->after('role'); }); DB::table('users')->whereNull('roles')->update(['roles'=>DB::raw("json_array(role)")]); }
 public function down(): void { Schema::table('users', fn(Blueprint $t)=>$t->dropColumn('roles')); }
};
