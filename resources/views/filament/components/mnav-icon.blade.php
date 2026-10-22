@switch($icon)
    @case('home')
        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12 11.2 3.05a1.13 1.13 0 0 1 1.6 0L21.75 12M4.5 9.75V19.9c0 .62.5 1.1 1.1 1.1H9.75v-4.88c0-.62.5-1.12 1.12-1.12h2.26c.62 0 1.12.5 1.12 1.12V21h4.15c.62 0 1.1-.5 1.1-1.1V9.75M8.25 21h8.25"/></svg>
        @break
    @case('cart')
        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.39c.51 0 .96.34 1.09.84l.38 1.4m0 0L6.9 14.25h11.2l1.9-7.5H5.1ZM6.9 14.25 5.8 18h12.7M9 21a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm8.25 0a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z"/></svg>
        @break
    @case('receipt')
        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 3.75h6M9 8.25h6M5.25 3.75h13.5v16.5l-2.25-1.5-2.25 1.5-2.25-1.5-2.25 1.5-2.25-1.5-2.25 1.5Z"/></svg>
        @break
    @case('cash')
        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h9l1.5 6H6Zm-3 6h15a1.5 1.5 0 0 1 1.5 1.5v7.5a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18.75v-7.5a1.5 1.5 0 0 1 1.5-1.5Zm3.75 3.75h.01m3.74 0h.01m3.75 0h.01M8.25 16.5h7.5"/></svg>
        @break
    @case('menu')
        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/></svg>
        @break
@endswitch
