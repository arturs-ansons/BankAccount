<?php

namespace Tests\Browser;

use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Throwable;

class AppStylingTest extends DuskTestCase
{
    /**
     * Colors to be reused
     */
    private const COLORS = [
        'primary' => 'rgba(15, 131, 82, 1)', // #0F8352
        'primary-darker' => 'rgba(10, 87, 55, 1)', // #0A5737
    ];

    /**
     * Styling constants for testing
     */
    private const STYLING_VALUES = [
        'font-family' => 'Montserrat',
        'button-color' => self::COLORS['primary'],
        'button-color-hover' => 'rgba(20, 174, 109, 1)', // #14AE6D
        'button-color-active' => self::COLORS['primary-darker'],
        'header-icon-color' => 'rgba(2, 23, 14, 1)', // #02170E
        'header-icon-color-hover' => self::COLORS['primary'],
        'header-icon-color-active' => self::COLORS['primary-darker'],
        'link-color' => 'rgba(2, 23, 14, 1)', // #02170E
        'link-color-hover' => self::COLORS['primary'],
        'link-color-active' => self::COLORS['primary-darker'],
        'header-link-underline' => 'none',
        'header-link-underline-hover' => 'none',
        'header-link-underline-active' => 'none',
        'header-link-font-weight' => '500',
        'header-bar-shadow' => 'drop-shadow(rgba(0, 0, 0, 0.25) 0px 1px 8px)',
        'forgot-password-color' => 'rgba(109, 109, 109, 1)', // #6D6D6D
        'forgot-password-color-hover' => self::COLORS['primary'],
        'forgot-password-color-active' => self::COLORS['primary-darker'],
        'forgot-password-decoration' => 'underline',
        'checkbox-border-radius' => '4px',
        'checkbox-border-color-hover' => 'rgb(15, 131, 82)', // #0F8352
        'checkbox-checked-color' => self::COLORS['primary']
    ];

    /**
     * @param $element
     * @param $stylingValueKey
     * @param $attributeToAssert
     * @param $errorMsg
     * @return void
     */
    private function assertElementStyling($element, $stylingValueKey, $attributeToAssert, $errorMsg)
    {
        $this->assertEquals(
            Str::lower(self::STYLING_VALUES[$stylingValueKey]),
            Str::lower($element->getCSSValue($attributeToAssert)),
            $errorMsg
        );
    }

