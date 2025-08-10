<?php

namespace Core\Console\Commands;

use Core\Application;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class Command extends SymfonyCommand
{
    /**
     * @var Application
     */
    protected Application $app;

    /**
     * @var OutputInterface
     */
    protected OutputInterface $output;

    /**
     * @var InputInterface
     */
    protected InputInterface $input;

    /**
     * @var SymfonyStyle
     */
    protected SymfonyStyle $io;

    /**
     * Constructor
     */
    public function __construct(Application $app)
    {
        parent::__construct();
        $this->app = $app;
    }

    /**
     * Execute the command
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->input = $input;
        $this->io = new SymfonyStyle($input, $output);
        
        try {
            return $this->handle($input, $output);
        } catch (\Exception $e) {
            $this->error('Command failed: ' . $e->getMessage());
            if ($this->app->isDebug()) {
                $this->line($e->getTraceAsString());
            }
            return 1;
        }
    }

    /**
     * Handle the command
     */
    abstract protected function handle(InputInterface $input, OutputInterface $output): int;

    /**
     * Get the application instance
     */
    protected function getApp(): Application
    {
        return $this->app;
    }

    /**
     * Write a string as standard output
     */
    protected function line(string $string, string $style = null): void
    {
        $styled = $style ? "<{$style}>{$string}</{$style}>" : $string;
        $this->output->writeln($styled);
    }

    /**
     * Write a string as information output
     */
    protected function info(string $string): void
    {
        $this->io->info($string);
    }

    /**
     * Write a string as comment output
     */
    protected function comment(string $string): void
    {
        $this->io->text("<comment>{$string}</comment>");
    }

    /**
     * Write a string as question output
     */
    protected function question(string $string): void
    {
        $this->line($string, 'question');
    }

    /**
     * Write a string as error output
     */
    protected function error(string $string): void
    {
        $this->io->error($string);
    }

    /**
     * Write a string as warning output
     */
    protected function warn(string $string): void
    {
        $this->io->warning($string);
    }

    /**
     * Write a string as success output
     */
    protected function success(string $string): void
    {
        $this->io->success($string);
    }

    /**
     * Create a new line
     */
    protected function newLine(int $count = 1): void
    {
        $this->io->newLine($count);
    }

    /**
     * Create a progress bar
     */
    protected function createProgressBar(int $max = 0): ProgressBar
    {
        return $this->io->createProgressBar($max);
    }

    /**
     * Create a table
     */
    protected function table(array $headers, array $rows): void
    {
        $this->io->table($headers, $rows);
    }

    /**
     * Ask a question
     */
    protected function ask(string $question, string $default = null): ?string
    {
        return $this->io->ask($question, $default);
    }

    /**
     * Ask a hidden question (like passwords)
     */
    protected function secret(string $question): ?string
    {
        return $this->io->askHidden($question);
    }

    /**
     * Ask for confirmation
     */
    protected function confirm(string $question, bool $default = false): bool
    {
        return $this->io->confirm($question, $default);
    }

    /**
     * Ask to choose from options
     */
    protected function choice(string $question, array $choices, string $default = null): string
    {
        return $this->io->choice($question, $choices, $default);
    }

    /**
     * Display title
     */
    protected function title(string $message): void
    {
        $this->io->title($message);
    }

    /**
     * Display section
     */
    protected function section(string $message): void
    {
        $this->io->section($message);
    }

    /**
     * Display listing
     */
    protected function listing(array $elements): void
    {
        $this->io->listing($elements);
    }

    /**
     * Display text
     */
    protected function text(string $message): void
    {
        $this->io->text($message);
    }

    /**
     * Display note
     */
    protected function note(string $message): void
    {
        $this->io->note($message);
    }

    /**
     * Display caution
     */
    protected function caution(string $message): void
    {
        $this->io->caution($message);
    }

    /**
     * Get the output interface
     */
    protected function getOutput(): OutputInterface
    {
        return $this->output;
    }

    /**
     * Get the input interface
     */
    protected function getInput(): InputInterface
    {
        return $this->input;
    }

    /**
     * Get the IO interface
     */
    protected function getIO(): SymfonyStyle
    {
        return $this->io;
    }

    /**
     * Check if a file exists
     */
    protected function fileExists(string $path): bool
    {
        return file_exists($this->app->basePath($path));
    }

    /**
     * Ensure directory exists
     */
    protected function ensureDirectoryExists(string $path): bool
    {
        $fullPath = $this->app->basePath($path);
        if (!is_dir($fullPath)) {
            return mkdir($fullPath, 0755, true);
        }
        return true;
    }

    /**
     * Write file content
     */
    protected function writeFile(string $path, string $content): bool
    {
        $fullPath = $this->app->basePath($path);
        $directory = dirname($fullPath);
        
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        return file_put_contents($fullPath, $content) !== false;
    }

    /**
     * Read file content
     */
    protected function readFile(string $path): string
    {
        $fullPath = $this->app->basePath($path);
        if (!file_exists($fullPath)) {
            throw new \Exception("File not found: {$path}");
        }
        return file_get_contents($fullPath);
    }

    /**
     * Execute system command
     */
    protected function exec(string $command): array
    {
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        return [$output, $returnCode];
    }

    /**
     * Get timestamp for files
     */
    protected function getTimestamp(): string
    {
        return date('Y_m_d_His');
    }

    /**
     * Convert string to StudlyCase
     */
    protected function studlyCase(string $value): string
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));
        return str_replace(' ', '', $value);
    }

    /**
     * Convert string to snake_case
     */
    protected function snakeCase(string $value, string $delimiter = '_'): string
    {
        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));
            $value = strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value));
        }
        return $value;
    }

    /**
     * Convert string to kebab-case
     */
    protected function kebabCase(string $value): string
    {
        return $this->snakeCase($value, '-');
    }

    /**
     * Get plural form of a word
     */
    protected function plural(string $value): string
    {
        // Simple pluralization
        if (substr($value, -1) === 'y') {
            return substr($value, 0, -1) . 'ies';
        } elseif (in_array(substr($value, -1), ['s', 'x', 'z']) || 
                  in_array(substr($value, -2), ['ch', 'sh'])) {
            return $value . 'es';
        } else {
            return $value . 's';
        }
    }

    /**
     * Get singular form of a word
     */
    protected function singular(string $value): string
    {
        // Simple singularization
        if (substr($value, -3) === 'ies') {
            return substr($value, 0, -3) . 'y';
        } elseif (substr($value, -2) === 'es') {
            return substr($value, 0, -2);
        } elseif (substr($value, -1) === 's' && strlen($value) > 1) {
            return substr($value, 0, -1);
        }
        return $value;
    }
}
