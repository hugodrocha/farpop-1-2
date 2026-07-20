<?php



namespace App\Services;



use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;



class TransferenciaService

{

    public static function gerarCodigoNota(int $origemId, int $destinoId): string

    {

        $ts = now()->format('YmdHis');

        $o = str_pad((string) $origemId, 4, '0', STR_PAD_LEFT);

        $d = str_pad((string) $destinoId, 4, '0', STR_PAD_LEFT);

        $r = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $codigo = substr($ts . $o . $d . $r, 0, 26);



        return str_pad($codigo, 26, '0', STR_PAD_LEFT);

    }



    public static function codigoUnico(int $origemId, int $destinoId): string

    {

        for ($i = 0; $i < 5; $i++) {

            $codigo = self::gerarCodigoNota($origemId, $destinoId);

            if (!DB::table('transferencias')->where('codigo_nota', $codigo)->exists()) {

                return $codigo;

            }

        }



        throw new RuntimeException('Não foi possível gerar código único para a nota.');

    }



    public static function tipoParaMovimentacao(string $tipo): string

    {

        return match ($tipo) {

            'entre_unidades' => 'transferencia_entre_unidades',

            'unidade_matriz' => 'transferencia_unidade_matriz',

            default => 'transferencia_matriz_unidade',

        };

    }



    public static function labelTipo(?string $tipo): string

    {

        return match ($tipo ?? 'matriz_unidade') {

            'entre_unidades' => 'Entre unidades',

            'unidade_matriz' => 'Devolução à matriz',

            default => 'Matriz → unidade',

        };

    }



    public static function finalizarLote(int $centralId, int $destinoId, int $usuarioId, ?string $observacao, array $itens): int

    {

        return self::finalizarEnvio($centralId, $destinoId, $usuarioId, $observacao, $itens, 'matriz_unidade', $centralId);

    }



    public static function finalizarEnvio(

        int $origemId,

        int $destinoId,

        int $usuarioId,

        ?string $observacao,

        array $itens,

        string $tipo = 'matriz_unidade',

        ?int $centralId = null

    ): int {

        Fase5SchemaService::garantir();



        $centralId = $centralId ?? EstoqueService::getCentralId();



        if ($origemId <= 0 || $destinoId <= 0 || $origemId === $destinoId) {

            throw new RuntimeException('Origem e destino devem ser unidades diferentes.');

        }

        if (empty($itens)) {

            throw new RuntimeException('O lote deve ter pelo menos 1 medicamento.');

        }



        match ($tipo) {

            'matriz_unidade' => self::validarMatrizUnidade($origemId, $destinoId, $centralId),

            'unidade_matriz' => self::validarUnidadeMatriz($origemId, $destinoId, $centralId),

            'entre_unidades' => self::validarEntreUnidades($origemId, $destinoId, $centralId),

            default => throw new RuntimeException('Tipo de transferência inválido.'),

        };



        $movTipo = self::tipoParaMovimentacao($tipo);

        $origemNome = DB::table('clinicas')->where('id', $origemId)->value('nome') ?: "unidade #{$origemId}";



        $itensValidos = [];

        foreach ($itens as $i => $it) {

            $medId = (int) ($it['medicamento_id'] ?? 0);

            $qtd = (int) ($it['quantidade'] ?? 0);

            if ($medId <= 0 || $qtd <= 0) {

                throw new RuntimeException('Item #' . ($i + 1) . ' inválido.');

            }

            $est = EstoqueService::getAtual($origemId, $medId);

            if ($est < $qtd) {

                $nome = DB::table('medicamentos')->where('id', $medId)->value('nome') ?: "med#{$medId}";

                throw new RuntimeException("Estoque insuficiente em {$origemNome} para '{$nome}'. Disponível: {$est}, solicitado: {$qtd}.");

            }

            $itensValidos[] = [

                'medicamento_id' => $medId,

                'quantidade' => $qtd,

                'lote' => ($it['lote'] ?? '') !== '' ? $it['lote'] : null,

                'validade' => ValidadeService::normalizar($it['validade'] ?? null),

            ];

        }



        return DB::transaction(function () use ($origemId, $destinoId, $usuarioId, $observacao, $itensValidos, $tipo, $movTipo) {

            $codigoNota = self::codigoUnico($origemId, $destinoId);



            $payload = [

                'codigo_nota' => $codigoNota,

                'clinica_origem_id' => $origemId,

                'clinica_destino_id' => $destinoId,

                'medicamento_id' => null,

                'quantidade' => null,

                'observacao' => $observacao,

                'usuario_envio_id' => $usuarioId,

                'status' => 'pendente',

                'enviado_em' => now(),

            ];



            if (Schema::hasColumn('transferencias', 'tipo_transferencia')) {

                $payload['tipo_transferencia'] = $tipo;

            }



            $transferenciaId = DB::table('transferencias')->insertGetId($payload);



            foreach ($itensValidos as $it) {

                $itemId = DB::table('transferencia_itens')->insertGetId([

                    'transferencia_id' => $transferenciaId,

                    'medicamento_id' => $it['medicamento_id'],

                    'quantidade_enviada' => $it['quantidade'],

                    'quantidade_recebida' => 0,

                    'lote' => $it['lote'],

                    'validade' => $it['validade'],

                ]);



                LoteService::inicializar();

                LoteService::baixarPepe(

                    $origemId,

                    $it['medicamento_id'],

                    $it['quantidade'],

                    $movTipo,

                    'transferencia_item',

                    $itemId,

                    $usuarioId,

                    $it['lote'],

                    $it['validade']

                );

            }



            AuditoriaService::registrar(

                'TRANSFERENCIA_ENVIADA',

                'nota ' . $codigoNota,

                count($itensValidos) . ' item(ns) — ' . self::labelTipo($tipo) . ' → clínica #' . $destinoId

            );



            return $transferenciaId;

        });

    }



