# AWS deployment

This project uses an EC2 PHP server, Amazon RDS for MySQL, and Amazon S3 for product images.

## Docker deployment

The application includes a PHP 8.2/Apache image. Docker does not replace RDS or S3 in the AWS deployment:

- `docker-compose.yml` runs the application with a local MySQL container for development.
- `docker-compose.aws.yml` runs only the application container and connects to the existing RDS and S3 resources.

### Local Docker test

Install Docker Desktop, then run from the project directory:

```powershell
docker compose up --build
```

Open `http://localhost:8080/login.php`. The first start imports `jovsdb.sql` into the local MySQL volume. To reset that database, use:

```powershell
docker compose down -v
```

### Docker on EC2 with existing RDS

Install Docker Compose on Ubuntu, clone or pull the repository, and create `/var/www/html/jovs/.env` with the RDS and S3 values. Do not commit `.env`:

```text
DB_HOST=YOUR_RDS_ENDPOINT
DB_NAME=jovsdb
DB_USER=YOUR_DB_USER
DB_PASSWORD=YOUR_DB_PASSWORD
AWS_REGION=ap-southeast-2
AWS_S3_BUCKET=YOUR_BUCKET
```

Start the app container without creating a local database:

```bash
docker compose -f docker-compose.aws.yml up -d --build
```

The EC2 IAM role supplies AWS credentials to the container. Do not add AWS access keys to `.env` or source code. Allow HTTP port 80 in the EC2 security group, and keep RDS port 3306 restricted to the EC2 security group.

## AWS resources

1. Create an RDS MySQL database named `jovsdb`.
2. Allow port 3306 from the EC2 security group only.
3. Create an S3 bucket in the same region as EC2.
4. Attach an EC2 IAM role with `s3:PutObject` permission for `arn:aws:s3:::YOUR_BUCKET/products/*`.

## Install on EC2

Install Apache, PHP, PHP-MySQL, PHP-CURL, PHP-MBString, PHP-XML, Git, and Composer. Copy this project to the web root, then run:

```bash
composer install --no-dev
```

Import the schema into RDS:

```bash
mysql -h YOUR_RDS_ENDPOINT -u YOUR_DB_USER -p jovsdb < jovsdb.sql
```

Configure these environment variables for Apache/PHP:

```text
DB_HOST=YOUR_RDS_ENDPOINT
DB_NAME=jovsdb
DB_USER=YOUR_DB_USER
DB_PASSWORD=YOUR_DB_PASSWORD
AWS_REGION=YOUR_AWS_REGION
AWS_S3_BUCKET=YOUR_BUCKET
```

The EC2 IAM role supplies AWS credentials; do not put access keys in source code.

## Verify

Sign in with the owner account, open Inventory Management, and upload a product image. The image is stored in S3 and its URL is stored in RDS. Create a sale and confirm that stock and the transaction record are updated atomically in RDS.
