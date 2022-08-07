<?php

declare(strict_types=1);

use mikehaertl\shellcommand\Command;

require_once 'vendor/autoload.php';

require_once __DIR__.\DIRECTORY_SEPARATOR.'config.php';

$files = scandir($inputPath);
natcasesort($files);


foreach ($files as $originalFile) {
    if (\mb_strlen($originalFile) < 3) {
        continue;
    }

    $originalFilePath = $inputPath.\DIRECTORY_SEPARATOR.$originalFile;
    $originalTime = filemtime($originalFilePath);

    $command = new Command('docker run --rm -v "'.$inputPath.':/inputVolume" -v "'.$outputPath.':/outputVolume" ffmpeg');

    $command->addArg('-i', null, false);
    $command->addArg('/inputVolume/'.$originalFile, null, true);

    // $command->addArg('-x265-params', 'pools=4', false);
    $command->addArg('-preset', 'faster', false);
    $command->addArg('-c:v', 'libx265', false);
    $command->addArg('-crf', '22', false);
    $command->addArg('-c:a', 'copy', false);

    $newName = str_replace('.mp4', '.tempFile.h265.mkv', $originalFile);
    $newFilePath = $outputPath.\DIRECTORY_SEPARATOR.$newName;

    $command->addArg('/outputVolume/'.$newName);

    // var_dump($command->getExecCommand());

    $r = shell_exec($command->getExecCommand());

    sleep(1);
    $newFilePathFinal = str_replace('.tempFile', '', $newFilePath);
    rename($newFilePath, $newFilePathFinal);
    touch($newFilePathFinal, $originalTime);

    if (file_exists($newFilePathFinal) && filesize($newFilePathFinal) > 1000000) {
        var_dump('Unlink file '.$originalFilePath);
        unlink($originalFilePath);
    }
}


// foreach (scandir($outputPath) as $originalFile) {
//     $fileN = str_replace('.h265.mkv', '.mp4', $originalFile);

//     $fileToDelete = $inputPath .DIRECTORY_SEPARATOR.$fileN;
//     var_dump($fileToDelete);

//     unlink($fileToDelete);
// }
