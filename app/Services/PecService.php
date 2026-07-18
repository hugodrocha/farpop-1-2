<?php

namespace App\Services;

class PecService
{
    /**
     * Verifica se a integração com o PEC está disponível neste ambiente.
     *
     * @return array{ok: bool, motivo: string}
     */
    public static function status(): array
    {
        $config = config('farpop.pec', []);

        if (empty($config['habilitado'])) {
            return ['ok' => false, 'motivo' => 'Integração desabilitada em config/farpop.php.'];
        }

        if (!function_exists('pg_connect')) {
            return ['ok' => false, 'motivo' => 'Extensão PostgreSQL (pgsql) do PHP não está habilitada.'];
        }

        $host = (string) ($config['host'] ?? '');
        $database = (string) ($config['database'] ?? '');

        if ($host === '' || $database === '') {
            return ['ok' => false, 'motivo' => 'Configuração PEC ausente ou incompleta.'];
        }

        return ['ok' => true, 'motivo' => 'OK'];
    }

    /**
     * Busca um cidadão no PEC pelo CPF (11 dígitos).
     *
     * @return array{nome: string, data_nascimento: string, telefone: string, cartao_sus: string, endereco: string, fonte: string}|null
     */
    public static function buscarCidadaoPorCpf(string $cpf): ?array
    {
        $cpf = preg_replace('/\D/', '', $cpf);
        if (strlen($cpf) !== 11) {
            return null;
        }

        $conn = self::abrirConexao();
        if (!$conn) {
            return null;
        }

        $config = config('farpop.pec', []);
        $schema = self::schemaSeguro((string) ($config['schema'] ?? 'public'));

        $sql = 'SELECT * FROM "' . $schema . '".tb_cidadao
            WHERE LPAD(regexp_replace(COALESCE(nu_cpf::text, \'\'), \'[^0-9]\', \'\', \'g\'), 11, \'0\') = $1
            LIMIT 1';

        $res = @pg_query_params($conn, $sql, [$cpf]);
        if (!$res) {
            @pg_close($conn);

            return null;
        }

        $row = pg_fetch_assoc($res);
        @pg_close($conn);

        if (!$row) {
            return null;
        }

        return self::mapearCidadao($row);
    }

    /**
     * @return resource|null
     */
    private static function abrirConexao()
    {
        $status = self::status();
        if (!$status['ok']) {
            return null;
        }

        $config = config('farpop.pec', []);
        $timeout = (int) ($config['timeout'] ?? 3);
        $password = str_replace("'", "''", (string) ($config['password'] ?? ''));

        $connStr = 'host=' . ($config['host'] ?? '127.0.0.1') .
            ' port=' . ($config['port'] ?? 5433) .
            ' dbname=' . ($config['database'] ?? '') .
            ' user=' . ($config['username'] ?? 'postgres') .
            " password='" . $password . "'" .
            ' connect_timeout=' . $timeout;

        $conn = @pg_connect($connStr);

        return $conn ?: null;
    }

    private static function schemaSeguro(string $schema): string
    {
        if ($schema === '' || !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $schema)) {
            return 'public';
        }

        return $schema;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{nome: string, data_nascimento: string, telefone: string, cartao_sus: string, endereco: string, fonte: string}
     */
    private static function mapearCidadao(array $row): array
    {
        $nome = (string) ($row['no_cidadao'] ?? $row['nome'] ?? '');
        $cns = (string) ($row['nu_cns'] ?? $row['cartao_sus'] ?? '');
        $nasc = (string) ($row['dt_nascimento'] ?? '');

        $telCel = (string) ($row['nu_telefone_celular'] ?? $row['nu_celular'] ?? '');
        $telRes = (string) ($row['nu_telefone_residencial'] ?? $row['nu_telefone'] ?? '');
        $telCon = (string) ($row['nu_telefone_contato'] ?? '');

        $telefone = '';
        foreach ([$telCel, $telCon, $telRes] as $t) {
            if (trim($t) !== '') {
                $telefone = $t;
                break;
            }
        }

        $logradouro = (string) ($row['no_logradouro'] ?? $row['ds_logradouro'] ?? '');
        $numero = (string) ($row['nu_numero'] ?? $row['ds_numero'] ?? '');
        $bairro = (string) ($row['no_bairro'] ?? $row['ds_bairro'] ?? '');
        $complemento = (string) ($row['ds_complemento'] ?? '');
        $municipio = (string) ($row['no_municipio'] ?? $row['ds_municipio'] ?? '');
        $uf = (string) ($row['sg_uf'] ?? '');
        $cep = (string) ($row['nu_cep'] ?? $row['co_cep'] ?? '');

        $partes = [];
        if ($logradouro !== '') {
            $partes[] = trim($logradouro . ($numero !== '' ? ', ' . $numero : ''));
        }
        if ($complemento !== '') {
            $partes[] = $complemento;
        }
        if ($bairro !== '') {
            $partes[] = 'Bairro ' . $bairro;
        }
        $cidadeUf = trim($municipio . ($uf !== '' ? '/' . $uf : ''));
        if ($cidadeUf !== '') {
            $partes[] = $cidadeUf;
        }
        if ($cep !== '') {
            $partes[] = 'CEP ' . $cep;
        }

        return [
            'nome' => $nome,
            'data_nascimento' => $nasc,
            'telefone' => $telefone,
            'cartao_sus' => $cns,
            'endereco' => implode(', ', $partes),
            'fonte' => 'pec',
        ];
    }
}
