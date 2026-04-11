<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../src/uploads.php';

$tempDir = makeTempDir('uploads');
$sourceImage = $tempDir . '/sample.png';
file_put_contents($sourceImage, 'fake-image-data');

$storedPath = storeQuestionImage([
    'tmp_name' => $sourceImage,
    'name' => 'diagram.png',
    'error' => UPLOAD_ERR_OK,
], $tempDir . '/uploads/questions');

assertTrueValue(str_starts_with($storedPath, '/uploads/questions/'), 'Stored image path should use the public uploads prefix.');
assertTrueValue(file_exists($tempDir . $storedPath), 'The stored image should exist in the target upload directory.');

echo "OK uploads_test\n";
