<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'farpop:backup-banco {--keep=14 : Dias para manter backups antigos}';

    protected $description = 'Gera backup SQL do banco FARPOP (MySQL)';

    public function handle(): int
    {
        $connection = Config::get('database.default');
        if ($connection !== 'mysql') {
            $this->error('Backup automático disponível apenas para MySQL.');

            return self::FAILURE;
        }

        $host = Config::get('database.connections.mysql.host');
        $port = Config::get('database.connections.mysql.port');
        $database = Config::get('database.connections.mysql.database');
        $username = Config::get('database.connections.mysql.username');
        $password = Config::get('database.connections.mysql.password');

        $dir = storage_path('app/backups/farpop');
        File::ensureDirectoryExists($dir);

        $filename = 'farpop_' . $database . '_' . now()->format('Y-m-d_His') . '.sql';
        $path = $dir . DIRECTORY_SEPARATOR . $filename;

        $mysqldump = $this->resolverMysqldump();
        if (!$mysqldump) {
            $this->error('mysqldump não encontrado. Instale o MySQL client ou configure FARPOP_MYSQLDUMP_PATH no .env');

            return self::FAILURE;
        }

        $passArg = $password !== '' && $password !== null
            ? '--password=' . escapeshellarg($password)
            : '';

        $cmd = sprintf(
            '%s --host=%s --port=%s --user=%s %s --routines --triggers --single-transaction %s > %s',
            escapeshellarg($mysqldump),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            $passArg,
            escapeshellarg($database),
            escapeshellarg($path)
        );

        $this->info('Gerando backup...');
        $code = 0;
        system($cmd, $code);

        if ($code !== 0 || !File::exists($path) || File::size($path) < 100) {
            $this->error('Falha ao gerar backup.');

            return self::FAILURE;
        }

        $this->info('Backup salvo: ' . $path);
        $this->limparAntigos($dir, (int) $this->option('keep'));

        return self::SUCCESS;
    }

    private function resolverMysqldump(): ?string
    {
        $custom = env('FARPOP_MYSQLDUMP_PATH');
        if ($custom && file_exists($custom)) {
            return $custom;
        }

        foreach (['mysqldump', 'C:\\xampp\\mysql\\bin\\mysqldump.exe', 'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe'] as $c) {
            $found = trim((string) shell_exec(escapeshellarg(PHP_OS_FAMILY === 'Windows' ? 'where' : 'which') . ' ' . escapeshellarg($c) . ' 2>nul'));
            if ($found !== '') {
                return strtok($found, "\r\n");
            }
            if (file_exists($c)) {
                return $c;
            }
        }

        return null;
    }

    private function limparAntigos(string $dir, int $dias): void
    {
        $limite = now()->subDays(max(1, $dias))->timestamp;
        foreach (File::glob($dir . DIRECTORY_SEPARATOR . 'farpop_*.sql') as $file) {
            if (filemtime($file) < $limite) {
                File::delete($file);
            }
        }
    }
}
