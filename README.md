# PHPMeetup Osijek 18. 06. 2026.

## Installation Steps

### 1. Buy a domain (Namecheap, Spaceship, Porkbun, ...)
June 2026, $1.98 for phpmeetupos.space for one year.

Point NS (NameServer) as CustomDNS to VPS providers name servers, eg. ns1.digitalocean.com

### 2. Deploy VPS (Digital Ocean, Contabo, Heroku, ...)
Add domain to domain list and make A record to VPS instance

You will get user (root) and IP, login in to VPS, example
```bash
ssh root@209.38.252.191
```

### 3. Download the script locally
```bash
curl -sL https://raw.githubusercontent.com/tjakopec/phpmeetup/main/vpsinstall.sh -o vpsinstall.sh
```
### 4. Grant execution permissions
```bash
chmod +x vpsinstall.sh
```

### 5. Run the script
```bash
./vpsinstall.sh
```

---

## vpsinstall.sh is automated Ubuntu 24.04 Symfony Server Setup

Designed to provision a production-ready environment for a Symfony application on Ubuntu 24.04. It handles the full stack installation, configuration, and security hardening.

### Prerequisites
* **Operating System:** A fresh installation of Ubuntu 24.04.
* **Access:** Root privileges.
* **Network:** The server must be reachable via the domain name you intend to use for SSL/Certbot verification.

---

### Installation Workflow

#### 1. Initialization and Environment Setup
* The script verifies root access and initializes a dedicated log file at `/var/log/phpmeetup_install.log` to track all operations.
* It sets up a global `error_handler` to ensure that if any step fails, the installation halts immediately, preventing partial or unstable configurations.

#### 2. User Input Configuration
Before proceeding, the script prompts for the following environment details (default values are provided):
* **Repository URL:** The source code for your Symfony project.
* **Repository Directory:** Where the code will be stored on the server.
* **Domain Name:** The URL for the website (required for Nginx and SSL).
* **Admin Email:** Used for Certbot SSL registration notifications.

#### 3. System Updating and Dependency Installation
* Synchronizes system package lists and upgrades existing software.
* Installs essential core tools, including Git, Unzip, MariaDB, Nginx, Certbot, and the UFW firewall.
* Adds the Ondřej Surý PHP PPA to ensure access to the latest PHP versions.

#### 4. PHP 8.5 Deployment
* Installs PHP 8.5 alongside all required extensions (CLI, FPM, MySQL, XML, Mbstring, Curl, Intl, Zip, and Bcmath).
* Configures the system to use PHP 8.5 as the default CLI version.

#### 5. Database Provisioning
* Automates the MariaDB startup and service enabling.
* Creates the database, initializes a dedicated database user, and assigns the necessary privileges automatically.
* Generates a secure, random 16-character password for the database user.

#### 6. Application Deployment
* Clones the provided Git repository into the target directory.
* Generates a secure `.env` file, including a uniquely generated 24-character `APP_SECRET` for Symfony.

#### 7. Symfony Project Initialization
* Installs all project dependencies using Composer with optimized autoloader settings.
* Clears the application cache and triggers Doctrine migrations to build the database schema.
* Loads initial project fixtures to populate the database.

#### 8. Security and Permissions
* Assigns correct file ownership (`www-data`) to ensure Nginx can serve the application files.
* Configures Access Control Lists (ACL) on the `var` directory to ensure proper read/write permissions for both the web server and the system user.

#### 9. Web Server and SSL Configuration
* Generates a custom Nginx virtual host configuration optimized for Symfony.
* Secures the connection by automatically requesting and installing a free SSL certificate via Let's Encrypt/Certbot.
* Redirects all HTTP traffic to HTTPS.

#### 10. Firewall Hardening
* Enables the UFW firewall.
* Explicitly allows traffic for SSH (22), HTTP (80), and HTTPS (443) to keep the server secure while maintaining accessibility.

### Post-Installation
Upon completion, the script outputs a summary dashboard containing the installation URL, database credentials, and directory paths. Ensure you save these credentials in a secure password manager.


