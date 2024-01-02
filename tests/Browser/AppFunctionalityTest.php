<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Assert;
use Tests\DuskTestCase;
use Throwable;

class AppFunctionalityTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Sample user data
     */
    private const USER_DATA = [
        'firstname' => 'Magebit',
        'lastname' => 'Test',
        'email' => 'testEmail@testing.com',
        'password' => 'password123',
        'subscribed' => 1
    ];

    /**
     * Function to get html input selector
     *
     * @param string $inputId
     * @param bool $isRegisterForm
     * @return string
     */
    private function getInputSelector(string $inputId, bool $isRegisterForm = true): string
    {
        return sprintf('[data-test="%s-%s"]',$isRegisterForm ? 'register' : 'login', $inputId);
    }

    /**
     * Function to create user directly using the model
     *
     * @return User
     */
    private function createUserDirectly(): User
    {
        return User::create(array_merge(
            self::USER_DATA,
            ['password' => Hash::make(self::USER_DATA['password'])]
        ));
    }

    /**
     * Test if index route is accessible
     *
     * @return void
     * @throws Throwable
     */
    public function testCanAccessIndex()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertRouteIs('index');
        });
    }

    /**
     * Test if login form has email validation
     *
     * @return void
     * @throws Throwable
     */
    public function testLoginEmailValidation()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->type($this->getInputSelector('email', false), 'a')
                ->assertSee('Please provide a valid e-mail address')
                ->keys($this->getInputSelector('email', false), ['{backspace}'])
                ->assertSee('E-mail address is required');
        });
    }

    /**
     * Test if registration form has email validation
     *
     * @return void
     * @throws Throwable
     */
    public function testRegisterEmailValidation()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->type($this->getInputSelector('email'), 'a')
                ->assertSee('Please provide a valid e-mail address')
                ->keys($this->getInputSelector('email'), ['{backspace}'])
                ->assertSee('E-mail address is required');
        });
    }

    /**
     * Test if registration form has password confirm validation
     *
     * @return void
     * @throws Throwable
     */
    public function testRegisterPasswordValidation()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->type($this->getInputSelector('password'), 'test')
                ->assertSee('This field value must be the same as "Password"')
                ->type($this->getInputSelector('passwordConfirm'), 'test')
                ->assertDontSee('This field value must be the same as "Password"');
        });
    }

    /**
     * Test if failed login displays error message
     *
     * @return void
     * @throws Throwable
     */
    public function testLoginErrorMessage()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->type($this->getInputSelector('email', false), self::USER_DATA['email'])
                ->type($this->getInputSelector('password', false), self::USER_DATA['password'])
                ->press($this->getInputSelector('submit', false))
                ->assertRouteIs('index')
                ->assertSee('Password or e-mail is incorrect!');
        });
    }

    /**
     * Test if successful registration displays success message
     *
     * @return void
     * @throws Throwable
     */
    public function testRegisterSuccessMessage()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->type($this->getInputSelector('firstName'), self::USER_DATA['firstname'])
                ->type($this->getInputSelector('lastName'), self::USER_DATA['lastname'])
                ->type($this->getInputSelector('email'), self::USER_DATA['email'])
                ->type($this->getInputSelector('password'), self::USER_DATA['password'])
                ->type($this->getInputSelector('passwordConfirm'), self::USER_DATA['password'])
                ->check($this->getInputSelector('newsletter'))
                ->press($this->getInputSelector('submit'))
                ->assertSee('User registered successfully!');
        });
    }

    /**
     * Test if success route is guarded with authentication
     *
     * @return void
     * @throws Throwable
     */
    public function testSuccessRouteGuard()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/success')
                ->assertRouteIs('index');
        });
    }

    /**
     * Test if login form fails
     *
     * @return void
     * @throws Throwable
     */
    public function testDoesLoginFail()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->type($this->getInputSelector('email', false), self::USER_DATA['email'])
                ->type($this->getInputSelector('password', false), self::USER_DATA['password'])
                ->press($this->getInputSelector('submit', false))
                ->assertRouteIs('index')
                ->assertGuest();
        });
    }

    /**
     * Test if registration form fails
     *
     * @return void
     * @throws Throwable
     */
    public function testDoesRegisterFail()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->type($this->getInputSelector('firstName'), self::USER_DATA['firstname'])
                ->type($this->getInputSelector('lastName'), self::USER_DATA['lastname'])
                ->type($this->getInputSelector('email'), self::USER_DATA['email'])
                ->type($this->getInputSelector('password'), 'wrongPassword')
                ->type($this->getInputSelector('passwordConfirm'), 'wrongPassword2')
                ->press($this->getInputSelector('submit'))
                ->assertRouteIs('index');
            Assert::assertNull(User::where('email', self::USER_DATA['email'])->get()->first(), 'User is created with invalid data');
        });
    }

    /**
     * Test if registration succeeded
     *
     * @return void
     * @throws Throwable
     */
    public function testDoesRegisterSucceed()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->type($this->getInputSelector('firstName'), self::USER_DATA['firstname'])
                ->type($this->getInputSelector('lastName'), self::USER_DATA['lastname'])
                ->type($this->getInputSelector('email'), self::USER_DATA['email'])
                ->type($this->getInputSelector('password'), self::USER_DATA['password'])
                ->type($this->getInputSelector('passwordConfirm'), self::USER_DATA['password'])
                ->check($this->getInputSelector('newsletter'))
                ->press($this->getInputSelector('submit'))
                ->assertRouteIs('index');
            Assert::assertNotNull(User::where('email', self::USER_DATA['email'])->get()->first(), 'User is not created');
        });
    }

    /**
     * Test if login succeeded
     *
     * @return void
     * @throws Throwable
     */
    public function testDoesLoginSucceed()
    {
        $user = $this->createUserDirectly();
        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/')
                ->type($this->getInputSelector('email', false), self::USER_DATA['email'])
                ->type($this->getInputSelector('password', false), self::USER_DATA['password'])
                ->press($this->getInputSelector('submit', false))
                ->assertRouteIs('success')
                ->assertAuthenticatedAs($user);
        });
    }

    /**
     * Test if success page has the required data
     *
     * @return void
     * @throws Throwable
     */
    public function testSuccessPageContent()
    {
        $this->createUserDirectly();
        $this->browse(function (Browser $browser) {
            $browser->loginAs(self::USER_DATA['email'])
                ->visit('/success')
                ->assertRouteIs('success')
                ->waitForTextIgnoringCase(
                    sprintf('Hello, %s %s',
                        self::USER_DATA['firstname'],
                        self::USER_DATA['lastname']
                    )
                );
        });
    }

    /**
     * Test if logout functionality works
     *
     * @return void
     * @throws Throwable
     */
    public function testLogout()
    {
        $this->createUserDirectly();
        $this->browse(function (Browser $browser) {
            $browser->loginAs(self::USER_DATA['email'])
                ->visit('/success')
                ->press('[data-test="logout"]')
                ->assertRouteIs('index')
                ->assertGuest();
        });
    }
}
