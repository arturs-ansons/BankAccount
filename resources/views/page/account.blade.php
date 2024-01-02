@extends('layout.default')

@section('content')
    <div class="container flex flex-col md:flex-row flex-wrap my-6 font-bold lg:mt-8 text-3xl">
        <h1 class="text-gray-900 page-title title-font">
            <span class="base" data-ui-id="page-title-wrapper">
                Account Login
            </span>
        </h1>
    </div>

    <div class="columns">
        <div class="column main">
            <div id="customer-login-container" class="login-container">
                <div class="w-full md:w-1/2 card mr-4">
                    <div aria-labelledby="block-customer-login-heading">
                        <form class="form form-login"
                              action="{{ url('login') }}"
                              method="post"
                              id="customer-login-form"
                        >
                            @csrf
                            <meta name="csrf-token" content="{{ csrf_token() }}">
                            <fieldset class="fieldset login">
                                <legend class="mb-3">
                                    <h2 class="text-xl font-medium title-font text-primary">
                                        Login
                                    </h2>
                                </legend>
                                <div class="text-secondary-darker mb-8">
                                    If you have an account, sign in with your email address.
                                </div>
                                <div class="field">
                                    <label class="label" for="email">
                                        <span>Email</span>
                                    </label>
                                    <div class="control">
                                        <input data-test="login-email" name="email" class="form-input" required="" value=""
                                               autocomplete="off" id="email" type="email" title="Email">
                                        @error('email')
                                        <div class="text-red-500">{{ $message }}</div>
                                        @enderror

                                    </div>
                                <div class="field">
                                    <label for="pass" class="label">
                                        <span>Password</span>
                                    </label>
                                    <div class="control flex items-center">
                                        <input data-test="login-password" name="password" class="form-input" required=""
                                               autocomplete="off" id="pass" title="Password" type="password">
                                        @error('loginPassword')
                                        <div class="text-red-500">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="actions-toolbar flex justify-between pt-6 pb-2 items-center">
                                    <button data-test="login-submit" type="submit" class="btn btn-primary disabled:opacity-75" name="send">
                                        <span>Sign In</span>
                                    </button>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>

                <div class="card w-full md:w-1/2 my-8 md:my-0">
                    <div>
                        <h2 class="text-xl font-medium title-font mb-3 text-primary" role="heading" aria-level="2">
                            New Customers
                        </h2>
                    </div>
                    <form class="form form-register" action="{{ route('register') }}" method="post" id="customer-register-form">
                        @csrf
                        <meta name="csrf-token" content="{{ csrf_token() }}">
                        <fieldset class="fieldset register">
                            <div class="field">
                                <label class="label" for="register-firstName">
                                    <span>First Name</span>
                                </label>
                                <div class="control">
                                    <input data-test="register-firstName" name="firstname" class="form-input" required="" value="{{ old('firstname') }}"
                                           autocomplete="off" id="register-firstName" type="text" title="First Name">
                                    @error('firstname')
                                    <div class="text-red-500">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="field">
                                <label class="label" for="register-lastName">
                                    <span>Last Name</span>
                                </label>
                                <div class="control">
                                    <input data-test="register-lastName" name="lastname" class="form-input" required="" value="{{ old('lastname') }}"
                                           autocomplete="off" id="register-lastName" type="text" title="Last Name">
                                    @error('lastname')
                                    <div class="text-red-500">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="field">
                                <label class="label" for="register-email">
                                    <span>Email</span>
                                </label>
                                <div class="control">
                                    <input data-test="register-email" name="registerEmail" class="form-input" value="{{ old('registerEmail') }}"
                                           autocomplete="off" id="register-email" type="email" title="Email">
                                    @error('registerEmail')
                                    <div class="text-red-500">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="field">
                                <label for="register-password" class="label">
                                    <span>Password</span>
                                </label>
                                <div class="control">
                                    <input data-test="register-password" name="password" class="form-input" required=""
                                           autocomplete="off" id="register-password" title="Password" type="password">
                                    @error('password')
                                    <div class="text-red-500">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>


                            <div class="field">
                                <label for="register-passwordConfirm" class="label">
                                    <span>Password Confirmation</span>
                                </label>
                                <div class="control">
                                    <input data-test="register-passwordConfirm" name="password_confirmation" class="form-input" required=""
                                           autocomplete="off" id="register-passwordConfirm" title="Password Confirmation" type="password">
                                    @error('password_confirmation')
                                    <div class="text-red-500">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="field">
                                <label class="label" for="currency">
                                    <span>Preferred Currency</span>
                                </label>
                                <div class="control">
                                    <select name="currency" id="currency" class="form-select" style="width: 210px;">
                                        <option value="default">Choose currency</option>
                                        <option value="EUR">Euro (EUR)</option>
                                        <option value="USD">Dollar (USD)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="actions-toolbar pt-6 pb-2 flex self-end">
                                <button data-test="register-submit" type="submit" class="btn btn-primary" name="send">
                                    <span>Create Account</span>
                                </button>
                            </div>
                        </fieldset>

                    </form>

                </div>
            </div>
        </div>
    </div>

@stop