## Human dependency list
| Name | Version | Description | Est. CVEs (2 yrs) |
| :--- | :--- | :--- | :--- |
| php | >=8.4 | Server-side scripting language | ~15+ |
| ext-ctype | * | Character type checking extension | 0* |
| ext-iconv | * | Character set conversion extension | 0* |
| api-platform/core | ^4.3 | API development framework for Symfony | < 5 |
| doctrine/doctrine-bundle | ^3.2.2 | Symfony bundle for Doctrine ORM | < 3 |
| doctrine/doctrine-migrations-bundle | ^4.0 | Database migration management | 0 |
| doctrine/orm | ^3.6.7 | Object-Relational Mapper | < 5 |
| phpdocumentor/reflection-docblock | ^6.0.3 | Reflection library for PHPDoc blocks | 0 |
| phpstan/phpdoc-parser | ^2.3.2 | PHPDoc parser for static analysis | 0 |
| symfony/apache-pack | >=1.0.1 | Configuration for Apache web server | 0 |
| symfony/asset | 8.0.* | Manages asset URL generation | 0 |
| symfony/console | 8.0.* | CLI command builder | < 2 |
| symfony/dotenv | 8.0.* | Environment variable loader | 0 |
| symfony/flex | ^2.10 | Composer plugin for Symfony | 0 |
| symfony/framework-bundle | 8.0.* | Core Symfony framework integration | < 10 |
| symfony/property-access | 8.0.* | Object property read/write utility | 0 |
| symfony/property-info | 8.0.* | Extract information about class properties | 0 |
| symfony/runtime | 8.0.* | Decouples application from entry points | 0 |
| symfony/security-bundle | 8.0.* | Security and authentication | < 5 |
| symfony/serializer | 8.0.* | Object to/from array/JSON serialization | < 2 |
| symfony/twig-bundle | 8.0.* | Integration for Twig template engine | 0 |
| symfony/yaml | 8.0.* | YAML format parser/dumper | 0 |
| twig/extra-bundle | ^2.12\|3.24 | Additional features for Twig | 0 |
| twig/twig | ^2.12\|3.27 | Template engine for PHP | < 3 |


## Dependency comparation
| Dependency Name | Human | Claude | Gemini | Qwen |
| :--- | :--- | :--- | :--- | :--- |
| **php** | >=8.4 | >=8.2 | >=8.4 | >=8.3 |
| **api-platform/core** | ^4.3 | ^3.3 | - | ^4.0 |
| **api-platform/doctrine-orm** | - | - | ^4.3 | - |
| **api-platform/symfony** | - | - | ^4.3 | - |
| **doctrine/doctrine-bundle** | ^3.2.2 | ^2.12 | ^3.2 | - |
| **doctrine/doctrine-fixtures-bundle** | - | ^3.6 | - | - |
| **doctrine/doctrine-migrations-bundle** | ^4.0 | ^3.3 | ^4.0 | - |
| **doctrine/orm** | ^3.6.7 | ^3.2 | ^3.6 | - |
| **ext-ctype** | * | * | * | - |
| **ext-iconv** | * | * | * | - |
| **nelmio/cors-bundle** | - | - | ^2.6 | - |
| **phpdocumentor/reflection-docblock** | ^6.0.3 | - | ^6.0 | - |
| **phpstan/phpdoc-parser** | ^2.3.2 | - | ^2.3 | - |
| **phpstan/phpstan** | - | - | - | ^1.10 |
| **phpunit/phpunit** | - | - | ^13.2 | - |
| **symfony/apache-pack** | >=1.0.1 | - | - | - |
| **symfony/asset** | 8.0.* | - | 8.1.* | - |
| **symfony/browser-kit** | - | - | 8.1.* | - |
| **symfony/config** | - | - | - | ^7.0 |
| **symfony/console** | 8.0.* | 7.4.* | 8.1.* | - |
| **symfony/dependency-injection** | - | - | - | ^7.0 |
| **symfony/dotenv** | 8.0.* | 7.4.* | 8.1.* | - |
| **symfony/expression-language** | - | - | 8.1.* | - |
| **symfony/flex** | ^2.10 | ^2 | ^2 | - |
| **symfony/framework-bundle** | 8.0.* | 7.4.* | 8.1.* | - |
| **symfony/http-client** | - | - | 8.1.* | - |
| **symfony/property-access** | 8.0.* | 7.4.* | 8.1.* | - |
| **symfony/property-info** | 8.0.* | - | 8.1.* | - |
| **symfony/runtime** | 8.0.* | 7.4.* | 8.1.* | - |
| **symfony/security-bundle** | 8.0.* | - | 8.1.* | - |
| **symfony/serializer** | 8.0.* | 7.4.* | 8.1.* | ^7.0 |
| **symfony/twig-bundle** | 8.0.* | - | 8.1.* | - |
| **symfony/validator** | - | 7.4.* | 8.1.* | ^7.0 |
| **symfony/yaml** | 8.0.* | 7.4.* | 8.1.* | ^7.0 |
| **twig/extra-bundle** | ^2.12\|3.24 | - | - | - |
| **twig/twig** | ^2.12\|3.27 | - | - | - |

