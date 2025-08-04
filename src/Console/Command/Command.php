<?php

namespace Kim1ne\B24\Console\Command;

use Symfony\Component\Console\Command\Command as SymfonyCommand;

abstract class Command
{
    const DEFAULT_NAMESPACE = '\Kim1ne\B24\Console\Command\\';

    /**
     * @param string|null $dir
     * @param string $namespace
     * @return SymfonyCommand[]
     */
    public static function all(?string $dir = null, string $namespace = self::DEFAULT_NAMESPACE): array
    {
        $dir ??= __DIR__;

        $files = glob($dir . '/*');

        $commands = [];
        foreach ($files as $file) {
            $basename = basename($file);

            if ($basename === 'Command.php') {
                continue;
            }

            if (is_dir($file)) {
                $commands = array_merge($commands, self::all($file, $namespace . $basename . '\\'));
                continue;
            }

            if (self::isPhpFile($basename) === false) {
                continue;
            }

            $shortClassName = pathinfo($basename, PATHINFO_FILENAME);
            $class = $namespace . $shortClassName;

            if (!is_subclass_of($class, SymfonyCommand::class)) {
                continue;
            }

            $commands[] = new $class();
        }

        return $commands;
    }

    private static function isPhpFile(string $file): bool
    {
        return str_ends_with($file, '.php');
    }
}