    private static function validarMatrizUnidade(int $origemId, int $destinoId, ?int $centralId): void

    {

        if (!$centralId || $origemId !== $centralId) {

            throw new RuntimeException('Envio matriz → unidade deve partir da Central.');

        }

        if ($destinoId === $centralId) {

            throw new RuntimeException('Destino não pode ser a Central.');

        }

    }



    private static function validarUnidadeMatriz(int $origemId, int $destinoId, ?int $centralId): void

    {

        if (!$centralId) {

            throw new RuntimeException('Unidade central não configurada.');

        }

        if ($destinoId !== $centralId) {

            throw new RuntimeException('Devolução deve ser enviada para a Central.');

        }

        if ($origemId === $centralId) {

            throw new RuntimeException('A Central não devolve para si mesma.');

        }

    }



    private static function validarEntreUnidades(int $origemId, int $destinoId, ?int $centralId): void

    {

        if ($centralId && ($origemId === $centralId || $destinoId === $centralId)) {

            throw new RuntimeException('Transferência entre unidades não envolve a Central. Use o envio matriz ou devolução.');

        }

    }



    public static function confirmarRecebimento(int $transferenciaId, int $usuarioId, int $clinicaUsuarioId, bool $ehAdmin, array $recebidas, ?string $obsRec, string $tipoRecebimento = 'ok'): void

