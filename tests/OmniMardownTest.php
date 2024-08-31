<?php

use FutureStation\OmniMarkdown\Exceptions\BinaryNotFoundException;
use FutureStation\OmniMarkdown\Exceptions\FileNotFound;
use FutureStation\OmniMarkdown\Markdown;

beforeEach(function () {
    // $this->dummyPdfFile = __DIR__.'/testfiles/dummy.pdf';
    // $this->dummyPdfFileText = 'This is a dummy PDF';

    $this->dummyTxtFile = __DIR__.'/testfiles/dummy.txt';
    $this->dummyTxtFileText = 'This is a dummy text file';
});

// it('can extract markdown from a pdf', function () {
//     $markdown = (new Markdown())
//         ->setFile($this->dummyPdfFile)
//         ->markdown();

//     expect($markdown)->toBe($this->dummyPdfFileText);
// })->todo();

it('can extract markdown from a txt file', function () {
    $markdown = (new Markdown())
        ->setFile($this->dummyTxtFile)
        ->markdown();

    expect($markdown)->toBe($this->dummyTxtFileText);
});

it('provides a static method to extract markdown', function () {
    expect(Markdown::getMarkdown($this->dummyTxtFile))
        ->toBe($this->dummyTxtFileText);
});

it('will throw an exception when the File is not found', function () {
    (new Markdown())
        ->setFile('/no/pdf/here/dummy.txt')
        ->markdown();
})->throws(FileNotFound::class);

it('will throw an exception when the binary is not found', function () {
    (new Markdown('/there/is/no/place/like/home/pdftotext'))
        ->setFile($this->dummyTxtFile)
        ->markdown();
})->throws(BinaryNotFoundException::class);
