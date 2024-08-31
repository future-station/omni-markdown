<?php

namespace FutureStation\OmniMarkdown\Exceptions;

use Exception;

class BinaryNotFoundException extends Exception
{
    /**
     * Creates a new BinaryNotFoundException with a custom message.
     *
     * @param  string  $path  The path where the binary was expected to be found.
     * @return self Returns an instance of BinaryNotFoundException with a message indicating the binary was not found or is not executable.
     */
    public static function fromPath(string $path): self
    {
        return new self("The required binary was not found or is not executable at `{$path}`.");
    }
}
