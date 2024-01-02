
<body>
<header>
    <div data-test="header-bar" id="header" class="relative z-30 w-full border-b shadow bg-container-lighter border-container-lighter">
        <div class="container flex flex-wrap items-center justify-between w-full py-3 mx-auto mt-0">
            <!--Logo-->
            <div class="order-1 sm:order-2 lg:order-1 w-full pb-2 sm:w-auto sm:pb-0">
                <a class="flex items-center justify-center text-xl font-medium tracking-wide text-gray-800
        no-underline hover:no-underline font-title" href="#" title="" aria-label="store logo">
                    <img
                        src="{{ asset("images/512x512.png") }}"
                        title="" alt="" width="189" height="53">
                </a>

            </div>

            <!--Main Navigation-->
            <div class="z-20 order-2 sm:order-1 lg:order-2 navigation lg:hidden">
                <!-- mobile
                <div class="bg-container-lighter">
                    <div class="flex items-baseline justify-between menu-icon">
                        <div class="flex justify-end w-full">
                            <a class="flex items-center justify-center cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" class="p-3 block" width="48" height="48"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                            </a>
                        </div>
                    </div>

                </div>-->
            </div>
        </div>
    </div>
</header>
