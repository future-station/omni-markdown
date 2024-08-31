<?php

namespace FutureStation\OmniMarkdown;

use Closure;
use FutureStation\OmniMarkdown\Exceptions\BinaryNotFoundException;
use FutureStation\OmniMarkdown\Exceptions\CouldNotExtractMarkdown;
use FutureStation\OmniMarkdown\Exceptions\FileNotFound;
use Symfony\Component\Process\Process;

class Markdown
{
    protected string $file;

    protected string $binPath;

    protected array $options = [];

    protected int $timeout = 60;

    /**
     * Markdown constructor.
     *
     * @throws BinaryNotFoundException
     */
    public function __construct(
        ?string $binPath = null,
        ?array $options = ['-t', 'gfm', '--wrap=none']
    ) {
        $this->binPath = $binPath ?? $this->findPandoc();
        $this->options = $this->parseOptions($options);

        if (! is_executable($this->binPath)) {
            throw BinaryNotFoundException::fromPath($this->binPath);
        }
    }

    /**
     * Attempt to find the pandoc binary in common locations.
     *
     * @throws BinaryNotFoundException
     */
    protected function findPandoc(): string
    {
        $commonPaths = [
            '/usr/local/bin/pandoc',    // Common on Linux
            '/opt/homebrew/bin/pandoc', // Homebrew on macOS (Apple Silicon)
            '/usr/bin/pandoc',          // Common on Linux
            '/opt/local/bin/pandoc',    // MacPorts on macOS
            '/usr/local/bin/pandoc',    // Homebrew on macOS (Intel)
        ];

        foreach ($commonPaths as $path) {
            if (is_executable($path)) {
                return $path;
            }
        }

        throw BinaryNotFoundException::fromPath($this->binPath);
    }

    /**
     * Static method to get Markdown from a file.
     *
     *
     * @throws BinaryNotFoundException
     * @throws CouldNotExtractMarkdown
     * @throws FileNotFound
     */
    public static function getMarkdown(
        string $file,
        ?string $format = 'gfm',
        bool $nowrap = true,
        ?string $binPath = null,
        int $timeout = 60,
        ?Closure $callback = null
    ): string {
        $validFormats = ['gfm', 'markdown'];
        if (! in_array($format, $validFormats)) {
            throw new \InvalidArgumentException("Invalid format specified. Use 'gfm' or 'markdown'.");
        }

        $options = ['-t', $format];

        if ($nowrap) {
            $options[] = '--wrap=none';
        }

        return (new static($binPath))
            ->setOptions($options)
            ->setFile($file)
            ->setTimeout($timeout)
            ->markdown($callback);
    }

    /**
     * Get the Markdown from the file.
     *
     * @throws CouldNotExtractMarkdown
     */
    public function markdown(?Closure $callback = null): string
    {
        $command = [$this->binPath, $this->file, ...$this->options];

        $process = new Process($command);
        $process->setTimeout($this->timeout);

        // Allow customization of the process instance via callback if provided
        $process = $callback instanceof Closure ? $callback($process) : $process;

        $process->run();

        if (! $process->isSuccessful()) {
            throw new CouldNotExtractMarkdown($process);
        }

        return trim($process->getOutput());
    }

    /**
     * Set the path to the binary.
     *
     * @return $this
     */
    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Set the file to be converted.
     *
     * @return $this
     *
     * @throws FileNotFound
     */
    public function setFile(string $file): self
    {
        if (! is_readable($file)) {
            throw new FileNotFound("Could not read `{$file}`");
        }

        $this->file = $file;

        return $this;
    }

    /**
     * Set the options for the conversion process.
     *
     * @return $this
     */
    public function setOptions(array $options): self
    {
        $this->options = $this->parseOptions($options);

        return $this;
    }

    /**
     * Add additional options to the conversion process.
     *
     * @return $this
     */
    public function addOptions(array $options): self
    {
        $this->options = array_merge(
            $this->options,
            $this->parseOptions($options)
        );

        return $this;
    }

    /**
     * Parse options array into a format suitable for the command line.
     */
    protected function parseOptions(array $options): array
    {
        return array_map(fn ($option): string => trim($option), $options);
    }
}
