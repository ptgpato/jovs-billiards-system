# Jovs Billiards and Chill

Jovs Billiards and Chill is a PHP and MySQL inventory and sales management system for a billiards business. It provides:

- Owner authentication and dashboard
- Cashier authentication
- Product and inventory management
- Inventory quantity and price updates
- Customer and sales transaction recording
- Atomic stock deduction during sales
- Product image upload to Amazon S3

The application is designed for a simple cloud deployment using:

```text
Users
  |
  v
Amazon EC2: Apache + PHP
  |
  +--> Amazon RDS for MySQL: application database
  |
  +--> Amazon S3: product image object storage
```

## Technology stack

- PHP 8.2+
- Apache
- MySQL 8 or compatible MariaDB
- MySQLi
- Composer
- AWS SDK for PHP
- Amazon EC2
- Amazon RDS for MySQL
- Amazon S3

## Docker

Docker support is provided through a PHP 8.2/Apache image:

```powershell
docker compose up --build
```

This starts the application and a local MySQL 8.4 database at `http://localhost:8080/login.php`.
For AWS, use `docker-compose.aws.yml`; it starts only the application container and uses the existing RDS database and EC2 IAM role for S3 access. See [DEPLOYMENT.md](./DEPLOYMENT.md) for the required `.env` values and commands.

## Project structure

```text
.
├── db_connect.php          # Database connection using environment variables
├── index.php               # Login form processing
├── login.php               # Login page
├── owner_dashboard.php     # Owner dashboard
├── inventory.php           # Inventory and product image management
├── sales.php               # Customer and sales transaction management
├── user_management.php     # Cashier account management
├── logout.php              # Session logout
├── s3_storage.php          # Amazon S3 upload helper
├── jovsdb.sql              # Database schema and seed data
├── composer.json           # PHP dependencies
├── composer.lock           # Locked dependency versions
└── DEPLOYMENT.md           # Short deployment checklist
```

## Default accounts

The database seed file contains these demonstration accounts:

| Role | Username | Password |
|---|---|---|
| Owner | `admin` | `admin1` |
| Cashier | `cashier` | `cashier1` |

Change these credentials for any real deployment. They are intended for school-project demonstration only.

## Local setup with XAMPP

### Requirements

Install:

- XAMPP with Apache, PHP, and MySQL/MariaDB
- Composer

### Install dependencies

From the project directory:

```powershell
composer install --no-dev
```

If Composer is installed only for the current Windows user but is not recognized, close and reopen PowerShell. The AWS SDK is installed from [composer.json](./composer.json).

### Create the local database

Start Apache and MySQL/MariaDB in XAMPP, then run:

```powershell
mysql -u root -e "CREATE DATABASE IF NOT EXISTS jovsdb"
mysql -u root jovsdb < jovsdb.sql
```

The local PHP defaults in `db_connect.php` are:

```text
DB_HOST=localhost
DB_NAME=jovsdb
DB_USER=root
DB_PASSWORD=
```

Open:

```text
http://localhost/jovs/login.php
```

## AWS deployment architecture

For the cloud deployment, use:

- **EC2** to run Apache and PHP
- **RDS for MySQL** to store users, products, inventory, customers, and sales
- **S3** to store product image objects
- **IAM role** attached to EC2 so the application can upload to S3 without storing AWS keys in source code

Keep the RDS instance private if possible. The RDS security group should allow port `3306` only from the EC2 security group.

## AWS prerequisites

Before deploying, prepare:

1. An EC2 instance in your AWS account.
2. An RDS MySQL database named `jovsdb`.
3. An S3 bucket in the same AWS region as EC2.
4. An IAM role attached to EC2.
5. EC2 security group rules for SSH and HTTP.
6. RDS security group access from the EC2 security group only.

Recommended EC2 inbound rules:

| Type | Port | Source |
|---|---:|---|
| SSH | 22 | Your IP address only |
| HTTP | 80 | `0.0.0.0/0` |

Do not expose RDS port `3306` to `0.0.0.0/0`.

## Create the RDS database

Create an RDS MySQL database and record:

- RDS endpoint
- Database name
- Database username
- Database password
- AWS region

From EC2, test the database connection:

```bash
mysql -h YOUR_RDS_ENDPOINT -u YOUR_RDS_USER -p jovsdb
```

Import the application schema:

```bash
mysql -h YOUR_RDS_ENDPOINT -u YOUR_RDS_USER -p jovsdb < jovsdb.sql
```

Verify the import:

```sql
SHOW TABLES;
SELECT username, role FROM users;
```

## Create and configure S3

Create an S3 bucket, for example:

```text
jovs-product-images-your-name
```

The application stores uploaded product images under:

```text
products/
```

Keep Block Public Access enabled unless your instructor specifically requires public objects. For a simple project demonstration, the application stores the S3 object URL in RDS.

Attach an IAM policy to the EC2 instance role. Replace `YOUR_BUCKET` with the actual bucket name:

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": [
        "s3:PutObject"
      ],
      "Resource": "arn:aws:s3:::YOUR_BUCKET/products/*"
    }
  ]
}
```

Do not add AWS access keys to PHP files or commit them to GitHub.

## Install the application on EC2

Connect to EC2 using SSH:

```bash
ssh -i YOUR_KEY.pem ubuntu@YOUR_EC2_PUBLIC_IP
```

Use `ec2-user` instead of `ubuntu` for Amazon Linux.

### Ubuntu installation

```bash
sudo apt update
sudo apt install -y apache2 php php-mysql php-curl php-mbstring php-xml php-zip unzip git composer mysql-client
sudo systemctl enable apache2
sudo systemctl start apache2
```

### Amazon Linux installation

```bash
sudo dnf update -y
sudo dnf install -y httpd php php-mysqli php-curl php-mbstring php-xml php-zip unzip git mariadb105
sudo systemctl enable httpd
sudo systemctl start httpd
```

Install Composer if it is not already available:

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
rm composer-setup.php
```

