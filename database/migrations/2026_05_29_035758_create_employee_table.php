<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->integer('designation_id')->index();
            $table->integer('department_id')->index();
            $table->string('employee_code')->unique(); 
            $table->string('employee_name'); 
            $table->string('epf_number')->nullable();             
            $table->string('nic_number')->unique();           
            $table->date('date_joined');        
            $table->date('date_resigned')->nullable();
            $table->string('gender')->nullable(); 
            $table->string('address')->nullable();             
            $table->text('remarks')->nullable();
            $table->boolean('status')->default(1);
            $table->unsignedBigInteger('last_updated_by');
            $table->timestamp('last_updated_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