    {

        Fase5SchemaService::garantir();



        DB::transaction(function () use ($transferenciaId, $usuarioId, $clinicaUsuarioId, $ehAdmin, $recebidas, $obsRec, $tipoRecebimento) {

            $t = DB::table('transferencias')->where('id', $transferenciaId)->where('status', 'pendente')->first();

            if (!$t) {

                throw new RuntimeException('Nota não está mais pendente.');

            }



            $podeReceber = $ehAdmin || (in_array(session('perfil'), ['farmaceutico', 'tecnico'], true) && $clinicaUsuarioId === (int) $t->clinica_destino_id);

            if (!$podeReceber) {

                throw new RuntimeException('Sem permissão para receber esta nota.');

            }



            $origemId = (int) $t->clinica_origem_id;

            $destinoId = (int) $t->clinica_destino_id;

            $tipoTransf = $t->tipo_transferencia ?? 'matriz_unidade';

            $movTipo = self::tipoParaMovimentacao($tipoTransf);

            $itens = DB::table('transferencia_itens')->where('transferencia_id', $transferenciaId)->get();



            $totalEnviado = 0;

            $totalRecebido = 0;



            foreach ($itens as $it) {

                $qtdEnviada = (int) $it->quantidade_enviada;

                $totalEnviado += $qtdEnviada;



                if ($tipoRecebimento === 'nao_recebido') {

                    $qtdRec = 0;

                } elseif ($tipoRecebimento === 'ok') {

                    $qtdRec = $qtdEnviada;

                } else {

                    $qtdRec = (int) ($recebidas[$it->id] ?? 0);

                }



                $qtdRec = max(0, min($qtdRec, $qtdEnviada));

                $totalRecebido += $qtdRec;

                $devolver = $qtdEnviada - $qtdRec;



                DB::table('transferencia_itens')->where('id', $it->id)->update([

                    'quantidade_recebida' => $qtdRec,

                    'recebido_em' => now(),

                ]);



                if ($qtdRec > 0) {

                    LoteService::inicializar();

                    LoteService::adicionarLote(

                        $destinoId,

                        (int) $it->medicamento_id,

                        $qtdRec,

                        $movTipo,

                        $it->lote,

                        $it->validade,

                        null,

                        null,

                        null,

                        'transferencia',

                        $transferenciaId,

                        $usuarioId,

                        'Recebido via transferência ' . ($t->codigo_nota ?? '')

                    );



                    DB::table('entradas_medicamentos')->insert([

                        'clinica_id' => $destinoId,

                        'medicamento_id' => $it->medicamento_id,

                        'quantidade' => $qtdRec,

                        'lote' => $it->lote,

                        'validade' => $it->validade,

                        'origem' => 'Transferência nota ' . ($t->codigo_nota ?? "#{$transferenciaId}"),

                        'observacao' => 'Recebido via transferência',

                        'usuario_id' => $usuarioId,

                        'data_entrada' => now(),

                    ]);

                }



                if ($devolver > 0) {

                    LoteService::inicializar();

                    LoteService::adicionarLote(

                        $origemId,

                        (int) $it->medicamento_id,

                        $devolver,

                        'devolucao',

                        $it->lote,

                        $it->validade,

                        null,

                        null,

                        null,

                        'transferencia_devolucao',

                        $transferenciaId,

                        $usuarioId,

                        'Devolução parcial transferência'

                    );

                }

            }



            $status = match (true) {

                $tipoRecebimento === 'nao_recebido' || $totalRecebido === 0 => 'nao_recebida',

                $totalRecebido < $totalEnviado => 'recebida_parcial',

                default => 'recebida',

            };



            $rotulo = match ($status) {

                'recebida_parcial' => '[Recebimento parcial]',

                'nao_recebida' => '[Não recebido]',

                default => '[Recebimento OK]',

            };



            DB::table('transferencias')->where('id', $transferenciaId)->update([

                'status' => $status,

                'usuario_recebimento_id' => $usuarioId,

                'recebido_em' => now(),

                'observacao_recebimento' => trim($rotulo . ' ' . ($obsRec ?? '')),

            ]);



            AuditoriaService::registrar(

                'TRANSFERENCIA_RECEBIDA',

                'nota ' . ($t->codigo_nota ?? "#{$transferenciaId}"),

                "{$rotulo} — " . self::labelTipo($tipoTransf) . " clínica destino #{$destinoId} ({$totalRecebido}/{$totalEnviado} un.)"

            );

        });

    }



    public static function cancelar(int $transferenciaId, int $usuarioId, string $motivo, string $perfil, int $clinicaUsuarioId): void

    {

        Fase5SchemaService::garantir();



        DB::transaction(function () use ($transferenciaId, $usuarioId, $motivo, $perfil, $clinicaUsuarioId) {

            $t = DB::table('transferencias')->where('id', $transferenciaId)->where('status', 'pendente')->first();

            if (!$t) {

                throw new RuntimeException('Transferência não encontrada ou já finalizada.');

            }



            $origemId = (int) $t->clinica_origem_id;

            $centralId = EstoqueService::getCentralId();



            if (!PerfilService::podeCancelarTransferencia($perfil, $clinicaUsuarioId, $centralId, $origemId)) {

                throw new RuntimeException('Sem permissão para cancelar transferências.');

            }



            $itens = DB::table('transferencia_itens')->where('transferencia_id', $transferenciaId)->get();

            foreach ($itens as $it) {

                LoteService::inicializar();

                LoteService::adicionarLote(

                    $origemId,

                    (int) $it->medicamento_id,

                    (int) $it->quantidade_enviada,

                    'devolucao',

                    $it->lote,

                    $it->validade,

                    null,

                    null,

                    null,

                    'transferencia_cancelada',

                    $transferenciaId,

                    $usuarioId,

                    'Cancelamento de transferência'

                );

            }



            DB::table('transferencias')->where('id', $transferenciaId)->update([

                'status' => 'cancelada',

                'usuario_cancelamento_id' => $usuarioId,

                'cancelado_em' => now(),

                'motivo_cancelamento' => $motivo,

            ]);



            AuditoriaService::registrar(

                'TRANSFERENCIA_CANCELADA',

                'nota ' . ($t->codigo_nota ?? "#{$transferenciaId}"),

                'Cancelada. Motivo: ' . $motivo

            );

        });

    }

}


