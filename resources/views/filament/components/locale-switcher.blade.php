<div>
    <x-filament::dropdown placement="bottom-end">
        <x-slot name="trigger">
            <button type="button" class="fi-user-menu-trigger group/avatar-menu-button flex items-center gap-x-3 rounded-lg p-2 text-sm font-medium hover:bg-gray-50 dark:hover:bg-white/5">
                <span class="fi-dropdown-trigger flex items-center gap-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                    </svg>
                    <span>{{ $locales[$currentLocale] ?? $currentLocale }}</span>
                </span>
            </button>
        </x-slot>

        <x-filament::dropdown.list>
            @foreach($locales as $code => $name)
                <form method="POST" action="{{ route('locale.switch') }}">
                    @csrf
                    <input type="hidden" name="locale" value="{{ $code }}">
                    <button type="submit" class="fi-dropdown-list-item flex w-full items-center gap-2 whitespace-nowrap rounded-md p-2 text-sm outline-none transition-colors hover:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/5 dark:focus-visible:bg-white/5 {{ $currentLocale === $code ? 'bg-gray-50 dark:bg-white/5' : '' }}">
                        <span>{{ $name }}</span>
                        @if($currentLocale === $code)
                            <svg class="ms-auto h-5 w-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        @endif
                    </button>
                </form>
            @endforeach
        </x-filament::dropdown.list>
    </x-filament::dropdown>
</div>
