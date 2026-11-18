@foreach (getMenus() as $menu)
    {{-- @dd(getMenus()) --}}
    {{-- SINGLE MENU --}}
    @if ($menu['url'] != null)
        @php
            $canakses = $menu['akses']['name'] ?? '';
        @endphp
        @can($canakses)
            <li class="menu-item {{ request()->is(ltrim($menu['url'], '/')) ? 'active' : '' }}">
                <a href="{{ $menu['url'] }}" class="menu-link">
                    <i class="menu-icon tf-icons {{ $menu['icon'] }}"></i>
                    <div data-i18n="{{ $menu['name'] }}">{{ $menu['name'] }}</div>
                </a>
            </li>
        @endcan
    @else
        {{-- SUB MENU --}}
        @php
            $parent = []; // Akses MenuUtama
            $akses = []; // Akses Permission
        @endphp

        @foreach ($menu['sub_menus'] as $parents)
            {{-- jika tidak punya anak lagi / parent kosong --}}
            @if (!$parents['parent'])
                @php
                    $parent[] = ltrim($parents['url'], '/') . '*'; // ini ditambahin titik sama bintang
                @endphp
                @if (!empty($parents['akses']['name']) && $parents['status'] != 'DISABLE')
                    @php
                        $akses[] = $parents['akses']['name'];
                    @endphp
                @endif
            @else
                {{-- jika punya anak lagi / parent isi --}}
                @foreach ($parents['parent'] as $sub)
                    @php
                        $parent[] = ltrim($sub['url'], '/');
                    @endphp
                    @if (!empty($sub['akses']['name']) && $sub['status'] != 'DISABLE')
                        @php
                            $akses[] = $sub['akses']['name'];
                        @endphp
                    @endif
                @endforeach
            @endif
        @endforeach


        {{-- SHOW SUBMENU --}}
        @canany($akses)
            <li class="menu-item {{ request()->is($parent) ? 'open' : '' }}">
                <a href="javascript:void(0)" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons {{ $menu['icon'] }}"></i>
                    <div data-i18n="{{ $menu['name'] }}">{{ $menu['name'] }}</div>
                </a>
                <ul class="menu-sub">
                    @foreach ($menu['sub_menus'] as $submenu)
                        {{-- Jika tidak punya anak lagi --}}
                        @if (!$submenu['parent'])
                            @if ($submenu['status'] != 'DISABLE')
                                @php
                                    $canakses = $submenu['akses']['name'] ?? '';
                                @endphp
                                @can($canakses)
                                    <li
                                        class="menu-item {{ request()->is(str_replace('/', '', $submenu['url'])) ? 'active' : '' }}">
                                        <a href="{{ $submenu['url'] }}" class="menu-link">
                                            <div data-i18n="{{ $submenu['name'] }}">{{ $submenu['name'] }}</div>
                                        </a>
                                    </li>
                                @endcan
                            @endif
                        @else
                            {{-- Jika punya anak lagi --}}

                            <li class="menu-item {{ request()->is($parent) ? 'open' : '' }}">
                                <a href="javascript:void(0)" class="menu-link menu-toggle">
                                    <div data-i18n="{{ $submenu['name'] }}">{{ $submenu['name'] }}</div>
                                </a>
                                <ul class="menu-sub">
                                    @foreach ($submenu['parent'] as $sub)
                                        @if ($sub['status'] != 'DISABLE')
                                            @php
                                                $canakses = $sub['akses']['name'] ?? '';
                                            @endphp
                                            @can($canakses)
                                                <li class="menu-item {{ request()->is($sub['url']) ? 'active' : '' }}">
                                                    <a href="{{ $sub['url'] }}" class="menu-link">
                                                        <div data-i18n="{{ $sub['name'] }}">{{ $sub['name'] }}
                                                        </div>
                                                    </a>
                                                </li>
                                            @endcan
                                        @endif
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </li>
        @endcanany
    @endif
@endforeach
