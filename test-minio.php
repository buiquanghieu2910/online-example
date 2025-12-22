<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use Aws\S3\S3Client;

$config = [
    'version' => 'latest',
    'region' => $_ENV['MINIO_REGION'],
    'endpoint' => $_ENV['MINIO_ENDPOINT'],
    'use_path_style_endpoint' => true,
    'credentials' => [
        'key' => $_ENV['MINIO_ACCESS_KEY'],
        'secret' => $_ENV['MINIO_SECRET_KEY'],
    ],
];

echo "Connecting to MinIO at: {$_ENV['MINIO_ENDPOINT']}\n";
echo "Access Key: {$_ENV['MINIO_ACCESS_KEY']}\n";
echo "Bucket: {$_ENV['MINIO_BUCKET']}\n\n";

try {
    $s3Client = new S3Client($config);
    
    // List all buckets
    echo "Listing buckets...\n";
    $result = $s3Client->listBuckets();
    
    foreach ($result['Buckets'] as $bucket) {
        echo "- {$bucket['Name']}\n";
    }
    
    // Check if our bucket exists
    $bucketName = $_ENV['MINIO_BUCKET'];
    if ($s3Client->doesBucketExist($bucketName)) {
        echo "\n✓ Bucket '{$bucketName}' exists!\n";
    } else {
        echo "\n✗ Bucket '{$bucketName}' does not exist. Creating...\n";
        $s3Client->createBucket(['Bucket' => $bucketName]);
        echo "✓ Bucket '{$bucketName}' created successfully!\n";
    }
    
    echo "\n✓ MinIO connection successful!\n";
} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
}
