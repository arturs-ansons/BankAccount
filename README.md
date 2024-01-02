# Laravel: Blog Application

## Project Specifications

**Read-Only Files**
- tests/*

**Environment**
- PHP version: 7.4
- Laravel version:
- Default Port: 8000
- Sqlite3 (PHP package)


**Commands**
- run:
```bash
yes | php artisan migrate && npm run watch
```
- install:
```bash
composer install && npm install
```
- test:
```bash
yes | php artisan migrate:refresh && ./vendor/bin/phpunit tests --log-junit junit.xml
```

## Question description

In this challenge, your task is to do styling adjustments to the account page and implement login and registration functionality.

Each user has the following structure:

- `id`: The unique ID of the user. (Integer)
- `firstname`: The First name of the user. (String)
- `lastname`: The Last name of the user. (String)
- `email`: The unique email of the user. (String)
- `subscribed`: "Is subscribed to newsletter" boolean value. (Tinyint)
- `created_at`: Created at timestamp. (created_at)
- `updated_at`: Updated at timestamp. (updated_at)

## Frontend

### Requirements
* The page is responsive (There is no design for mobile in style guide, but we expect it to work)
* The logo must be changed
* Elements must be styled according to style guide
  * Logo
  * Buttons
  * Main navigation
  * Colors
  * Links
  * Input fields
* Logo and copyright is changed according to style guide
* The input field is validated - you are free to choose frameworks, or use vanilla JS
  * There is an error message under input that show validation messages if:
    * Invalid email is added - "Please provide a valid e-mail address"
    * No email address is provided - “Email address is required”
    * Password and "Confirm password" fields are not identical - "This field value must be the same as "Password"." 
* **IMPORTANT:** All input fields should have according `data-test=""` attribute set, for automated testing to work.
  * **Login** (these are already pre-set in template)
    *  E-mail: `data-test="login-email"`
    *  Password: `data-test="login-password"`
    *  Submit button: `data-test="login-submit"`
  * **Register**
    *  First name: `data-test="register-firstName"`
    *  Last name: `data-test="register-lastName"`
    *  E-mail: `data-test="register-email"`
    *  Password: `data-test="register-password"`
    *  Password confirmation: `data-test="register-passwordConfirm"`
    *  Newsletter checkbox: `data-test="register-newsletter"`
    *  Submit button: `data-test="register-submit"`
  * **Success page** (this is already pre-set in template)
    * Logout button: `data-test="logout"`

**NOTE:** All existing `data-test` attributes must not be moved/removed, otherwise automated tests will fail.

### Design
* [Style guide link on Figma](https://www.figma.com/file/KnO6VpOnWyHbFWhALbDqmy/Hackerrank?type=design&node-id=0-1&t=SCieW94ySlwOLWJB-0)


## Backend functionality requirements:

You are provided with the implementation of the User model:

`GET /`:
- in `page/account.blade.php` must be added possibility to display error and success message

`POST` `/login`:
- create request validation
- if the email and password fields are correct
  - the user must be logged in
  - redirect to /success page
- otherwise, 
  - redirect to home page with error message- "Password or e-mail is incorrect!"

`GET /success`:
- `page/success.blade.php` contains prefilled data
- if the user is authorized, $user must return Firstname and Lastname value, and should be passed to `page/success.blade.php`
- if the user is not authorized, he/she must be redirected to the home route

`GET /logout`:
- method must logout the user if it is authorized, and must redirect to the home route

`POST` `/register`:
- create request validation
- firstname, lastname, email and password fields are required
  - password must be at least 8 characters and must contain letters and numbers
  - confirm_password and password must be identical
  - email must be unique
- subscribed field is optional and must contain boolean value (1, 0)
- redirect on home page for a successful 200 response with success message- "User registered successfully!"


### Notes
Each form must contain CSRF Token, e.g.
```html
@csrf

<!-- Equivalent to... -->
<input type="hidden" name="_token" value="{{ csrf_token() }}" /> 
```
more information available [here](https://laravel.com/docs/10.x/csrf#preventing-csrf-requests).
