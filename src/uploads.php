<?php
declare(strict_types=1);

function ensureUploadDirectory(string $absolutePath): void
{
    if (! is_dir($absolutePath) && ! mkdir($absolutePath, 0777, true) && ! is_dir($absolutePath)) {
        throw new RuntimeException('Unable to create upload directory.');
    }
}

function storeQuestionImage(array $file, string $absoluteUploadDir): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed.');
    }

    $originalName = (string) ($file['name'] ?? 'question-image');
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (! in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true)) {
        throw new InvalidArgumentException('Unsupported image type.');
    }

    ensureUploadDirectory($absoluteUploadDir);

    $targetName = 'question_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $targetPath = rtrim($absoluteUploadDir, '/') . '/' . $targetName;
    $tmpName = (string) ($file['tmp_name'] ?? '');

    $stored = is_uploaded_file($tmpName)
        ? move_uploaded_file($tmpName, $targetPath)
        : @copy($tmpName, $targetPath);

    if (! $stored) {
        throw new RuntimeException('Unable to store uploaded image.');
    }

    return '/uploads/questions/' . $targetName;
}
