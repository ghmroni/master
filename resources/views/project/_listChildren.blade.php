{{-- @foreach ($children as $childs)
    
    <div class="accordion mt-1 accordion-bordered">
        <div class="col-md-12">
            <div class="card accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button type="button" class="accordion-button" data-bs-toggle="collapse"
                        data-bs-target="#accordionOne{{ $childs->id }}" aria-expanded="true"
                        aria-controls="accordionOne">
                        <span class="text-info fw-bold">
                            {{ ucwords($childs->name) }}
                        </span>
                    </button>
                </h2>

                <div id="accordionOne{{ $childs->id }}" class="accordion-collapse collapse"
                    data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <label class="form-check-label text-warning mb-2">
                            <input class="form-check-input selectAll" type="checkbox"data-group="{{ $childs->id }}">
                            pilih semua
                        </label>
                        @foreach ($childs->allChildren as $child)
                            <div class="col-md-12 mb-2 ms-4">
                                <div class="form-check-warning me-3 me-lg-3">
                                    <label class="form-check-label text-success fw-bold">
                                        <input class="form-check-input perm-checkbox" type="checkbox"
                                            name="permissions[]" value="{{ $child->id }}"
                                            id="perm_{{ $child->id }}" data-group="{{ $childs->id }}"
                                            @if ($akses->contains($child->id)) checked @endif>
                                        {{ ucwords($child->name) }}
                                    </label>
                                </div>
                            </div>

                            // kalau masih ada anak lagi
                            @if ($child->allChildren->isNotEmpty())
                                @include('project._listChildren', [
                                    'children' => $child->allChildren,
                                    'parent' => $child->id,
                                ])
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

     

    </div>
@endforeach --}}


@foreach ($children as $child)
    {{-- Mengatur jarak geser ke kanan (indentasi) secara otomatis berdasarkan kedalaman level ($level * 20 pixel) --}}
    <div class="col-md-12 mb-2" style="padding-left: {{ $level * 20 }}px;">
        <div class="form-check-warning me-3 me-lg-3">
            <label class="form-check-label ">
                {{-- TAMBAHKAN atribut data-parent di bawah ini --}}
                <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]"
                    value="{{ $child->id }}" id="perm_{{ $child->id }}" data-group="{{ $groupId }}"
                    data-parent="{{ $parentId }}" @if ($akses->contains($child->id)) checked @endif>
                {{ ucwords($child->name) }}
            </label>
        </div>
    </div>

    {{-- REKURSIF JIKA ADA TURUNAN --}}
    @if ($child->allChildren->isNotEmpty())
        @include('project._listChildren', [
            'children' => $child->allChildren,
            'groupId' => $groupId,
            'parentId' => $child->id, // Kirim ID anak ini sebagai parent untuk sub-anak di bawahnya
            'level' => $level + 1,
        ])
    @endif
@endforeach
