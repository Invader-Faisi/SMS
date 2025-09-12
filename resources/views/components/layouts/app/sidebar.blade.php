@props(['title' => null, 'navbarHeading' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
{{--Head--}}
<head>
        @include('partials.head')
        @fluxAppearance
    </head>

    <body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="bg-zinc-50 dark:bg-zinc-900 border-r rtl:border-r-0 rtl:border-l border-zinc-200 dark:border-zinc-700">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
{{--Brand logo--}}
        <div class="flex items-center space-x-2">
            <flux:avatar size="xl" src="{{ asset('/logo.png') }}" />
            <p class="text-lg text-green-700 dark:text-white font-semibold hidden sm:block">SMS</p>
        </div>
        <flux:separator />
{{--Sidebar Menu--}}
        <flux:navlist variant="outline">
            <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
            <flux:navlist.group heading="User Management" expandable>
                <flux:navlist.item icon="book-open" :href="route('admin.teacher')" :current="request()->routeIs('admin.teacher')" wire:navigate>{{ __('Teachers') }}</flux:navlist.item>
                <flux:navlist.item icon="user-group" :href="route('admin.staff')" :current="request()->routeIs('admin.staff')" wire:navigate>{{ __('Staffs') }}</flux:navlist.item>
                <flux:navlist.item icon="users" :href="route('admin.parent')" :current="request()->routeIs('admin.parent')" wire:navigate>{{ __('Parents') }}</flux:navlist.item>
                <flux:navlist.item icon="academic-cap" :href="route('admin.student')" :current="request()->routeIs('admin.student')" wire:navigate>{{ __('New Admission') }}</flux:navlist.item>
            </flux:navlist.group>
            <flux:navlist.group heading="Class Management" expandable>
                <flux:navlist.item icon="building-library" :href="route('classes')" :current="request()->routeIs('classes')" wire:navigate>{{ __('Classes') }}</flux:navlist.item>
                <flux:navlist.item icon="table-cells" :href="route('classes.timetables')" :current="request()->routeIs('classes.timetables')" wire:navigate>{{ __('Time Tables') }}</flux:navlist.item>
                <flux:navlist.item icon="users" :href="route('classes.attendance')" :current="request()->routeIs('classes.attendance')" wire:navigate>{{ __('Attendance') }}</flux:navlist.item>
            </flux:navlist.group>
            <flux:navlist.group heading="Account Management" expandable>
                <flux:navlist.item icon="banknotes" :href="route('fee-structure')" :current="request()->routeIs('fee-structure')" wire:navigate>{{ __('Fee Structure') }}</flux:navlist.item>
                <flux:navlist.item icon="currency-dollar" :href="route('fees')" :current="request()->routeIs('fees')" wire:navigate>{{ __('Fees') }}</flux:navlist.item>
                <flux:navlist.item icon="users" :href="route('classes.attendance')" :current="request()->routeIs('classes.attendance')" wire:navigate>{{ __('Attendance') }}</flux:navlist.item>
            </flux:navlist.group>
            <flux:navlist.item href="#" icon="list-bullet">Transactions</flux:navlist.item>
        </flux:navlist>
        <flux:spacer />
{{--    Show in mobile mode--}}
        <flux:navlist.item href="#" icon="list-bullet" class="lg:hidden lg:block">Settings</flux:navlist.item>
        <flux:dropdown class="lg:hidden lg:block" position="bottom" align="start">

            <flux:profile
                :name="auth()->user()->username"
                :initials="auth()->user()->initials()"
                icon:trailing="chevrons-up-down"
            />

            <flux:menu class="w-[220px]">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

{{--Navbar menu--}}
    <flux:header class="fixed left-60 top-0 right-0 block! bg-white lg:bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
        <flux:navbar scrollable class="w-full">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
                <p class="text-2xl text-indigo-700 font-bold mx-2 hidden md:block dark:text-white">DigiPaeds School System</p>
                <flux:separator vertical/>
                <flux:heading size="lg" level="1" class="ml-4 hidden md:block">{{ $navbarHeading ?? ''}}</flux:heading>
                <flux:spacer class="flex-grow"/>
            <flux:navlist variant="outline" class="hidden md:block">
                <flux:navlist.item href="#" icon="list-bullet">Settings</flux:navlist.item>
            </flux:navlist>

            <!-- Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">

                <flux:profile
                    :name="auth()->user()->role"
                    :initials="auth()->user()->initials()"
                    icon:trailing="chevrons-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->username }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->role }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:navbar>
    </flux:header>


    {{ $slot }}


    @fluxScripts

    </body>
</html>
