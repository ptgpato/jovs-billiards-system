<?php
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

function upload_product_image(array $file): ?string
{
    $bucket = getenv('AWS_S3_BUCKET');
    if (!$bucket || !class_exists('Aws\S3\S3Client') || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('The image must be smaller than 5 MB.');
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    $content_type = mime_content_type($file['tmp_name']);
    if (!in_array($content_type, $allowed_types, true)) {
        throw new RuntimeException('Only JPG, PNG, and WEBP images are allowed.');
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $key = 'products/' . bin2hex(random_bytes(16)) . '.' . $extension;
    $client = new Aws\S3\S3Client([
        'version' => 'latest',
        'region' => getenv('AWS_REGION') ?: 'ap-southeast-1',
    ]);
    $client->putObject([
        'Bucket' => $bucket,
        'Key' => $key,
        'SourceFile' => $file['tmp_name'],
        'ContentType' => $content_type,
    ]);

    return 'https://' . $bucket . '.s3.' . (getenv('AWS_REGION') ?: 'ap-southeast-1') . '.amazonaws.com/' . $key;
}
