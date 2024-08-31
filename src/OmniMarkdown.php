<?php

namespace FutureStation\OmniMarkdown;

use Closure;
use FutureStation\OmniMarkdown\Exceptions\BinaryNotFoundException;
use FutureStation\OmniMarkdown\Exceptions\CouldNotExtractMarkdown;
use FutureStation\OmniMarkdown\Exceptions\FileNotFound;
use Symfony\Component\Process\Process;

class OmniMarkdown
{
    protected string $file;

    protected string $binPath;

    protected array $options;

    protected int $timeout = 60;

    /**
     * OmniMarkdown constructor.
     *
     * @throws BinaryNotFoundException
     */
    public function __construct(
        ?string $binPath = null,
    ) {
        $this->binPath = $binPath ?? '/usr/bin/pandoc';

        if (! is_executable($this->binPath)) {
            throw BinaryNotFoundException::fromPath($this->binPath);
        }
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
        ?string $binPath = null,
        array $options = [],
        int $timeout = 60,
        ?Closure $callback = null
    ): string {
        return (new static($binPath, $timeout))
            ->setOptions($options)
            ->setFile($file)
            ->markdown($callback);
    }

    /**
     * Get the Markdown from the file.
     *
     * @throws CouldNotExtractMarkdown
     */
    public function markdown(?Closure $callback = null): string
    {
        $command = array_merge([$this->binPath, $this->file, ...$this->options]);
        $process = new Process($command);
        $process->setTimeout($this->timeout);

        // Allow customization of the process instance via callback if provided
        $process = $callback ? $callback($process) : $process;

        $process->run();

        if (! $process->isSuccessful()) {
            throw new CouldNotExtractMarkdown($process->getErrorOutput());
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
        return array_map(function ($option) {
            return trim($option);
        }, $options);
    }
}
