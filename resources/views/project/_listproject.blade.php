{{-- <div class="row">
    @foreach ($projects as $project)
        <div class="col-md-12 mb-4">
            <div class="ribbon-wrapper card shadow border card-border-shadow-info">
                <div class="ribbon ribbon-bookmark ribbon-primary">
                    <i class="fas fa-key me-1"></i>
                    {{ strtoupper($project->name) }}
                </div>

                <div class="row">
                    @include('project._listChildren', [
                        'children' => $project->allChildren,
                        'parent' => $project->id,
                    ])
                </div>
            </div>
        </div>
    @endforeach
</div> --}}

<div class="accordion mt-1 accordion-bordered" id="accordionExample">
    @foreach ($projects as $childs)
        <div class="card accordion-item mb-2">
            <h2 class="accordion-header" id="headingOne{{ $childs->id }}">
                <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                    data-bs-target="#accordionOne{{ $childs->id }}" aria-expanded="false"
                    aria-controls="accordionOne{{ $childs->id }}">
                    <span class="text-primary fw-bold">
                        {{ ucwords($childs->plain_text) }}
                    </span>
                </button>
            </h2>

            <div id="accordionOne{{ $childs->id }}" class="accordion-collapse collapse"
                data-bs-parent="#accordionExample">
                <div class="accordion-body">
                    <!-- Checkbox Pilih Semua Utama -->
                    <label class="form-check-label text-dark mb-2 fw-bold">
                        <input class="form-check-input selectAll" type="checkbox" data-group="{{ $childs->id }}">
                        Pilih Semua
                    </label>

                    <!-- Panggil fungsi rekursif anak di sini -->
                    @if ($childs->allChildren->isNotEmpty())
                        @include('project._listChildren', [
                            'children' => $childs->allChildren,
                            'groupId' => $childs->id,
                            'parentId' => $childs->id, // Inisialisasi parentId pertama
                            'level' => 1,
                        ])
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
