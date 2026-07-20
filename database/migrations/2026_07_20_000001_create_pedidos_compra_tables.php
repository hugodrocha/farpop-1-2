<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pedidos_compra')) {
            return;
        }

        Schema::create('pedidos_compra', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->unsignedBigInteger('clinica_id')->nullable()->index();
            $table->string('status', 20)->default('rascunho')->index();
            $table->boolean('agrupado')->default(false);
            $table->text('observacao')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('usuario_nome', 120)->nullable();
            $table->timestamp('enviado_em')->nullable();
            $table->timestamp('atendido_em')->nullable();
            $table->timestamp('criado_em')->useCurrent();
            $table->timestamp('atualizado_em')->nullable()->useCurrentOnUpdate();
        });

        Schema::create('pedidos_compra_itens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pedido_id')->index();
            $table->unsignedBigInteger('medicamento_id')->nullable();
            $table->unsignedBigInteger('clinica_id')->nullable();
            $table->string('unidade_nome', 180)->nullable();
            $table->string('codigo', 40)->nullable();
            $table->string('medicamento', 255);
            $table->string('dosagem', 80)->nullable();
            $table->unsignedInteger('estoque_atual')->default(0);
            $table->unsignedInteger('qtd_pedido')->default(0);
            $table->string('prioridade', 40)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos_compra_itens');
        Schema::dropIfExists('pedidos_compra');
    }
};