Clone the GitHub repository:

```bash
sudo git clone https://github.com/YOUR_GITHUB_USERNAME/YOUR_REPOSITORY.git /var/www/html/jovs
cd /var/www/html/jovs
composer install --no-dev
```

For a private repository, authenticate with GitHub using an appropriate GitHub method. Never place a GitHub token in the repository files.

Set ownership:

```bash
# Ubuntu
sudo chown -R www-data:www-data /var/www/html/jovs

# Amazon Linux
sudo chown -R apache:apache /var/www/html/jovs
```

## Configure the application environment

The committed [.env.example](./.env.example) is only a template. Do not put real passwords in it.

The application reads these values with `getenv()`:

```text
DB_HOST=YOUR_RDS_ENDPOINT
DB_NAME=jovsdb
DB_USER=YOUR_RDS_USER
DB_PASSWORD=YOUR_RDS_PASSWORD
AWS_REGION=YOUR_AWS_REGION
AWS_S3_BUCKET=YOUR_S3_BUCKET
```

For Apache, configure these as server environment variables. On Ubuntu, one simple approach is to add them to `/etc/apache2/envvars`:

```bash
sudo nano /etc/apache2/envvars
```

Add:

```bash
export DB_HOST="YOUR_RDS_ENDPOINT"
export DB_NAME="jovsdb"
export DB_USER="YOUR_RDS_USER"
export DB_PASSWORD="YOUR_RDS_PASSWORD"
export AWS_REGION="YOUR_AWS_REGION"
export AWS_S3_BUCKET="YOUR_S3_BUCKET"
```

For Amazon Linux, create `/etc/httpd/conf.d/jovs-env.conf`:

```apache
SetEnv DB_HOST "YOUR_RDS_ENDPOINT"
SetEnv DB_NAME "jovsdb"
SetEnv DB_USER "YOUR_RDS_USER"
SetEnv DB_PASSWORD "YOUR_RDS_PASSWORD"
SetEnv AWS_REGION "YOUR_AWS_REGION"
SetEnv AWS_S3_BUCKET "YOUR_S3_BUCKET"
```

Protect the configuration:

```bash
sudo chmod 600 /etc/httpd/conf.d/jovs-env.conf
```

Restart Apache:

```bash
# Ubuntu
sudo systemctl restart apache2

# Amazon Linux
sudo systemctl restart httpd
```

## Verify the deployment

Open:

```text
http://YOUR_EC2_PUBLIC_IP/jovs/login.php
```

Test the following:

1. Log in as `admin`.
2. Open Inventory Management.
3. Add a product.
4. Upload a JPG, PNG, or WEBP product image smaller than 5 MB.
5. Confirm the image object appears in the S3 `products/` prefix.
6. Confirm the product and image URL exist in RDS.
7. Create a sales transaction.
8. Confirm the transaction is recorded in RDS.
9. Confirm product stock decreases.
10. Verify invalid quantities are rejected.

Sales are processed transactionally: the customer record, transaction record, and stock deduction are committed together or rolled back together.

## Troubleshooting

### Composer is not recognized

Close and reopen the terminal, then run:

```bash
composer --version
composer install --no-dev
```

### Database connection fails

Check:

- RDS status is available.
- The EC2 security group is allowed by the RDS security group.
- RDS endpoint, username, password, and database name are correct.
- Port `3306` is open from EC2 to RDS.

Test:

```bash
mysql -h YOUR_RDS_ENDPOINT -u YOUR_RDS_USER -p jovsdb
```

### S3 upload fails

Check:

- `AWS_REGION` is correct.
- `AWS_S3_BUCKET` exactly matches the bucket name.
- The EC2 instance has an IAM role.
- The IAM role allows `s3:PutObject` for `products/*`.
- Composer dependencies exist in `vendor/`.

Check the AWS SDK:

```bash
ls vendor/aws/aws-sdk-php
```

### Apache returns a PHP error

Check the Apache error log:

```bash
# Ubuntu
sudo tail -f /var/log/apache2/error.log

# Amazon Linux
sudo tail -f /var/log/httpd/error_log
```

## Updating the deployed application

From EC2:

```bash
cd /var/www/html/jovs
sudo git pull origin main
sudo -u www-data composer install --no-dev
sudo systemctl restart apache2
```

For Amazon Linux, replace `www-data` with `apache` and restart `httpd`.

Before every update:

1. Test the application locally.
2. Commit and push changes to GitHub.
3. Pull the changes on EC2.
4. Run database migrations carefully if the schema changed.
5. Verify login, inventory, S3 upload, and sales.

## Security notes

This is a simple school-project deployment. Even so:

- Do not commit `.env`, AWS keys, RDS passwords, or `.pem` files.
- Use an EC2 IAM role instead of hardcoded AWS credentials.
- Use a non-root RDS database user.
- Restrict RDS access to the EC2 security group.
- Keep S3 permissions limited to the required bucket path.
- Change the demonstration passwords before public access.
