## Installation
1. Create `.env.local` (Check out the [environment variables]('#dotenv'))
2. Install dependencies by running the command `composer install`
3. Create the database and load the fixtures by running the command `composer resetdb`
4. Your local installation is now ready to use!

## Development
* Lint testing by running the command `composer lint`
    * If you get a cs-fixer error, fix it by running the command `composer fix`.
* PHPUnit by running the command `composer phpunit`
    * NOTE: Create a `.env.test.local` with a custom `DATABASE_URL` environment variable for a testing database.

## Environment variables
Variable | Description
--- | ---
APP_ENV | Environment (e.g. prod/dev/test)
APP_SECRET | Secret string
DATABASE_URL | Database details
APP_ADMIN_HOST | The domain of the admin environment (e.g. admin.webshop.com)
APP_WEBSHOP_HOST | The domain of the webshop environment (e.g. webshop.com)