## Human project baseline
```bash
├── bin
│   ├── console
│   └── phpunit
├── composer.json
├── config
│   ├── bundles.php
│   ├── packages
│   │   ├── api_platform.yaml
│   │   ├── cache.yaml
│   │   ├── doctrine.yaml
│   │   ├── doctrine_migrations.yaml
│   │   ├── framework.yaml
│   │   ├── property_info.yaml
│   │   ├── rate_limiter.yaml
│   │   ├── routing.yaml
│   │   ├── security.yaml
│   │   ├── test
│   │   │   └── rate_limiter.yaml
│   │   ├── twig.yaml
│   │   ├── validator.yaml
│   │   └── web_profiler.yaml
│   ├── preload.php
│   ├── reference.php
│   ├── routes
│   │   ├── api_platform.yaml
│   │   ├── framework.yaml
│   │   ├── security.yaml
│   │   └── web_profiler.yaml
│   ├── routes.yaml
│   └── services.yaml
├── migrations
│   └── Version20260601100717.php
├── phpstan-baseline.neon
├── phpstan.neon
├── phpunit.dist.xml
├── public
│   ├── .htaccess
│   └── index.php
├── src
│   ├── ApiResource
│   ├── Controller
│   │   ├── HomeController.php
│   │   └── SecurityController.php
│   ├── DataFixtures
│   │   ├── AppFixtures.php
│   │   ├── PostNumbersFixture.php
│   │   ├── ServiceTypeFixture.php
│   │   └── UserFixtures.php
│   ├── Dto
│   │   ├── DimensionsDto.php
│   │   ├── PriceBreakdown.php
│   │   ├── QuoteRequest.php
│   │   └── QuoteResponse.php
│   ├── Entity
│   │   ├── BaseEntity.php
│   │   ├── PostOffice.php
│   │   ├── ServiceType.php
│   │   ├── ShippingZone.php
│   │   ├── Tariff.php
│   │   └── User.php
│   ├── EventListener
│   │   └── RequestDataListener.php
│   ├── Kernel.php
│   ├── Repository
│   │   ├── PostOfficeRepository.php
│   │   ├── ServiceTypeRepository.php
│   │   ├── ShippingPriceAllInOneRepository.php
│   │   ├── ShippingZoneRepository.php
│   │   ├── TariffRepository.php
│   │   └── UserRepository.php
│   ├── Security
│   │   └── AppCustomAuthenticator.php
│   ├── Service
│   │   ├── RequestDataStore.php
│   │   ├── ShippingPriceCalculator.php
│   │   └── ShippingPriceCalculatorInterface.php
│   ├── State
│   │   └── QuoteProcessor.php
│   └── Validator
│       ├── CroatianPostalCode.php
│       ├── CroatianPostalCodeValidator.php
│       ├── ServiceTypeExists.php
│       ├── ServiceTypeExistsValidator.php
│       ├── ShippingLimitsConstraint.php
│       └── ShippingLimitsConstraintValidator.php
├── templates
│   ├── base.html.twig
│   ├── home
│   │   ├── index.html.twig
│   │   └── public.html.twig
│   └── security
│       └── login.html.twig
└── tests
    ├── Controller
    │   └── ApiRateLimiterTest.php
    ├── Service
    │   ├── PHPMeetup
    │   │   ├── PHPMeetupTaskJSONTest.php
    │   │   └── PHPMeetupTaskTest.php
    │   └── ShippingCalculator
    │       ├── EdgeCasesTest.php
    │       ├── PriorityMultiplierTest.php
    │       ├── TestBase.php
    │       ├── VolumetricWeightTest.php
    │       ├── WeightTiersTest.php
    │       └── ZoneResolutionTest.php
    └── bootstrap.php
```

## AI analysis of completion
### Claude 50%
### Gemini 30% (no database)
### Qwen 5% (no database)

### Review Notes:
- **phpmeetupClaude**: Implemented core requirements including API Platform, DTOs, a proper calculator service interface, Doctrine ORM entities, data fixtures, and a test suite. However, it completely missed the advanced architectural features present in the baseline (Security, Web Frontend, Event Listeners, Rate Limiting, and Custom Validation Constraints).
- **phpmeetupGemini**: Implemented API Platform integration, the calculator service architecture, and test coverage. It failed to implement any form of database persistence (missing ORM Entities, Repositories, and Fixtures) relying on hardcoded configuration instead. It also missed all the advanced features (Security, Logging, Web Frontend, Rate Limiting, etc.).
- **phpmeetupQwen**: The furthest from the baseline. It abandoned API Platform entirely for a standard Symfony Controller, missed the interface for the calculator service, and relied on JSON file parsing instead of a database/ORM. It had no tests, no security, no frontend, and none of the baseline's advanced architectural features.