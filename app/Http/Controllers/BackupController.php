<?php

namespace App\Http\Controllers;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

class BackupController extends Controller
{
    public function index()
    {
        $backupDir = $this->backupDir();
        $currentPath = $backupDir;

        $files = collect(scandir($backupDir))
            ->filter(fn($f) => str_ends_with($f, '.zip'))
            ->map(function ($file) use ($backupDir) {
                $path = $backupDir . '/' . $file;
                return (object) [
                    'name'           => $file,
                    'size'           => filesize($path),
                    'size_formatted' => $this->formatSize(filesize($path)),
                    'date'           => date('Y-m-d H:i:s', filemtime($path)),
                ];
            })
            ->sortByDesc('date')
            ->values();

        return view('backup.index', compact('files', 'currentPath'));
    }

    public function create()
    {
        $backupDir = $this->backupDir();

        $timestamp = now()->format('Y-m-d_H-i-s');
        $database = DB::connection()->getDatabaseName();
        $filename  = "ERP_Backup_{$database}_{$timestamp}.zip";
        $sqlName   = "ERP_Backup_{$database}_{$timestamp}.sql";
        $zipPath   = "{$backupDir}/{$filename}";
        $tempSql = "{$backupDir}/{$sqlName}";

        try {
            file_put_contents($tempSql, $this->exportSql());

            $zip = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Cannot create zip archive.');
            }
            $zip->addFile($tempSql, $sqlName);
            $zip->close();
        } catch (\Throwable $e) {
            if (file_exists($tempSql)) {
                unlink($tempSql);
            }
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }

            return redirect()->route('backup.index')->with('error', 'Backup failed: ' . $e->getMessage());
        }

        if (file_exists($tempSql)) {
            unlink($tempSql);
        }

        return redirect()->route('backup.index')->with([
            'success' => "Backup created: {$filename}",
            'download' => $filename,
        ]);
    }

    public function download($filename)
    {
        $path = $this->backupPath($filename);
        if (!file_exists($path)) {
            return redirect()->route('backup.index')->with('error', 'File not found.');
        }
        return response()->download($path);
    }

    public function destroy($filename)
    {
        $path = $this->backupPath($filename);
        if (file_exists($path)) {
            unlink($path);
        }
        return redirect()->route('backup.index')->with('success', 'Backup deleted.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:zip,sql|max:512000',
        ]);

        $file = $request->file('backup_file');

        if ($file->getClientOriginalExtension() === 'zip') {
            $zip = new ZipArchive;
            if ($zip->open($file->getRealPath()) !== true) {
                return redirect()->route('backup.index')->with('error', 'Cannot open zip file.');
            }
            $sqlContent = null;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (str_ends_with($name, '.sql')) {
                    $sqlContent = $zip->getFromIndex($i);
                    break;
                }
            }
            $zip->close();
            if (!$sqlContent) {
                return redirect()->route('backup.index')->with('error', 'No SQL file found in the zip archive.');
            }
        } else {
            $sqlContent = file_get_contents($file->getRealPath());
        }

        if (empty(trim($sqlContent))) {
            return redirect()->route('backup.index')->with('error', 'SQL file is empty.');
        }

        try {
            $this->importSql($sqlContent);
        } catch (\Throwable $e) {
            return redirect()->route('backup.index')->with('error', 'Import failed: ' . $e->getMessage());
        }

        return redirect()->route('backup.index')->with('success', 'Database imported successfully.');
    }

    private function exportSql(): string
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            throw new RuntimeException("Only MySQL/MariaDB backups are supported. Current driver: {$driver}.");
        }

        $cliDump = $this->runMysqlDump();
        if ($cliDump !== null) {
            return $cliDump;
        }

        return $this->exportSqlWithPdo($connection);
    }

    private function exportSqlWithPdo(ConnectionInterface $connection): string
    {
        $pdo = $connection->getPdo();
        $database = $connection->getDatabaseName();
        $date = now()->format('Y-m-d H:i:s');
        $lines = [
            '-- ERP database backup',
            "-- Database: {$database}",
            "-- Created at: {$date}",
            'SET FOREIGN_KEY_CHECKS=0;',
            'SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";',
            'SET NAMES utf8mb4;',
            '',
        ];

        $tables = $connection->select('SHOW FULL TABLES WHERE Table_type = ?', ['BASE TABLE']);
        $tableColumn = "Tables_in_{$database}";

        foreach ($tables as $tableRow) {
            $table = $tableRow->{$tableColumn};
            $quotedTable = $this->quoteIdentifier($table);
            $createRow = (array) $connection->selectOne("SHOW CREATE TABLE {$quotedTable}");
            $createSql = $createRow['Create Table'] ?? array_values($createRow)[1] ?? null;

            if (!$createSql) {
                throw new RuntimeException("Could not read CREATE TABLE statement for {$table}.");
            }

            $lines[] = "-- Table structure for {$table}";
            $lines[] = "DROP TABLE IF EXISTS {$quotedTable};";
            $lines[] = "{$createSql};";
            $lines[] = '';

            $columns = collect($connection->select("SHOW COLUMNS FROM {$quotedTable}"))
                ->pluck('Field')
                ->all();
            $quotedColumns = implode(', ', array_map(fn ($column) => $this->quoteIdentifier($column), $columns));

            $connection->table($table)->orderBy($columns[0])->chunk(500, function ($rows) use (&$lines, $pdo, $table, $quotedTable, $quotedColumns, $columns) {
                if ($rows->isEmpty()) {
                    return;
                }

                $values = [];
                foreach ($rows as $row) {
                    $row = (array) $row;
                    $values[] = '(' . implode(', ', array_map(
                        fn ($column) => $this->sqlValue($pdo, $row[$column] ?? null),
                        $columns
                    )) . ')';
                }

                $lines[] = "-- Data for {$table}";
                $lines[] = "INSERT INTO {$quotedTable} ({$quotedColumns}) VALUES";
                $lines[] = implode(",\n", $values) . ';';
                $lines[] = '';
            });
        }

        $views = $connection->select('SHOW FULL TABLES WHERE Table_type = ?', ['VIEW']);
        foreach ($views as $viewRow) {
            $view = $viewRow->{$tableColumn};
            $quotedView = $this->quoteIdentifier($view);
            $createRow = (array) $connection->selectOne("SHOW CREATE VIEW {$quotedView}");
            $createSql = $createRow['Create View'] ?? array_values($createRow)[1] ?? null;

            if ($createSql) {
                $lines[] = "-- View structure for {$view}";
                $lines[] = "DROP VIEW IF EXISTS {$quotedView};";
                $lines[] = "{$createSql};";
                $lines[] = '';
            }
        }

        $lines[] = 'SET FOREIGN_KEY_CHECKS=1;';
        $lines[] = '';

        return implode("\n", $lines);
    }

    private function importSql(string $sqlContent): void
    {
        $cliImported = $this->runMysqlImport($sqlContent);
        if ($cliImported) {
            return;
        }

        $connection = DB::connection();
        $connection->unprepared('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($this->splitSqlStatements($sqlContent) as $statement) {
                $trimmed = trim($statement);
                if ($trimmed !== '') {
                    $connection->unprepared($trimmed);
                }
            }
        } finally {
            $connection->unprepared('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    private function runMysqlDump(): ?string
    {
        $db = $this->mysqlConnectionConfig();
        $binary = $this->findExecutable('mysqldump');

        if (!$binary) {
            return null;
        }

        $process = new Process(array_filter([
            $binary,
            "--user={$db['username']}",
            $db['password'] !== '' ? "--password={$db['password']}" : null,
            "--host={$db['host']}",
            "--port={$db['port']}",
            '--routines',
            '--events',
            '--triggers',
            '--add-drop-table',
            '--single-transaction',
            '--skip-comments',
            $db['database'],
        ]));
        $process->setTimeout(300);
        $process->run();

        return $process->isSuccessful() ? $process->getOutput() : null;
    }

    private function runMysqlImport(string $sqlContent): bool
    {
        $db = $this->mysqlConnectionConfig();
        $binary = $this->findExecutable('mysql');

        if (!$binary) {
            return false;
        }

        $process = new Process(array_filter([
            $binary,
            "--user={$db['username']}",
            $db['password'] !== '' ? "--password={$db['password']}" : null,
            "--host={$db['host']}",
            "--port={$db['port']}",
            $db['database'],
        ]));
        $process->setTimeout(600);
        $process->setInput($sqlContent);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput() ?: 'Unknown mysql import error.');
        }

        return true;
    }

    private function mysqlConnectionConfig(): array
    {
        $connectionName = config('database.default');
        $db = config("database.connections.{$connectionName}");

        if (!in_array($db['driver'] ?? null, ['mysql', 'mariadb'], true)) {
            throw new RuntimeException('Backup requires a MySQL or MariaDB database connection.');
        }

        return [
            'host' => $db['host'] ?? '127.0.0.1',
            'port' => $db['port'] ?? '3306',
            'database' => $db['database'] ?? '',
            'username' => $db['username'] ?? '',
            'password' => $db['password'] ?? '',
        ];
    }

    private function findExecutable(string $name): ?string
    {
        $command = PHP_OS_FAMILY === 'Windows' ? 'where' : 'command -v';
        $process = Process::fromShellCommandline("{$command} {$name}");
        $process->run();

        if ($process->isSuccessful()) {
            $path = trim(explode(PHP_EOL, $process->getOutput())[0]);
            return $path !== '' ? $path : null;
        }

        foreach ($this->commonMysqlPaths($name) as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function commonMysqlPaths(string $name): array
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return [];
        }

        return [
            "C:\\xampp\\mysql\\bin\\{$name}.exe",
            "C:\\wamp64\\bin\\mysql\\mysql8.0.31\\bin\\{$name}.exe",
            "C:\\laragon\\bin\\mysql\\mysql-8.0\\bin\\{$name}.exe",
            "C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\{$name}.exe",
        ];
    }

    private function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $statement = '';
        $length = strlen($sql);
        $quote = null;
        $lineComment = false;
        $blockComment = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $next = $sql[$i + 1] ?? '';

            if ($lineComment) {
                if ($char === "\n") {
                    $lineComment = false;
                }
                continue;
            }

            if ($blockComment) {
                if ($char === '*' && $next === '/') {
                    $blockComment = false;
                    $i++;
                }
                continue;
            }

            if ($quote === null && $char === '-' && $next === '-' && preg_match('/\s/', $sql[$i + 2] ?? '')) {
                $lineComment = true;
                $i++;
                continue;
            }

            if ($quote === null && $char === '#') {
                $lineComment = true;
                continue;
            }

            if ($quote === null && $char === '/' && $next === '*') {
                $blockComment = true;
                $i++;
                continue;
            }

            $statement .= $char;

            if (($char === "'" || $char === '"' || $char === '`') && ($i === 0 || $sql[$i - 1] !== '\\')) {
                $quote = $quote === $char ? null : ($quote ?? $char);
                continue;
            }

            if ($char === ';' && $quote === null) {
                $statements[] = substr($statement, 0, -1);
                $statement = '';
            }
        }

        if (trim($statement) !== '') {
            $statements[] = $statement;
        }

        return $statements;
    }

    private function sqlValue(\PDO $pdo, mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return $pdo->quote((string) $value);
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    public static function autoBackup(): bool
    {
        try {
            $ctrl = new static;
            $backupDir = $ctrl->backupDir();
            $database = DB::connection()->getDatabaseName();
            $todayPrefix = "ERP_Backup_{$database}_" . now()->format('Y-m-d');

            foreach (scandir($backupDir) as $file) {
                if (str_starts_with($file, $todayPrefix) && str_ends_with($file, '.zip')) {
                    return false;
                }
            }

            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename  = "ERP_Backup_{$database}_{$timestamp}.zip";
            $sqlName   = "ERP_Backup_{$database}_{$timestamp}.sql";
            $zipPath   = "{$backupDir}/{$filename}";
            $tempSql   = "{$backupDir}/{$sqlName}";

            file_put_contents($tempSql, $ctrl->exportSql());

            $zip = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                $zip->addFile($tempSql, $sqlName);
                $zip->close();
            }

            if (file_exists($tempSql)) {
                unlink($tempSql);
            }

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function updatePath(Request $request)
    {
        $request->validate([
            'backup_path' => 'required|string',
        ]);

        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $request->input('backup_path'));

        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
        }

        if (!is_dir($path)) {
            return redirect()->route('backup.index')->with('error', 'Directory does not exist and could not be created.');
        }

        if (!is_writable($path)) {
            return redirect()->route('backup.index')->with('error', 'Directory is not writable.');
        }

        \App\Models\Setting::updateOrCreate(
            ['key' => 'backup_path'],
            ['value' => $path]
        );

        return redirect()->route('backup.index')->with('success', 'Backup path updated.');
    }

    private function backupDir(): string
    {
        $path = \App\Models\Setting::getValue('backup_path', storage_path('app\\backup'));
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return $path;
    }

    private function backupPath(string $filename): string
    {
        $filename = basename($filename);
        if (!str_ends_with($filename, '.zip')) {
            abort(404);
        }

        return $this->backupDir() . DIRECTORY_SEPARATOR . $filename;
    }

    private function formatSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
