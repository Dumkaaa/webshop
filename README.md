## Installation
1. Create `.env.local` (Check out the [environment variables]('#dotenv'))
2. Install dependencies by running the command `composer install`
3. Create the database and load the fixtures by running the command `composer createdb`
4. Your local installation is now ready to use!

## Development
* Lint testing by running the command `composer lint`
    * If you get a cs-fixer error, fix it by running the command `composer fix`.
* PHPUnit by running the command `composer phpunit`
    * NOTE: Create a `.env.test.local` with a custom `DATABASE_URL` environment variable for a testing database.
* Reset the database by running `composer resetdb`

## Environment variables
Variable | Description
--- | ---
APP_ENV | Environment (e.g. prod/dev/test)
APP_SECRET | Secret string
DATABASE_URL | Database details
APP_ADMIN_HOST | The domain of the admin environment (e.g. admin.webshop.com)
APP_WEBSHOP_HOST | The domain of the webshop environment (e.g. webshop.com)

## Twig custom functions and filters
### Functions
Function | Arguments | Description
--- | --- | ---
render_menu | - type (string/MenuTypeInterface, required) <br>- template (string, required) | Renders a menu type.

### Filters
Filter | Arguments | Description
--- | --- | ---

## Features
### Menu builder
Menu's are dynamically generating using a custom menu system. You can create menu types (e.g. [AdminType](https://gitlab.com/Stanjan/webshop/-/blob/master/src/Admin/Menu/AdminType.php)) that can build the menu using the [MenuBuilder](https://gitlab.com/Stanjan/webshop/-/blob/master/src/Menu/MenuBuilder.php).
The menu can be created with the [MenuFactory](https://gitlab.com/Stanjan/webshop/-/blob/master/src/Menu/MenuFactory.php) or in Twig by using the `render_menu` function.