<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
  {
      Schema::table('agenda_rapat', function (Blueprint $table) {
          $table->string('ttd_pemimpin')->nullable();
      });
  }

  public function down()
  {
      Schema::table('agenda_rapat', function (Blueprint $table) {
          $table->dropColumn('ttd_pemimpin');
      });
  }
};
