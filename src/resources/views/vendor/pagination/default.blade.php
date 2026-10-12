@if ($paginator->hasPages())
<nav style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
    <div style="font-size:13px;color:var(--muted);">
        Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
    </div>
    <div style="display:flex;gap:4px;align-items:center;">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:8px;border:1.5px solid var(--line);color:var(--muted);cursor:not-allowed;font-size:13px;">
                <i class="fa-solid fa-chevron-left"></i>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:8px;border:1.5px solid var(--line);color:var(--text);text-decoration:none;font-size:13px;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--line)';this.style.color='var(--text)'">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
        @endif

        {{-- Pages --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;color:var(--muted);font-size:13px;">…</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:8px;background:var(--primary);color:#fff;font-size:13px;font-weight:700;">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:8px;border:1.5px solid var(--line);color:var(--text);text-decoration:none;font-size:13px;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--line)';this.style.color='var(--text)'">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:8px;border:1.5px solid var(--line);color:var(--text);text-decoration:none;font-size:13px;transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--line)';this.style.color='var(--text)'">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
        @else
            <span style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:8px;border:1.5px solid var(--line);color:var(--muted);cursor:not-allowed;font-size:13px;">
                <i class="fa-solid fa-chevron-right"></i>
            </span>
        @endif
    </div>
</nav>
@endif
