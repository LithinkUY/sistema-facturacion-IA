<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // Agregar columna solo si no existe (SQLite):
        try {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->integer('business_id')->after('subject_type')->nullable();
            });
        } catch (\Exception $e) {}

        // Solo actualizar datos si la clase existe (si el paquete está instalado)
        if (class_exists('Spatie\\Activitylog\\Models\\Activity')) {
            $activites = Activity::with(['causer'])->groupBy('causer_id')->get();

            foreach ($activites as $activity) {
                Activity::where('causer_id', $activity->causer_id)
                    ->update(['business_id' => $activity->causer->business_id ?? null]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
};
