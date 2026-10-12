@foreach($navigationMenus as $navigationMenu)
    @if(($navigationMenu['type'] ?? 'link') === 'group')
        <details
            class="nav-group {{ !empty($navigationMenu['is_active']) ? 'has-active' : '' }}"
            data-key="{{ $navigationMenu['key'] }}"
            @if(!empty($navigationMenu['is_open'])) open @endif
        >
            <summary>
                <span class="nav-icon"><i class="fa-solid {{ $navigationMenu['icon'] }}"></i></span>
                {{ $navigationMenu['label'] }}
                <i class="fa-solid fa-chevron-right nav-arrow"></i>
            </summary>
            <div class="nav-submenu">
                @foreach($navigationMenu['items'] as $navigationItem)
                    <a href="{{ $navigationItem['url'] }}" class="{{ !empty($navigationItem['is_active']) ? 'active' : '' }}">
                        <span class="sub-dot"></span> {{ $navigationItem['label'] }}
                    </a>
                @endforeach
            </div>
        </details>
    @else
        <a href="{{ $navigationMenu['url'] }}" class="nav-item {{ !empty($navigationMenu['is_active']) ? 'active' : '' }}">
            <span class="nav-icon"><i class="fa-solid {{ $navigationMenu['icon'] }}"></i></span>
            {{ $navigationMenu['label'] }}
        </a>
    @endif
@endforeach
