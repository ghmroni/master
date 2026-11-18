<div class="row" id="card-block">

    @php
        use Carbon\Carbon;
    @endphp

    @forelse ($lowongans as $lowongan)
        @php
            $isExpired = Carbon::createFromFormat('Y-m-d', $lowongan->end)->lt(Carbon::today());
            $daysDiff = Carbon::parse($lowongan->created_at)->diffInDays();
        @endphp

        <div class="col-lg-6 col-sm-6 mb-2 mt-2">
            <div class="card card-border-shadow-{{ $isExpired ? 'danger' : 'success' }} h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <img src="{{ $lowongan->project->logo ? '/storage/hris/project/' . $lowongan->project->logo : '/assets/img/avatars/no-image.png' }}"
                                alt="Logo">
                        </div>
                        <h4 class="ms-1 mb-0">{{ ucwords(strtolower($lowongan->judul)) }}</h4>
                    </div>
                    <p class="mb-1 text-muted">{{ $lowongan->project->name }}</p>
                    <small class="text-muted">Created by {{ ucwords(strtolower($lowongan->user->name)) }}</small>

                    <div class="d-flex justify-content-between align-items-center mt-2 mb-0">
                        <small class="text-{{ $isExpired ? 'danger' : 'success' }}">
                            {{ $daysDiff > 30 ? '30+ hari yang lalu' : Carbon::parse($lowongan->created_at)->locale('id')->diffForHumans() }}
                        </small>
                        <small class="text-info">
                            <span class="fw-bold me-1">{{ $lowongan->totalApply }}</span>
                            <span class="text-muted">Applied</span>
                        </small>
                        <div class="btn-group">
                            @can('edit-lowongan')
                                <a href="{{ route('lowongan.editLowongan', ['slug' => $lowongan->slug]) }}"
                                    class="btn btn-icon btn-outline-warning btn-sm" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="{{ $isExpired ? 'Perpanjang' : 'Edit Lowongan' }}">
                                    <span class="fas fa-pencil-alt"></span>
                                </a>
                            @endcan
                            @can('hapus-lowongan')
                                @if (!$isExpired)
                                    <button class="btn btn-icon btn-outline-danger btn-sm tutupLowongan"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Tutup Lowongan"
                                        data-slug="{{ $lowongan->slug }}">
                                        <span class="fas fa-sign-out-alt"></span>
                                    </button>
                                @endif
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @empty
        <div class="col-12">
            <p class="text-muted">Oops! Tidak ada hasil ditemukan.</p>
        </div>
    @endforelse
</div>

<div class="container-xxl mt-5">
    <div class="footer-container d-flex align-items-center justify-content-between py-2 flex-md-row flex-column">
        <div>
            <small class="text-muted">
                Showing {{ $lowongans->firstItem() }} to {{ $lowongans->lastItem() }} of
                {{ $lowongans->total() }}
                results
            </small>
        </div>

        <div class="d-lg-inline-block">
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-primary justify-content-center">
                    {{-- Previous Button --}}
                    <li class="page-item prev">
                        @if ($lowongans->onFirstPage())
                            <a class="page-link disabled" href="#">
                                <i class="ti ti-chevron-left ti-xs"></i>
                            </a>
                        @else
                            <a class="page-link" href="{{ $lowongans->previousPageUrl() }}">
                                <i class="ti ti-chevron-left ti-xs"></i>
                            </a>
                        @endif
                    </li>

                    {{-- Page Number Links --}}
                    @php
                        $start = max(1, $lowongans->currentPage() - 2);
                        $end = min($lowongans->lastPage(), $lowongans->currentPage() + 2);
                    @endphp

                    {{-- Page Number Links --}}
                    @for ($page = $start; $page <= $end; $page++)
                        @if ($page == $lowongans->currentPage())
                            <li class="page-item active">
                                <a class="page-link" href="{{ $lowongans->url($page) }}" data-i="{{ $page }}"
                                    data-page="{{ $lowongans->perPage() }}">
                                    {{ $page }}
                                </a>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $lowongans->url($page) }}" data-i="{{ $page }}"
                                    data-page="{{ $lowongans->perPage() }}">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endfor

                    {{-- Next Button --}}
                    <li class="page-item next">
                        @if ($lowongans->hasMorePages())
                            <a class="page-link pagination-next" href="{{ $lowongans->nextPageUrl() }}">
                                <i class="ti ti-chevron-right ti-xs"></i>
                            </a>
                        @else
                            <a class="page-link pagination-next disabled" href="#">
                                <i class="ti ti-chevron-right ti-xs"></i>
                            </a>
                        @endif
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>
