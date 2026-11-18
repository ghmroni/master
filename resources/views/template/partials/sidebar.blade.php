<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme tourMenu">
    <div class="app-brand demo">
        <a href="/" class="app-brand-link">
            <img src="{{ asset('assets/img/favicon/logohr.png') }}" alt="Logo" width="41px" class="me-2">
            {{-- <i class="fas fa-h-square fa-3x me-4 text-dark"></i> --}}

            <i class="fa-solid fa-h fa-2x me-2 text-dark"></i>
            <i class="fa-solid fa-r fa-2x me-2 text-dark"></i>
            <i class="fa-solid fa-i fa-2x me-2 text-dark"></i>
            <i class="fa-solid fa-s fa-2x me-2 text-dark"></i>

        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle  text-large ms-auto">
            <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
            <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboards -->
        {{-- <li class="menu-item {{ request()->is('/') ? 'active' : '' }}">
            <a href="/" class="menu-link">
                <i class="menu-icon tf-icons ti ti-home"></i>
                <div data-i18n="Dashboard">Dashboard</div>
            </a>
        </li> --}}

        {{-- Loop menu dari helper --}}
        @foreach (getMenus(auth()->user()) as $menu)
            {{-- Jika menu punya URL (menu langsung, tanpa submenu) --}}
            @if ($menu->url)
                {{-- <li class="menu-item {{ request()->is(ltrim($menu->url, '/')) ? 'active' : '' }}"> --}}
                <li
                    class="menu-item {{ request()->is(trim($menu->url, '/')) || ($menu->url == '/' && request()->path() == '/') ? 'active' : '' }}">
                    <a href="{{ $menu->url }}" class="menu-link">
                        <i class="menu-icon tf-icons {{ $menu->icon }}"></i>
                        <div data-i18n="{{ ucwords(strtolower($menu->name)) }}">
                            {{ ucwords(strtolower($menu->name)) }}
                        </div>
                    </a>
                </li>
            @else
                {{-- Menu dengan submenu, hanya tampil jika ada submenu yang lolos filter --}}
                @if ($menu->subMenus->isNotEmpty())
                    <li
                        class="menu-item {{ $menu->subMenus->contains(function ($sub) {return request()->is(ltrim($sub->url, '/'));})? 'active open': '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons {{ $menu->icon }}"></i>
                            <div data-i18n="{{ ucwords(strtolower($menu->name)) }}">
                                {{ ucwords(strtolower($menu->name)) }}
                            </div>
                        </a>
                        <ul class="menu-sub">
                            @foreach ($menu->subMenus as $sub)
                                <li class="menu-item {{ request()->is(ltrim($sub->url, '/')) ? 'active' : '' }}">
                                    <a href="{{ $sub->url }}" class="menu-link">
                                        <div data-i18n="{{ ucwords(strtolower($sub->name)) }}">
                                            {{ ucwords(strtolower($sub->name)) }}
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endif
            @endif
        @endforeach


    </ul>
</aside>