    /**
     * @param $browser
     * @param $element
     * @param $attributeToCheck
     * @param $expectedValue
     * @param $errorMessage
     * @return void
     */
    private function checkElementStylingWithStates(
        $browser,
        $element,
        $attributeToCheck,
        $expectedValue,
        $errorMessage = 'Element'
    ) {
        $this->assertElementStyling(
            $element,
            $expectedValue,
            $attributeToCheck,
            sprintf('%s has incorrect %s.', $errorMessage, $attributeToCheck)
        );

        $browser->driver->getMouse()->mouseMove($element->getCoordinates());
        $this->assertElementStyling(
            $element,
            sprintf('%s-hover', $expectedValue),
            $attributeToCheck,
            sprintf('%s has incorrect hover %s.', $errorMessage, $attributeToCheck)
        );

        $browser->driver->getMouse()->mouseDown($element->getCoordinates());
        $this->assertElementStyling(
            $element,
            sprintf('%s-active', $expectedValue),
            $attributeToCheck,
            sprintf('%s has incorrect active %s.', $errorMessage, $attributeToCheck)
        );
        $browser->driver->getMouse()->mouseUp();
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testIsCorrectFontFamily()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
            $tag = $browser->driver->findElement(WebDriverBy::tagName('body'));
            $fontFamily = str_replace('"', '', Str::lower($tag->getCSSValue('font-family')));
            $this->assertTrue(
                Str::startsWith($fontFamily, Str::lower(self::STYLING_VALUES['font-family'])),
                'Body has incorrect font family'
            );
        });
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testIsButtonCorrectColor()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
            foreach ($browser->driver->findElements(WebDriverBy::tagName('button')) as $tag) {
                $this->checkElementStylingWithStates(
                    $browser,
                    $tag,
                    'background-color',
                    'button-color',
                    sprintf('Button with text %s', $tag->getText())
                );
            }
        });
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testIsHeaderIconsCorrectColor()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
            foreach ($browser->driver->findElements(WebDriverBy::cssSelector('[data-test=header-icon]')) as $tag) {
                $this->checkElementStylingWithStates(
                    $browser,
                    $tag,
                    'color',
                    'header-icon-color',
                    'Header icon'
                );
            }
        });
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testIsHeaderLinksCorrectColor()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
            foreach ($browser->driver->findElements(WebDriverBy::cssSelector('[data-test=header-link]')) as $tag) {
                $this->checkElementStylingWithStates(
                    $browser,
                    $tag,
                    'color',
                    'link-color',
                    sprintf('Header link with text "%s"', $tag->getText())
                );
            }
        });
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testIsHeaderLinksWithoutUnderline()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
            foreach ($browser->driver->findElements(WebDriverBy::cssSelector('[data-test=header-link]')) as $tag) {
                $this->checkElementStylingWithStates(
                    $browser,
                    $tag,
                    'text-decoration-line',
                    'header-link-underline',
                    sprintf('Header link with text "%s"', $tag->getText())
                );
            }
        });
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testIsHeaderLinksCorrectWeight()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
            foreach ($browser->driver->findElements(WebDriverBy::cssSelector('[data-test=header-link]')) as $tag) {
                $this->assertElementStyling(
                    $tag,
                    'header-link-font-weight',
                    'font-weight',
                    sprintf('Header link with text "%s"', $tag->getText())
                );
            }
        });
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testIsHeaderLinksUppercase()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
            foreach ($browser->driver->findElements(WebDriverBy::cssSelector('[data-test=header-link]')) as $tag) {
                $this->assertTrue(
                    ctype_upper(preg_replace('/[^a-zA-Z]/', '', $tag->getText())),
                    sprintf('Header link with text "%s" is not uppercase.', $tag->getText())
                );
            }
        });
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testDoesHeaderBarHaveShadow()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
            $tag = $browser->driver->findElement(WebDriverBy::cssSelector('[data-test=header-bar]'));
            $this->assertElementStyling(
                $tag,
                'header-bar-shadow',
                'filter',
                'Header bar has missing/incorrect shadow.'
            );
        });
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testForgotPasswordColor()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
            $tag = $browser->driver->findElement(WebDriverBy::linkText('Forgot Your Password?'));
            $this->checkElementStylingWithStates(
                $browser,
                $tag,
                'color',
                'forgot-password-color',
                'Forgot password link'
            );
        });
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testForgotPasswordUnderline()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
            $tag = $browser->driver->findElement(WebDriverBy::linkText('Forgot Your Password?'));
            $this->assertElementStyling(
                $tag,
                'forgot-password-decoration',
                'text-decoration-line',
                'Forgot password link doesn\'t have underline.'
            );
        });
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testFooterLinksColor()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
            foreach ($browser->driver->findElements(WebDriverBy::cssSelector('[data-test=footer-link]')) as $tag) {
                $this->checkElementStylingWithStates(
                    $browser,
                    $tag,
                    'color',
                    'link-color',
                    sprintf('Footer link with text "%s"', $tag->getText())
                );
            }
        });
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testFooterLinksUnderline()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
            foreach ($browser->driver->findElements(WebDriverBy::cssSelector('[data-test=footer-link]')) as $tag) {
                $this->checkElementStylingWithStates(
                    $browser,
                    $tag,
                    'text-decoration-line',
                    'header-link-underline',
                    sprintf('Footer link with text "%s"', $tag->getText())
                );
            }
        });
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testCheckboxStyling()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
            $tag = $browser->driver->findElement(WebDriverBy::cssSelector('[data-test="register-newsletter"]'));
            $this->assertElementStyling(
                $tag,
                'checkbox-border-radius',
                'border-radius',
                'Newsletter checkbox doesn\'t have rounded border'
            );

            $browser->driver->getMouse()->mouseMove($tag->getCoordinates());
            $this->assertElementStyling(
                $tag,
                'checkbox-border-color-hover',
                'border-color',
                'Newsletter checkbox doesn\'t have correct hover border-color'
            );

            $tag->click();
            $this->assertElementStyling(
                $tag,
                'checkbox-checked-color',
                'background-color',
                'Newsletter checkbox doesn\'t have correct background-color'
            );
        });
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testRegisterSectionTitle()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
            $this->assertTrue(
                Str::contains(
                    strtolower($browser->resolver->findOrFail('')->getText()),
                    'create new customer account'
                ),
                'Couldn\'t find text "Create New Customer Account"'
            );
        });
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testRegisterFormTitles()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
            $this->assertTrue(
                Str::contains(
                    strtolower($browser->resolver->findOrFail('')->getText()),
                    'personal information'
                ),
                'Couldn\'t find text "Personal Information"'
            );
            $this->assertTrue(
                Str::contains(
                    strtolower($browser->resolver->findOrFail('')->getText()),
                    'sign-in information'
                ),
                'Couldn\'t find text "Sign-in Information"'
            );
        });
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testFooterCopyright()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
            $this->assertTrue(
                Str::contains(
                    strtolower($browser->resolver->findOrFail('')->getText()),
                    '© 2023 pineapple ltd.'
                ),
                'Couldn\'t find text "© 2023 Pineapple Ltd."'
            );
        });
    }
}
