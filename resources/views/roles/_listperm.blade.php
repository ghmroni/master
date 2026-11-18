<div class="row">
    {{-- @dd($permissions) --}}
    @foreach ($permissions as $group => $items)
        <div class="col-md-12 mb-4">
            <div class="ribbon-wrapper card shadow border card-border-shadow-warning">
                <div class="ribbon ribbon-bookmark ribbon-primary">
                    <i class="fas fa-key me-1"></i>
                    {{ strtoupper($group) }}
                </div>

                <div class="col-md-4">
                    <label class="form-check-label text-warning mb-2">
                        <input class="form-check-input selectAll" type="checkbox"data-group="{{ $group }}">
                        pilih semua
                    </label>
                </div>

                <div class="row">
                    @foreach ($items as $perm)
                        <div class="col-md-4 mb-2">
                            <div class="form-check-warning me-3 me-lg-3">
                                <label class="form-check-label">
                                    <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]"
                                        value="{{ $perm->id }}" id="perm_{{ $perm->id }}"
                                        data-group="{{ $group }}"
                                        @if (in_array($perm->id, $rolePermissions)) checked @endif
                                        @if (in_array($perm->id, $selectedPermissions)) checked @endif>
                                    {{ ucwords($perm->name) }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
